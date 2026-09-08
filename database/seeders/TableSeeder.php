<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $rowsPerColumn = 10;

        $rowOverrides = [
            // 'C' => 7,
        ];

        $spacingX = 130;
        $spacingY = 110;

        foreach ($columns as $columnIndex => $letter) {
            $rows = $rowOverrides[$letter] ?? $rowsPerColumn;

            for ($row = 1; $row <= $rows; $row++) {
                Table::updateOrCreate(
                    ['table_code' => "{$letter}{$row}"],
                    [
                        'x_position' => 100 + ($columnIndex * $spacingX),
                        'y_position' => 100 + (($row - 1) * $spacingY),
                    ]
                );
            }
        }
    }
}
