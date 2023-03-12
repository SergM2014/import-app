<?php

namespace App\Console\Commands;

use App\Imports\ImportProduct;
use App\Services\XlsxParser;
use Illuminate\Console\Command;
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

        $import = new ImportProduct;
        \Excel::import($import, storage_path(config('app.import.xlsxFile')));

        $this->logging($import->getrowCount(), $import->getDublicatedProducts());
        $time = time()-$start;
        $this->info('Used RAM '.(memory_get_peak_usage(true)/1024/1024).' MB');
        $this->info("End. The prozess lasted $time seconds");
    }

    private function logging($counter, $dublicatedProducts): void
    {
        Log::channel('importProducts')->info("Total number of inserted in Db records is : $counter");
        if ($dublicatedProducts >0) { 
             Log::channel('importProducts')->info("Total number of dublicated records is : $dublicatedProducts");
        }
    }  
}
