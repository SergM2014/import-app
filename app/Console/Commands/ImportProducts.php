<?php

namespace App\Console\Commands;

use App\Rules\MaxSizeRule;
use App\Imports\ImportProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;


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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start = time();

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

        $import = new ImportProduct;
        Excel::import($import, $file);

        $this->logging($import->getrowCount(), $import->getDublicatedProducts());
        $time = time()-$start;
        $this->info('Used RAM '.(memory_get_peak_usage(true)/1024/1024).' MB');
        $this->info("End. The prozess lasted $time seconds");
    }

    private function logging($counter, $dublicatedProducts): void
    {
        Log::channel('importProducts')->info("Total number of inserted in Db records is : $counter");
        if ($dublicatedProducts > 0) { 
             Log::channel('importProducts')->info("Total number of dublicated records is : $dublicatedProducts");
        }
    }  
}
