<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;

class CategoryImport implements ToModel, WithUpserts
{
    private $headingRow = true;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if ($this->headingRow == true) { $this->headingRow = false; return null; }

        $title = ($row[0] == '')?  $row[1]: $row[0];
        if ($title == '') $title = $row[2];

        return new Category([
            'title' => $title,
        ]);
    } 

    public function uniqueBy()
    {
        return 'title';
    }
}
