<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Log;

class StatisticsExport implements WithMultipleSheets
{

    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function sheets(): array
    {
        $sheets = [];

        try {
            $sheets[] = new StatisticsExportSheet1();
            return $sheets;
        }
        catch(\Exception $e) {
            Log::info('Something went wrong while exporting in Sheet'.$e);
        }

    }

}
