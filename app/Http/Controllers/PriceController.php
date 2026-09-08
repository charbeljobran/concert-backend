<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function index()
    {
        return Price::orderByDesc('year')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'amount_per_table' => 'required|numeric|min:0',
        ]);

        return Price::updateOrCreate(
            ['year' => $validated['year']],
            ['amount_per_table' => $validated['amount_per_table']]
        );
    }
}