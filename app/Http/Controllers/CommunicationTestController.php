<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommunicationTestController extends Controller
{
    public $data_matrix = [], $cumulative_sums = [];
    public $filename = 'cumulative_sums.csv';
    public $weeks = 52;

    public function index(Request $request)
    {
        $givenWeek = $request->input('target_week', $this->weeks); // Get week from request, default to 52
        $this->generate_data();
        $this->calculate_cumulative_sums($givenWeek);
        $this->export_cumulative_sums_csv();
        return $this->cumulative_sums;
    }

    private function generate_data()
    {
        $row_individuals = 10;

        mt_srand(42); // Seed for reproducibility

        for ($row_index = 0; $row_index < $row_individuals; $row_index++) {
            for ($column_index = 0; $column_index < $this->weeks; $column_index++) {
                // Generate a random float between 0 and 1 with 4 decimal places
                $this->data_matrix[$row_index][$column_index] = round(mt_rand() / mt_getrandmax(), 4);
            }
        }
    }

    private function calculate_cumulative_sums($givenWeek)
    {
        foreach ($this->data_matrix as $row_index => $weeks_data) {
            $cumulative_sum = 0;
            foreach ($weeks_data as $column_index => $value) {
                if ($column_index >= $givenWeek) break; // Stop at given week
                $cumulative_sum += $value;
                // Round cumulative sums to 4 decimal places for consistency
                $this->cumulative_sums[$row_index][$column_index] = round($cumulative_sum, 4);
            }
        }
    }

    private function export_cumulative_sums_csv()
    {
        $file = fopen($this->filename, 'w');

        // Write header row
        fputcsv($file, array_merge(['Individual'], range(1, $this->weeks)));

        // Write data rows
        foreach ($this->cumulative_sums as $i => $weeks_data) {
            // Ensure all values are treated as numbers
            $row = array_map('floatval', array_merge([($i + 1)], $weeks_data));
            fputcsv($file, $row);
        }

        fclose($file);
    }
}
