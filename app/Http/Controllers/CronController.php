<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use App\Mail\StatisticsReport;
use App\Exports\StatisticsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class CronController extends Controller
{
    public function export() 
    {
        Log::info('in export');
        //$object = new \stdClass();
        //$object->email = 'manali.ranade@anveshak.com';
        
        try {
            $contents = Excel::raw(new StatisticsExport, \Maatwebsite\Excel\Excel::XLSX);
            //Mail::to($object)->send(new StatisticsReport($contents));
            foreach (['manali.ranade@anveshak.com', 'aishwarya.dhopate@anveshak.com'] as $recipient) {
                Mail::to($recipient)->send(new StatisticsReport($contents));
            }
            
        }
        catch(\Exception $e) {
            Log::info($e);
        }
    }
}
