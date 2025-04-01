<?php

namespace App\Http\Controllers;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class CollectionsTestController extends Controller
{

    public function index(){

  $employees = [
    ['name' => 'John', 'city' => 'Dallas'],
    ['name' => 'Jane', 'city' => 'Austin'],
    ['name' => 'Jake', 'city' => 'Dallas'],
    ['name' => 'Jill', 'city' => 'Dallas'],
  ];

  $offices = [
    ['office' => 'Dallas HQ', 'city' => 'Dallas'],
    ['office' => 'Dallas South', 'city' => 'Dallas'],
    ['office' => 'Austin Branch', 'city' => 'Austin'],
  ];

// use collections to simplify the process
$employee_collection = collect($employees);
$office_collection = collect($offices);


$groupedEmployees = $employee_collection->groupBy('city'); // Group employees by city
/*{
    "Dallas": [
      {
        "name": "John",
        "city": "Dallas"
      },
      {
        "name": "Jake",
        "city": "Dallas"
      },
      {
        "name": "Jill",
        "city": "Dallas"
      }
    ],
    "Austin": [
      {
        "name": "Jane",
        "city": "Austin"
      }
    ]
  }
*/
$groupedOffices = $office_collection->groupBy('city'); // Group offices by city

/*{
    "Dallas": [
      {
        "office": "Dallas HQ",
        "city": "Dallas"
      },
      {
        "office": "Dallas South",
        "city": "Dallas"
      }
    ],
    "Austin": [
      {
        "office": "Austin Branch",
        "city": "Austin"
      }
    ]
  }
    */
$output = $groupedOffices->map(function ($offices, $city) use ($groupedEmployees) {

    $cityEmployees = $groupedEmployees->get($city, collect())->pluck('name')->toArray();     // Get employees for the current city

    // Map each office to the employees of the city
    return $offices->mapWithKeys(function ($office) use ($cityEmployees) {
        return [$office['office'] => $cityEmployees];
    });
})->toArray();

return $output;
/*{
    "Dallas": {
      "Dallas HQ": [
        "John",
        "Jake",
        "Jill"
      ],
      "Dallas South": [
        "John",
        "Jake",
        "Jill"
      ]
    },
    "Austin": {
      "Austin Branch": [
        "Jane"
      ]
    }
  }*/
    }
}
