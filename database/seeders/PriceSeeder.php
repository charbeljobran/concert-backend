<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            2026 => 40,
            
        ];

        foreach ($prices as $year => $amount) {
            Price::updateOrCreate(
                ['year' => $year],
                ['amount_per_table' => $amount]
            );
        }
    }
}