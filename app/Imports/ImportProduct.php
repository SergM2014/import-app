<?php

namespace App\Imports;

use App\Models\Product;
use App\Services\XlsxParser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\Log;

class ImportProduct implements ToModel,  WithUpserts, WithBatchInserts
{
    private $categories;
    
    private $headingRow = true;

    private $counter = 0;

    private $dublicatedProducts = [];

    public function __construct()
    {
        $this->categories = $this->getCategories();
    }

    public function model(array $row)
    {
        if($this->headingRow == true) { $this->headingRow = false; return null; }

        $this->counter++;

        $title = $this->getCategoryTitle($row);

            $categoryId = $this->categories[$title];

            if($row[10] != '') {
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

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'code';
    }

    private function getCategories(): array
    {
        $datas = (new xlsxParser())->getCategories();
        $categories = [];

        foreach($datas as $title) {
            $categories[$title] = DB::table('categories')->where('title', $title)->first()->id;
        }
       
        return $categories;
    }

    public function getCategoryTitle($value)
    {
        $title = ($value[0] == '')?  $value[1]: $value[0];
        if ($title == '') $title = $value[2];

        return $title;
    }

    private function logging(): void
    {
        Log::channel('importProducts')->info("Total number of inserted in Db records is : $this->counter");
        if (!empty($this->dublicatedProducts)) { 
             Log::channel('importProducts')->info("Total number of dublicated records is : ".count($this->dublicatedProducts));
        }
    }
}
