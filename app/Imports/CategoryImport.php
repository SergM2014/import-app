<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;

class CategoryImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $title = ($row[0] == '')?  $row[1]: $row[0];
        if ($title == '') $title = $row[2];

        return new Category([
            'title' => $title,
        ]);
    } 
}
