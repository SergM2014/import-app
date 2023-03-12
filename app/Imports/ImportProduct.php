<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use App\Models\Category;

class ImportProduct implements ToModel,  WithUpserts, WithChunkReading, WithBatchInserts
{
    private $categories = [];
    
    private $headingRow = true;

    private $rows = 0;

    private $dublicatedProducts = 0;

    public function __construct()
    {
         $this->getCategories();
    }

    public function model(array $row)
    {
        if($this->headingRow == true) { $this->headingRow = false; return null; }

        ++$this->rows;

        $title = $this->getCategoryTitle($row);

        $categoryId = $this->categories[$title];

        if(isset($row[10])) {
            unset($row[0]);

            $temp = [];
            foreach ($row as $key => $value) $temp[$key-1] = $value;
            $row = $temp;
        }

        $warrantyStatus = $row[8] == config('app.import.warrantyStatus')? 'no': (string)$row[8];
        $presenceStatus = $row[9] == config('app.import.presenceStatus')? 'yes': 'no';

        return new Product([
            'category_id' => $categoryId,
            'manufacturer' => $row[3],
            'title' => $row[4]?? substr($row[6], 100),
            'code' => $row[5],
            'description' => $row[6],
            'price' => $row[7],
            'warranty' => $warrantyStatus,
            'presence' => $presenceStatus,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        ++$this->dublicatedProducts;
        return 'code';
    }

    private function getCategories()
    {
        $datas = Category::all();

        foreach ($datas as $data) {
            $this->categories [$data->title] = $data->id;
        }
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function getDublicatedProducts()
    {
        return $this->dublicatedProducts;
    }

    public function getCategoryTitle($value)
    {
        $title = ($value[0] == '')?  $value[1]: $value[0];
        if ($title == '') $title = $value[2];

        return $title;
    }
}
