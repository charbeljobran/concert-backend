<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->query('year', now()->year);

        return Table::withExists(['reservations as is_reserved' => function ($query) use ($year) {
            $query->where('reservation_tables.year', $year);
        }])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    $validated = $request->validate([
        'table_code' => 'required|string|max:10',
        'x_position' => 'required|integer',
        'y_position' => 'required|integer',
    ]);

    return Table::updateOrCreate(
        ['table_code' => $validated['table_code']],
        ['x_position' => $validated['x_position'], 'y_position' => $validated['y_position']]
    );

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $year = request()->query('year', now()->year);

        return Table::withExists(['reservations as is_reserved' => function ($query) use ($year) {
            $query->where('reservation_tables.year', $year);
        }])
            ->with(['reservations' => function ($query) use ($year) {
                $query->wherePivot('year', $year)
                    ->with(['customer', 'tables' => function ($q) use ($year) {
                        $q->wherePivot('year', $year);
                    }]);
            }])
            ->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
