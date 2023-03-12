<?php

namespace App\Console\Commands;

use App\Imports\ImportProduct;
use App\Services\XlsxParser;
use Illuminate\Console\Command;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $xlsxParser;

    private $dublicatedProducts = [];

    public function __construct(XlsxParser $xlsxParser)
    {
        $this->xlsxParser = $xlsxParser;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start = time();

        // $products = $this->xlsxParser->getDatas();
        // $categories = $this->getCategories();
       
       // $this->insertProduct($products, $categories);
        $import = new ImportProduct;
        \Excel::import($import, storage_path(config('app.import.xlsxFile')));
dd($import->getRowCount());
        $this->logging($import->getrowCount());
        $time = time()-$start;
        $this->info('Used RAM '.(memory_get_peak_usage(true)/1024/1024).' MB');
        $this->info("End. The prozess lasted $time seconds");
    }

    private function getCategories(): array
    {
        $datas = $this->xlsxParser->getCategories();
        $categories = [];

        foreach($datas as $title) {
            $categories[$title] = DB::table('categories')->where('title', $title)->first()->id;
        }
       
        return $categories;
    }

    private function insertProduct(Generator $products, array $categories): void
    {
        $counter = 0;

        foreach ($products as $key => $product) {
            if ($key == 0) continue;
            $title = $this->xlsxParser->getCategoryTitle($product);

            $categoryId = $categories[$title];

            if($product[10] != '') {
                unset($product[0]);

                $temp = [];
                foreach ($product as $key => $value) $temp[$key-1] = $value;
                $product = $temp;
            }

            if (DB::table('products')->where('code', $product[5])->first()) {
                $this->dublicatedProducts[] = $product;
                continue;
            }

            $presenceStatus = $product[9] == config('app.import.presenceStatus')? 'yes': 'no';
            $warrantyStatus = $product[8] == config('app.import.warrantyStatus')? 'no': (string)$product[8];

            DB::table('products')->insert([
                'category_id' => $categoryId, 'manufacturer' => $product[3], 'title' => $product[4], 'code' => $product[5],
                'description'=> $product[6], 'price' => $product[7], 'warranty' => $warrantyStatus, 'presence' => $presenceStatus
            ]);

            $counter++;
        }

        $this->logging($counter);
    }

    private function logging($counter): void
    {
        Log::channel('importProducts')->info("Total number of inserted in Db records is : $counter");
        if (!empty($this->dublicatedProducts)) { 
             Log::channel('importProducts')->info("Total number of dublicated records is : ".count($this->dublicatedProducts));
        }
    }  
    
}
