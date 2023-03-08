<?php

declare(strict_types=1);

namespace App\Services;

use App\Rules\MaxSizeRule;
use Generator;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Validator;

class XlsxParser
{
    public function getCategories(): array
    {
        $datas = $this->getDatas();

        $categories = [];
        foreach ($datas as $key => $value) {
            if ($key == 0) continue;

            $categories[] = $this->getCategoryTitle($value);
        }

        return array_values(array_unique($categories));
    }

    public function getDatas(): array|Generator
    {
        $file = storage_path(config('app.import.xlsxFile'));

        $validator = Validator::make(
            [
                'file'       => $file,
                'extension'  => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                'mimeType'   => mime_content_type($file),
            ],
            [
                'file'       => new MaxSizeRule,
                'extension'  => 'in:xlsx,xls',
                'mimeType'   => 'in:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
          );

        if ($validator->fails()) {
           die('The following errors took place => '.$validator->errors().PHP_EOL);
        }

        if ( $xlsx = SimpleXLSX::parse($file) ) {
            $datas = ( $xlsx->readRows() );
        } else {
            echo SimpleXLSX::parseError();
        }

        return $datas;
    }

    public function getCategoryTitle($value)
    {
        $title = ($value[0] == '')?  $value[1]: $value[0];
        if ($title == '') $title = $value[2];

        return $title;
    }
}