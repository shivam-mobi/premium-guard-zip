<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
use Illuminate\Support\Facades\Log;

class LateModelController extends Controller
{
    public static $aces_vcdb = 'mysql_aces'; 
    public static $main_db = 'mysql';

    protected $pg_secdb;
    protected $image_path;
    protected $defau_path;

    public function __construct() {
        $this->pg_secdb = Config::get('constants.pg_secdbname');
        $this->image_path = Config::get('constants.imagepath');
        $this->default_path =  Config::get('constants.defaultpath');
    }

    /**
     * Display a listing of Year.
     *
     * @return \Illuminate\Http\Response
     */

    public function indexBK(Request $request)
    {
        $products["years"] = DB::connection(self::$main_db)->table('applications')
                                                  ->selectRaw('DISTINCT(YearID) as year')
                                                  ->orderBy('year','DESC')
                                                  ->take(3)
                                                  ->get();
        return response()->json($products);
    	
    }

    public function index(Request $request)
    {
        $years = DB::connection(self::$main_db)->table('applications')
            ->select('YearID as year')
            ->distinct()
            ->orderByDesc('year')
            ->limit(3)
            ->get();

        return response()->json(['years' => $years]);
    }

    public function allresultBK(Request $request,$year,$id=null,$parttypename=null)
    {

        if($id==null && $parttypename==null) {
            $id = 1;
            $parttypename = 'Oil Filter';
        }

        // $products["parttypes"] = DB::connection(self::$main_db)
                                // ->select("SELECT CASE WHEN partTypeid = 1 then 'Oil Filter' WHEN partTypeid = 14 then 'Housing Cap' WHEN partTypeid = 10 then 'Drain Plug' ELSE partTypename END AS partTypename 
                                // FROM parttype_master 
                                // WHERE partTypeid IN(1,2,3,4,5,8,10,14) 
                                // ORDER BY FIELD(partTypeid,1,2,3,4,5,14,10,8)");
                                // ->select("SELECT CASE WHEN PartTerminologyID = 5340 then 'Oil Filter' WHEN PartTerminologyID = 54048 then 'Housing Cap' WHEN PartTerminologyID = 5560 then 'Drain Plug' ELSE partTypename END AS partTypename 
                                // FROM parttype_master 
                                // WHERE PartTerminologyID IN(5340,6192,6832,6200,6216,8600,10964,8852,11713,47330,5560,5428,54048) 
                                // ORDER BY partTypeOrder ASC");

        $partTypeNames = [
            "Oil Filter",
            "Air Filter",
            "Cabin Air Filter",
            "Fuel Filter",
            "Transmission Filter",
            "Housing Cap",
            "Drain Plug",
            "Wiper Blade"
        ];

        $products["parttypes"] = array_map(function($name) {
            return (object)['partTypename' => $name];
        }, $partTypeNames);

        $partTypeArray=array("1"=>[5340],"2"=>[6192],"3"=>[6832],"4"=>[6200,6216],"5"=>[8600,10964],"6"=>[54048],"7"=>[5560,5428],"8"=>[8852,11713,47330]);

        if($year!='' && $id!='' && $parttypename!='') {

            $parttypename =str_replace(' ', '-',$parttypename);
            $parttypeid = implode(',',$partTypeArray[$id]);

            $get_partnumbers = DB::connection(self::$main_db) 
                                // ->select("SELECT p.partnumber,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) AS model,p.partType 
                                // FROM applications a JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                                // JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                                // JOIN partNumberInfo p ON a.partnumber=p.partNumber 
                                // WHERE p.partType IN (".$parttypeid.") AND a.YearID=$year AND 
                                // p.partnumber NOT LIKE 'MG%' AND 
                                // p.partnumber NOT IN('NSO','NSA','NSC','NSF','NST','NRO','NRA','NRC','NRF','NRT','TBA','NR') 
                                // GROUP BY a.partnumber 
                                // ORDER BY CAST(digits(a.partnumber) AS UNSIGNED) DESC");
                                ->select("SELECT p.partNumber as partnumber,TRIM(ma.MakeName) AS make,
                                    TRIM(mo.ModelName) AS model,p.PartTerminologyID as partType
                                FROM applications a
                                JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID
                                JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID
                                JOIN partNumberInfo p ON a.part_id=p.part_id
                                WHERE p.PartTerminologyID IN (".$parttypeid.") AND a.YearID=$year AND
                                p.partNumber NOT LIKE 'MG%' AND
                                p.partNumber NOT IN('NSO','NSA','NSC','NSF','NST','NRO','NRA','NRC','NRF','NRT','TBA','NR')
                                GROUP BY p.partNumber
                                ORDER BY CAST(digits(p.partNumber) AS UNSIGNED) DESC");


            if(count($get_partnumbers) > 0) {

                foreach ($get_partnumbers as $key => $value) { 
                    $part = $value->partnumber;
                    $part_split = substr($part,0,2);
                    $part_upper = strtoupper($part_split);
    
                    if($part_upper=="DF"){
                        $partTypeName = "Diesel-Fuel-Filter";
                    }
                    else{
                        $partTypeName = $parttypename;
                    }

                    $make = $value->make;
                    $model = $value->model;
                    $make = str_replace(' ', '-',$make);
                    $model = str_replace(' ', '-',$model);

                    $productImg = DB::connection(self::$main_db)
                                // ->select("SELECT fileName from image_mapper WHERE partNumber='$part' 
                                //     ORDER BY CASE 
                                //     WHEN fileName LIKE '%ZZ2_FRO.%' then 1 WHEN fileName LIKE '%P02_.%' then 2
                                //     WHEN fileName LIKE '%P04_FRO.%' then 3 WHEN fileName LIKE '%P01_BAC.%' then 4
                                //     WHEN fileName LIKE '%P01_TOP.%' then 5 WHEN fileName LIKE '%P01_BOT.%' then 6
                                //     WHEN fileName LIKE '%P01_SID.%' then 7 WHEN fileName LIKE '%P01_LEF.%' then 8
                                //     WHEN fileName LIKE '%P01_RIG.%' then 9 WHEN fileName LIKE '%P01_ANG.%' then 10
                                //     WHEN fileName LIKE '%-F.%' then 11 WHEN fileName LIKE '%-E.%' then 12
                                //     WHEN fileName LIKE '%-A.%' then 13 WHEN fileName LIKE '%-B.%' then 14
                                //     WHEN fileName LIKE '%-C.%' then 15 WHEN fileName LIKE '%-D.%' then 16
                                //     WHEN fileName LIKE '%-G.%' then 17 WHEN fileName LIKE '%-H.%' then 18
                                //     WHEN fileName LIKE '%-K.%' then 19 WHEN fileName LIKE '%-I.%' then 20
                                //     ELSE 21 END LIMIT 1");
                                ->select("SELECT fileName from image_mapper im
                                    JOIN partNumberInfo p ON im.part_id=p.part_id
                                    WHERE p.partNumber='$part'
                                    ORDER BY CASE 
                                    WHEN fileName LIKE '%ZZ2_FRO.%' then 1 WHEN fileName LIKE '%P02_.%' then 2
                                    WHEN fileName LIKE '%P04_FRO.%' then 3 WHEN fileName LIKE '%P01_BAC.%' then 4
                                    WHEN fileName LIKE '%P01_TOP.%' then 5 WHEN fileName LIKE '%P01_BOT.%' then 6
                                    WHEN fileName LIKE '%P01_SID.%' then 7 WHEN fileName LIKE '%P01_LEF.%' then 8
                                    WHEN fileName LIKE '%P01_RIG.%' then 9 WHEN fileName LIKE '%P01_ANG.%' then 10
                                    WHEN fileName LIKE '%-F.%' then 11 WHEN fileName LIKE '%-E.%' then 12
                                    WHEN fileName LIKE '%-A.%' then 13 WHEN fileName LIKE '%-B.%' then 14
                                    WHEN fileName LIKE '%-C.%' then 15 WHEN fileName LIKE '%-D.%' then 16
                                    WHEN fileName LIKE '%-G.%' then 17 WHEN fileName LIKE '%-H.%' then 18
                                    WHEN fileName LIKE '%-K.%' then 19 WHEN fileName LIKE '%-I.%' then 20
                                    ELSE 21 END LIMIT 1");

                    
                   
                    
                    // if(count($productImg) > 0) {
                    //     $remoteFileUrl = $this->image_path.$productImg[0]->fileName;

                    //     $options = [
                    //         'http' => [
                    //             'method' => 'HEAD',
                    //             'ignore_errors' => true,
                    //         ],
                    //     ];
                    //     $context = stream_context_create($options);

                    //     $response = file_get_contents($remoteFileUrl, false, $context);

                    //     if ($response !== false) {
                        
                    //         $headers = $http_response_header; 
                    //         $statusCode = explode(' ', $headers[0])[1];
                    //         if ($statusCode === '200') {
                        
                    //         } else {
                    //             $productImg = [];
                    //         }
                    //     } else {
                    //         $productImg = [];
                    //     }

                    // }

                    $imagesrc = '';
                    if(count($productImg) > 0) {
                        
                     
                        $value->image_path = $this->image_path.$productImg[0]->fileName;
                    }
                    else {

                      
                        if($id==6) {
                            $value->image_path = $this->default_path.'PgFilterLookupOILCAP.png';
                        }
                        elseif($id==7) {
                            $value->image_path = $this->default_path.'PgFilterLookupDRAINPLUG.png';
                        }
                        elseif($id==8) {
                            $value->image_path = $this->default_path.'PgFilterLookupREARWIPER.png';
                        }
                        else {
                            $value->image_path = $this->default_path.'filterType'.$id.'.png';
                        }
                    }

                    $value->year = $year;
                    $value->make = $make;
                    $value->model = $model;
                    $value->partTypeName = $partTypeName;

                }

                
            }

            $products["allresult"] = $get_partnumbers;

            return json_encode($products);
  
        }
    	
    }

    public function allresult(Request $request, $year, $id = null, $parttypename = null)
    {
        set_time_limit(300);
        $partTypeNames = ['Oil Filter', 'Air Filter', 'Cabin Air Filter', 'Fuel Filter', 'Transmission Filter', 'Housing Cap', 'Drain Plug', 'Wiper Blade'];
        $partTypeArray = ['1' => [5340], '2' => [6192], '3' => [6832], '4' => [6200, 6216], '5' => [8600, 10964], '6' => [54048], '7' => [5560, 5428], '8' => [8852, 11713, 47330]];

        $products = [
            'parttypes' => array_map(fn($name) => (object)['partTypename' => $name], $partTypeNames),
            'allresult' => []
        ];

        if ($id === null && $parttypename === null) {
            $id = '1';
            $parttypename = 'Oil Filter';
        }

        $year = (int) $year;
        if ($year <= 0 || !isset($partTypeArray[(string) $id]) || $parttypename === '') {
            return response()->json($products);
        }

        $parttypeid = implode(',', array_map('intval', $partTypeArray[(string) $id]));
        $parttypename = str_replace(' ', '-', $parttypename);

        $get_partnumbers = DB::connection(self::$main_db)->select(
            "SELECT p.partNumber as partnumber, TRIM(ma.MakeName) AS make, TRIM(mo.ModelName) AS model, p.PartTerminologyID as partType
            FROM applications a
            JOIN {$this->pg_secdb}.make ma ON a.MakeID = ma.MakeID
            JOIN {$this->pg_secdb}.model mo ON a.ModelID = mo.ModelID
            JOIN partNumberInfo p ON a.part_id = p.part_id
            WHERE p.PartTerminologyID IN ($parttypeid) AND a.YearID = ? AND p.partNumber NOT LIKE 'MG%'
            AND p.partNumber NOT IN ('NSO','NSA','NSC','NSF','NST','NRO','NRA','NRC','NRF','NRT','TBA','NR')
            GROUP BY p.partNumber ORDER BY CAST(digits(p.partNumber) AS UNSIGNED) DESC",
            [$year]
        );

        $defaultImages = ['6' => 'PgFilterLookupOILCAP.png', '7' => 'PgFilterLookupDRAINPLUG.png', '8' => 'PgFilterLookupREARWIPER.png'];

        foreach ($get_partnumbers as $value) {
            $part = $value->partnumber;
            $value->partTypeName = (strtoupper(substr($part, 0, 2)) === 'DF') ? 'Diesel-Fuel-Filter' : $parttypename;
            $value->make = str_replace(' ', '-', $value->make);
            $value->model = str_replace(' ', '-', $value->model);
            $value->year = $year;

            $productImg = DB::connection(self::$main_db)->select(
                "SELECT fileName FROM image_mapper im JOIN partNumberInfo p ON im.part_id = p.part_id
                WHERE p.partNumber = ?
                ORDER BY CASE WHEN fileName LIKE '%ZZ2_FRO.%' THEN 1 WHEN fileName LIKE '%P02_.%' THEN 2
                WHEN fileName LIKE '%P04_FRO.%' THEN 3 WHEN fileName LIKE '%P01_BAC.%' THEN 4
                WHEN fileName LIKE '%P01_TOP.%' THEN 5 WHEN fileName LIKE '%P01_BOT.%' THEN 6
                WHEN fileName LIKE '%P01_SID.%' THEN 7 WHEN fileName LIKE '%P01_LEF.%' THEN 8
                WHEN fileName LIKE '%P01_RIG.%' THEN 9 WHEN fileName LIKE '%P01_ANG.%' THEN 10
                WHEN fileName LIKE '%-F.%' THEN 11 WHEN fileName LIKE '%-E.%' THEN 12
                WHEN fileName LIKE '%-A.%' THEN 13 WHEN fileName LIKE '%-B.%' THEN 14
                WHEN fileName LIKE '%-C.%' THEN 15 WHEN fileName LIKE '%-D.%' THEN 16
                WHEN fileName LIKE '%-G.%' THEN 17 WHEN fileName LIKE '%-H.%' THEN 18
                WHEN fileName LIKE '%-K.%' THEN 19 WHEN fileName LIKE '%-I.%' THEN 20 ELSE 21 END LIMIT 1",
                [$part]
            );

            $value->image_path = count($productImg) > 0
                ? $this->image_path . $productImg[0]->fileName
                : $this->default_path . ($defaultImages[(string) $id] ?? 'filterType' . $id . '.png');
        }

        $products['allresult'] = $get_partnumbers;

        return response()->json($products);
    }
}
