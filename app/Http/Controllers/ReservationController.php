<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Price;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->query('year', now()->year);

        return Reservation::with('tables', 'customer')
            ->where('year', $year)
            ->when($request->query('payment_status'), function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when($request->query('customer_name'), function ($query, $name) {
                $query->whereHas('customer', function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%");
                });
            })
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:255',
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'exists:tables,id',
            'notes' => 'nullable|string'
        ]);
        $year = now()->year;

        $price = Price::where('year', $year)->first();
        if (!$price) {
            abort(422, "No price set for {$year} yet.");
        }
        return DB::transaction(function () use ($validated, $year, $price) {
            $customer = Customer::firstOrCreate(
                ['phone' => $validated['customer_phone']],
                ['name' => $validated['customer_name']]
            );

            $alreadyReserved = DB::table('reservation_tables')
                ->whereIn('table_id', $validated['table_ids'])
                ->where('year', $year)
                ->lockForUpdate()
                ->pluck('table_id');

            if ($alreadyReserved->isNotEmpty()) {
                abort(422, 'Some tables are already reserved: ' . $alreadyReserved->implode(', '));
            }

            $reservation = Reservation::create([
                'customer_id' => $customer->id,
                'year' => $year,
                'payment_status' => 'pending',
                'total_amount' => count($validated['table_ids']) * $price->amount_per_table,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['table_ids'] as $tableId) {
                $reservation->tables()->attach($tableId, ['year' => $year]);
            }

            return $reservation->load('tables', 'customer');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Reservation::with('tables', 'customer')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reservation = Reservation::findOrFail($id);

        $validated = $request->validate([
            'payment_status' => 'sometimes|in:pending,paid,cancelled',
            'amount_paid' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|nullable|string',
        ]);

        if (array_key_exists('payment_status', $validated)) {
            if ($validated['payment_status'] === 'paid' && !$reservation->paid_at) {
                $validated['paid_at'] = now();
            } elseif ($validated['payment_status'] !== 'paid') {
                $validated['paid_at'] = null;
            }
        }

        $reservation->update($validated);

        if ($reservation->payment_status === 'cancelled') {
            $reservation->tables()->wherePivot('year', $reservation->year)->detach();
        }

        return $reservation->load('tables', 'customer');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted.']);
    }

    public function addTables(Request $request, string $id)
    {
        $reservation = Reservation::findOrFail($id);

        $validated = $request->validate([
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'exists:tables,id',
        ]);

        return DB::transaction(function () use ($validated, $reservation) {
            $alreadyReserved = DB::table('reservation_tables')
                ->whereIn('table_id', $validated['table_ids'])
                ->where('year', $reservation->year)
                ->lockForUpdate()
                ->pluck('table_id');

            if ($alreadyReserved->isNotEmpty()) {
                abort(422, 'Some tables are already reserved: ' . $alreadyReserved->implode(', '));
            }

            foreach ($validated['table_ids'] as $tableId) {
                $reservation->tables()->attach($tableId, ['year' => $reservation->year]);
            }

            $price = Price::where('year', $reservation->year)->firstOrFail();
            $reservation->total_amount = $reservation->tables()->count() * $price->amount_per_table;
            $reservation->save();

            return $reservation->load('tables', 'customer');
        });
    }
    public function removeTable(string $id, string $tableId)
    {
        $reservation = Reservation::findOrFail($id);

        $reservation->tables()->wherePivot('year', $reservation->year)->detach($tableId);

        if ($reservation->tables()->count() === 0) {
            $reservation->delete();
            return response()->json(['message' => 'Reservation removed (no tables remaining).']);
        }

        $price = Price::where('year', $reservation->year)->firstOrFail();
        $reservation->total_amount = $reservation->tables()->count() * $price->amount_per_table;
        $reservation->save();

        return $reservation->load('tables', 'customer');
    }
    public function years()
    {
        return Reservation::select('year')->distinct()->orderByDesc('year')->pluck('year');
    }
}
