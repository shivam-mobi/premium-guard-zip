<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\QualifierController;

use DB;
use Config;
use stdClass;
use Log;

class ClassicSearchController extends Controller
{


    public static $aces_vcdb = 'mysql_aces';
    public static $main_db = 'mysql';


    protected $pg_db;
    protected $pg_secdb;
    protected $image_path;
    protected $current_year;

    public function __construct()
    {
        $this->pg_db = Config::get('constants.pg_dbname');
        $this->pg_secdb = Config::get('constants.pg_secdbname');
        $this->image_path = Config::get('constants.imagepath');
        $this->default_path = Config::get('constants.defaultpath');
        $this->current_year = date('Y');
    }

    // public function indexBK(Request $request, $vehicletypeid)
    // {
    //     return $data = DB::connection(self::$aces_vcdb)
    //         ->table('basevehicle')
    //         ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
    //         ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
    //         ->select('basevehicle.YearID as year')->distinct()
    //         ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
    //         ->groupBy('basevehicle.YearID')
    //         ->orderBy('basevehicle.YearID', 'DESC')
    //         ->get();
    // }

    public function index(Request $request, $vehicletypeid)
    {
        return DB::connection(self::$aces_vcdb)
            ->table('basevehicle')
            ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
            ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
            ->select('basevehicle.YearID as year')
            ->where('vehicletype.VehicleTypeGroupID', (int) $vehicletypeid)
            ->groupBy('basevehicle.YearID')
            ->orderBy('basevehicle.YearID', 'DESC')
            ->get();
    }

    // public function vehicletypeCollectionBK(Request $request)
    // {
    //     return $data = DB::connection(self::$aces_vcdb)
    //         ->table('vehicletypegroup')
    //         ->select('VehicleTypeGroupID as vehicletypeID', 'VehicleTypeGroupName as vehicletype')
    //         ->orderByRaw('FIELD(VehicleTypeGroupID,2,8,7)')
    //         ->get();
    // }

    public function vehicletypeCollection(Request $request)
    {
        $items = DB::connection(self::$aces_vcdb)
            ->table('vehicletypegroup')
            ->select('VehicleTypeGroupID as vehicletypeID', 'VehicleTypeGroupName as vehicletype')
            ->whereIn('VehicleTypeGroupID', [2, 8, 7])
            ->orderByRaw('CASE VehicleTypeGroupID WHEN 2 THEN 1 WHEN 8 THEN 2 WHEN 7 THEN 3 END')
            ->get();
        return $items;
    }

    public function getmakesBK(Request $request, $vehicletypeid, $yearVal, $projectname=null)
    {

        $next_year = $this->current_year + 1;

        if ($projectname == 'pureflow' && ($yearVal==$next_year || $yearVal==$this->current_year)) {

            // Combine both queries using UNION
            $results = DB::connection(self::$aces_vcdb)
                ->select("(SELECT make.MakeID AS MakeID, make.MakeName AS make
                FROM basevehicle
                JOIN make ON basevehicle.MakeID = make.MakeID 
                JOIN model ON basevehicle.ModelID = model.ModelID 
                JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID 
                WHERE basevehicle.YearID = $yearVal AND vehicletype.VehicleTypeGroupID = $vehicletypeid
                GROUP BY make) UNION (SELECT makeID AS MakeID, make
                FROM $this->pg_db.applications_pureflow_seo
                WHERE year = $yearVal
                GROUP BY make) ORDER BY make ASC");

            
        } elseif ($projectname == 'highflow' && ($yearVal==$next_year || $yearVal==$this->current_year)) {

           // Combine both queries using UNION
           $results = DB::connection(self::$aces_vcdb)
                ->select("(SELECT make.MakeID AS MakeID, make.MakeName AS make
                FROM basevehicle
                JOIN make ON basevehicle.MakeID = make.MakeID 
                JOIN model ON basevehicle.ModelID = model.ModelID 
                JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID 
                WHERE basevehicle.YearID = $yearVal AND vehicletype.VehicleTypeGroupID = $vehicletypeid
                GROUP BY make) UNION (SELECT makeID AS MakeID, make
                FROM $this->pg_db.applications_highflow_seo
                WHERE year = $yearVal
                GROUP BY make) ORDER BY make ASC");

        }
        else {

            $results = DB::connection(self::$aces_vcdb)
                ->table('basevehicle')
                ->join('make', 'basevehicle.MakeID', '=', 'make.MakeID')
                ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
                ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
                ->select('make.MakeID as MakeID', 'make.MakeName as make')->distinct()
                ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
                ->where('basevehicle.YearID', $yearVal)
                ->groupBy('make.MakeName')
                ->orderBy('make.MakeName', 'ASC')
                ->get();
        }

        return $results;

    }

    public function getmakes(Request $request, $vehicletypeid, $yearVal, $projectname = null)
    {
        $vehicletypeid = (int) $vehicletypeid;
        $yearVal = (int) $yearVal;
        $next_year = $this->current_year + 1;
        $isRecentYear = in_array($yearVal, [$this->current_year, $next_year], true);

        if ($isRecentYear && in_array($projectname, ['pureflow', 'highflow'], true)) {
            $seoTable = $projectname === 'pureflow' ? 'applications_pureflow_seo' : 'applications_highflow_seo';
            $results = DB::connection(self::$aces_vcdb)->select(
                "(SELECT make.MakeID AS MakeID, make.MakeName AS make
                FROM basevehicle
                JOIN make ON basevehicle.MakeID = make.MakeID
                JOIN model ON basevehicle.ModelID = model.ModelID
                JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID
                WHERE basevehicle.YearID = ? AND vehicletype.VehicleTypeGroupID = ?
                GROUP BY make.MakeID, make.MakeName)
                UNION
                (SELECT makeID AS MakeID, make
                FROM {$this->pg_db}.{$seoTable}
                WHERE year = ?
                GROUP BY makeID, make)
                ORDER BY make ASC",
                [$yearVal, $vehicletypeid, $yearVal]
            );
        } else {
            $results = DB::connection(self::$aces_vcdb)
                ->table('basevehicle')
                ->join('make', 'basevehicle.MakeID', '=', 'make.MakeID')
                ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
                ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
                ->select('make.MakeID as MakeID', 'make.MakeName as make')
                ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
                ->where('basevehicle.YearID', $yearVal)
                ->groupBy('make.MakeID', 'make.MakeName')
                ->orderBy('make.MakeName', 'ASC')
                ->get();
        }

        return $results;
    }

    public function getmodelsBK(Request $request, $vehicletypeid, $yearVal, $makeVal, $projectname=null)
    {


        $next_year = $this->current_year + 1;

        if ($projectname == 'pureflow' && ($yearVal==$next_year || $yearVal==$this->current_year)) {

            // Combine both queries using UNION
            $results = DB::connection(self::$aces_vcdb)
                ->select("(SELECT model.ModelName AS model, basevehicle.BaseVehicleID
                    FROM basevehicle 
                    JOIN model ON basevehicle.ModelID = model.ModelID 
                    JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID
                    WHERE vehicletype.VehicleTypeGroupID = $vehicletypeid AND basevehicle.YearID = $yearVal 
                    AND basevehicle.MakeID = $makeVal GROUP BY model) 
                    UNION (SELECT model, BaseVehicleID 
                    FROM $this->pg_db.applications_pureflow_seo WHERE year = $yearVal AND makeID = $makeVal 
                    GROUP BY model)  ORDER BY model ASC");
                
            
        } elseif ($projectname == 'highflow' && ($yearVal==$next_year || $yearVal==$this->current_year)) {

           // Combine both queries using UNION
           $results = DB::connection(self::$aces_vcdb)
                ->select("(SELECT model.ModelName AS model, basevehicle.BaseVehicleID
                FROM basevehicle 
                JOIN model ON basevehicle.ModelID = model.ModelID 
                JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID
                WHERE vehicletype.VehicleTypeGroupID = $vehicletypeid AND basevehicle.YearID = $yearVal 
                AND basevehicle.MakeID = $makeVal GROUP BY model) 
                UNION (SELECT model, BaseVehicleID 
                FROM $this->pg_db.applications_highflow_seo WHERE year = $yearVal AND makeID = $makeVal 
                GROUP BY model)  ORDER BY model ASC");
        }
        else {

            $results = DB::connection(self::$aces_vcdb)
                ->table('basevehicle')
                ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
                ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
                ->select('model.ModelName as model', 'basevehicle.BaseVehicleID')->distinct()
                ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
                ->where('basevehicle.YearID', $yearVal)
                ->where('basevehicle.MakeID', $makeVal)
                ->groupBy('model.ModelName')
                ->orderBy('model.ModelName', 'ASC')
                ->get();
        }

        return $results;

    }

    public function getmodels(Request $request, $vehicletypeid, $yearVal, $makeVal, $projectname = null)
    {
        $vehicletypeid = (int) $vehicletypeid;
        $yearVal = (int) $yearVal;
        $makeVal = (int) $makeVal;
        $next_year = $this->current_year + 1;
        $isRecentYear = in_array($yearVal, [$this->current_year, $next_year], true);

        if ($isRecentYear && in_array($projectname, ['pureflow', 'highflow'], true)) {
            $seoTable = $projectname === 'pureflow' ? 'applications_pureflow_seo' : 'applications_highflow_seo';
            $results = DB::connection(self::$aces_vcdb)->select(
                "(SELECT model.ModelName AS model, basevehicle.BaseVehicleID
                FROM basevehicle
                JOIN model ON basevehicle.ModelID = model.ModelID
                JOIN vehicletype ON model.VehicleTypeID = vehicletype.VehicleTypeID
                WHERE vehicletype.VehicleTypeGroupID = ? AND basevehicle.YearID = ? AND basevehicle.MakeID = ?
                GROUP BY model.ModelID, model.ModelName, basevehicle.BaseVehicleID)
                UNION
                (SELECT model, BaseVehicleID
                FROM {$this->pg_db}.{$seoTable}
                WHERE year = ? AND makeID = ?
                GROUP BY model, BaseVehicleID)
                ORDER BY model ASC",
                [$vehicletypeid, $yearVal, $makeVal, $yearVal, $makeVal]
            );
        } else {
            $results = DB::connection(self::$aces_vcdb)
                ->table('basevehicle')
                ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
                ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
                ->select('model.ModelName as model', 'basevehicle.BaseVehicleID')
                ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
                ->where('basevehicle.YearID', $yearVal)
                ->where('basevehicle.MakeID', $makeVal)
                ->groupBy('model.ModelName', 'basevehicle.BaseVehicleID')
                ->orderBy('model.ModelName', 'ASC')
                ->get();
        }

        return $results;
    }


    public function checkforengine(Request $request, $baseVehicleId)
    {
        $enginetermcheck = [];

        //send EngineBaseID as EngineConfigID(as EngineConfigID label used in frontend)
        $enginetermcheck = DB::connection(self::$aces_vcdb)
            ->select("SELECT GROUP_CONCAT(DISTINCT eb.EngineBaseID) as EngineConfigID,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
                            FROM basevehicle bv JOIN vehicle v ON bv.BaseVehicleID = v.BaseVehicleID 
                            JOIN vehicletoengineconfig vtec ON v.VehicleID = vtec.VehicleID 
                            JOIN engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID 
                            JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
                            LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
                            WHERE bv.BaseVehicleID = $baseVehicleId 
                            GROUP BY engine ORDER BY engine");


        if ($enginetermcheck != null) {
            foreach ($enginetermcheck as $key => $value) {
                if ($value->engine == "") {
                    $value->engine = "-L";
                }
            }

        }

        return json_encode($enginetermcheck);
    }




    public function checkforengineallpart(Request $request, $baseVehicleId)
    {
        $enginetermcheck = [];

        //send EngineBaseID as EngineConfigID(as EngineConfigID label used in frontend)
        $enginetermcheck = DB::connection(self::$aces_vcdb)
            ->select("SELECT GROUP_CONCAT(DISTINCT eb.EngineBaseID) as EngineConfigID,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
                            FROM basevehicle bv JOIN vehicle v ON bv.BaseVehicleID = v.BaseVehicleID 
                            JOIN vehicletoengineconfig vtec ON v.VehicleID = vtec.VehicleID 
                            JOIN engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID 
                            JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
                            LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
                            WHERE bv.BaseVehicleID = $baseVehicleId 
                            GROUP BY engine ORDER BY engine");


        //To show dropdown -- Scenario -- If all partnumbers are same for each engine value - then do not show engine dropdown

        //By default value - Show
        $show = 'true';

        //showdropdown key will not be present if enginetermcheck count is empty OR one
        if (!empty($enginetermcheck) && count($enginetermcheck) > 1) {


            $count_of_result = [];
            $result = [];

            foreach ($enginetermcheck as $etc) {

                $engineId = $etc->EngineConfigID;

                $partTypeArray = DB::connection(self::$main_db)
                    ->select("SELECT p.partNumber 
                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) 
                    GROUP BY p.partNumber");

                $final_result = collect($partTypeArray)->pluck('partNumber')->toArray();

                array_push($result, $final_result);
            }

            function areArraysIdentical($arrays)
            {

                $sortedArrays = $arrays; // Make a copy to keep the original arrays intact
                array_walk($sortedArrays, 'sort');

                $firstSortedArray = $sortedArrays[0];

                $same_value = 1;
                foreach ($sortedArrays as $sortedArray) {

                    //if partnumbers from first array are not matching with other 2 arrays -- Break the loop
                    if ($sortedArray !== $firstSortedArray) {
                        $same_value = 0;
                        break;
                    } else {
                        continue;
                    }


                }

                return $same_value;
            }



            // If partnumbers from first array are matching with other 2 arrays(all same) - Then do not show engine dropdown  
            if (areArraysIdentical($result)) {
                $show = 'false';
            }

            $newObject = new stdClass();
            $newObject->showdropdown = $show;
            $newObject->engine = 'engineval';


            array_push($enginetermcheck, $newObject);


        }

        return json_encode($enginetermcheck);
    }





    public function enginecheckpureflow(Request $request, $baseVehicleId)
    {


        $enginetermcheck = [];

        //send EngineBaseID as EngineConfigID(as EngineConfigID label used in frontend)
        $enginetermcheck = DB::connection(self::$aces_vcdb)
            ->select("SELECT GROUP_CONCAT(DISTINCT eb.EngineBaseID) as EngineConfigID,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
            FROM basevehicle bv JOIN vehicle v ON bv.BaseVehicleID = v.BaseVehicleID 
            JOIN vehicletoengineconfig vtec ON v.VehicleID = vtec.VehicleID 
            JOIN engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID 
            JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
            LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
            WHERE bv.BaseVehicleID = $baseVehicleId 
            GROUP BY engine ORDER BY engine");

        //To show dropdown -- Scenario -- If all partnumbers are same for each engine value - then do not show engine dropdown

        //By default value - Show
        $show = 'true';

        //showdropdown key will not be present if enginetermcheck count is empty OR one
        if (!empty($enginetermcheck) && count($enginetermcheck) > 1) {


            $count_of_result = [];
            $result = [];

            foreach ($enginetermcheck as $etc) {

                $engineId = $etc->EngineConfigID;

                $partTypeArray = DB::connection(self::$main_db)
                    ->select("SELECT p.partNumber 
                    FROM partNumberInfo p JOIN applications a ON p.part_id=a.part_id 
                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND a.MfrLabel LIKE '%pureflow%' 
                    GROUP BY p.partNumber");



                $final_result = collect($partTypeArray)->pluck('partNumber')->toArray();

                array_push($result, $final_result);
            }


            function areArraysIdentical1($arrays)
            {

                $sortedArrays = $arrays; // Make a copy to keep the original arrays intact
                array_walk($sortedArrays, 'sort');


                $firstSortedArray = $sortedArrays[0];

                $same_value = 1;
                foreach ($sortedArrays as $sortedArray) {

                    //if partnumbers from first array are not matching with other 2 arrays -- Break the loop
                    if ($sortedArray !== $firstSortedArray) {
                        $same_value = 0;
                        break;
                    } else {
                        continue;
                    }


                }

                return $same_value;
            }



            $qualifier_result = [];
            // If partnumbers from first array are matching with other 2 arrays(all same)  
            if (areArraysIdentical1($result)) {

                foreach ($enginetermcheck as $etc) {
                    $engineId = $etc->EngineConfigID;
                    //check if partnumbers are same for all qualifier values (for each engine passed)
                    $qua_result1 = (new QualifierController())->qualifiercheckinengine($request, $baseVehicleId, $engineId, 'noqualifier', 0, 0);

                    array_push($qualifier_result, $qua_result1);

                }

                //if any one of the engine value has (qulifiers to show(then it returns true))

                if (in_array("true", $qualifier_result)) {
                    $show = 'true';
                } else {
                    $show = 'false';
                }

            }

            $newObject = new stdClass();
            $newObject->showdropdown = $show;
            $newObject->engine = 'engineval';


            array_push($enginetermcheck, $newObject);


        }

        return json_encode($enginetermcheck);
    }

    public function getengines(Request $request, $baseVehicleId)
    {

        //send EngineBaseID as EngineConfigID(as EngineConfigID label used in frontend)
        return $results = DB::connection(self::$aces_vcdb)
            ->select(
                DB::raw("SELECT CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine,GROUP_CONCAT(DISTINCT eb.EngineBaseID) AS EngineConfigID FROM vehicle v 
                        JOIN vehicletoengineconfig vtec ON v.VehicleID = vtec.VehicleID AND v.BaseVehicleID = :baseVehicleId
                        JOIN engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
                        LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID GROUP BY engine"),
                array(
                    'baseVehicleId' => $baseVehicleId
                )
            );
    }


    //Product page
    public function productpageBK(Request $request, $partNumber)
    {
        // $product_specs=DB::connection(self::$main_db)
        //                         ->select("SELECT ms.PAName,mp.value,ms.PAID 
        //                         FROM part_attributes_mapper mp LEFT JOIN part_attributes_master ms ON ms.PAID=mp.PAID 
        //                         WHERE partNumber='$partNumber'  
        //                         GROUP BY ms.PAName 
        //                         ORDER  BY FIELD(ms.PAName,'Shape','Gasket Material','Anti-Drain Back Valve','Thread Size','Bypass Relief Valve','Burst Pressure','Micron Rating','Gasket Thickness','Gasket Outside Diameter','Gasket Inside Diameter','Outside Diameter','Height','Width','Length','Side C Length','Side B Length','Side A Length')DESC");

        $product_specs = DB::connection(self::$main_db)
            ->select(
                DB::raw("SELECT ms.PAName, mp.value, ms.PAID
                    FROM part_attributes_mapper mp
                    LEFT JOIN part_attributes_master ms ON ms.PAID = mp.PAID
                    JOIN partNumberInfo pni ON mp.part_id = pni.part_id
                    WHERE pni.partNumber = '$partNumber'
                    GROUP BY ms.PAName, mp.value, ms.PAID
                    ORDER  BY FIELD(ms.PAName,'Shape','Gasket Material','Anti-Drain Back Valve',
                    'Thread Size','Bypass Relief Valve','Burst Pressure','Micron Rating',
                    'Gasket Thickness','Gasket Outside Diameter','Gasket Inside Diameter',
                    'Outside Diameter','Height','Width','Length','Side C Length','Side B Length','Side A Length') DESC")
            );

        $PAID = array(14, 19, 24, 25, 31, 522, 531, 534, 568, 569, 570, 576, 577, 578, 579, 1908, 1172, 9035, 9036);
        $attributes_array = array();
        $flag_height = 0;

        if (!empty($product_specs)) {

            foreach ($product_specs as $product_specs1) {

                if ($product_specs1->PAID == 19 && $flag_height == 1) {
                    continue;
                }

                $spec_value1 = '';
                $spec_value1 = strtoupper($product_specs1->value);

                //e.g 6.31 IN , 6.31IN
                if (preg_match('/^(\d+\.?\d*)IN$/', $spec_value1) == true || preg_match('/^(\d+\.?\d*) IN$/', $spec_value1) == true) {
                    $spec_value = str_replace('IN', '', $spec_value1);

                } else {
                    $spec_value = $product_specs1->value;
                }



                if (in_array($product_specs1->PAID, $PAID) && strpos($spec_value1, '/min') === false) {
                    $reg_line = false;
                    $reg_line = preg_match('/[|,±?\/]/', $spec_value); // check if | , / present in string
                    if (strpos($spec_value1, 'MM') == true && strpos($spec_value1, 'IN') == false) {

                        if (!$reg_line) {
                            $split_value_MM = explode("MM", $product_specs1->value);
                            $spec_value = trim($split_value_MM[0]);

                            if (is_numeric($spec_value)) {
                                $spec_value = $spec_value / 25.4; //convert mm value to inch
                            }
                        } elseif ($reg_line) {


                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                if (strpos($spec_value1, '±') !== false) {
                                    $spec_value = $components[0];
                                    if (is_numeric($spec_value)) {
                                        $spec_value = $spec_value / 25.4;
                                        $spec_value = round($spec_value, 2);
                                    } else {
                                        $spec_value = round($components[0], 2);
                                    }
                                } else {
                                    $spec_value = round($components[0], 2);
                                }

                            }
                        }
                    } elseif (strpos($spec_value1, 'IN') !== false) {
                        $spec_value = str_replace('IN', '', $spec_value1);
                        if ($reg_line) {

                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                $spec_value = round($components[0], 2);
                            }
                        }
                    } else {
                        if ($reg_line) {

                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                $spec_value = round($components[0], 2);
                            }
                        }
                    }

                }

                $split_value_PAID = "";
                if ($product_specs1->PAID == 627 || $product_specs1->PAID == -627) {
                    if (strpos($spec_value, 'KG') !== false) {
                        $split_value_PAID = explode(" KG", $spec_value);
                        $split_value_PAID[0] = round($split_value_PAID[0], 2);
                        $spec_value = $split_value_PAID[0] * 2.205;
                        $spec_value = round($spec_value, 2);
                        $spec_value = $spec_value . " LBS";
                    } elseif (strpos($spec_value, 'PG') !== false) {
                        $split_value_PAID = explode("PG", $spec_value);
                        $split_value_PAID[0] = round($split_value_PAID[0], 2);
                        $spec_value = $split_value_PAID[0] * 2.20462E-15;
                        $spec_value = $spec_value * 1E15;
                        $spec_value = round($spec_value, 2);
                        $spec_value = $spec_value . " LBS";
                    } else {
                        $spec_value = $spec_value;
                    }
                }


                if ($product_specs1->PAID == 31) {
                    $flag_height = 1;
                }
                $product_specs1->product_specs_name = $product_specs1->PAName;


                //value

                if (in_array($product_specs1->PAID, $PAID) && strpos($spec_value1, '/min') === false) {

                    //if '±' not present in the string and no two '.' present in string
                    $reg_exp = preg_match('/[a-zA-Z\"\'\/^±£$%&*()}{@#~?><>,|=_+¬-]/', $spec_value);
                    if (!$reg_exp && substr_count($spec_value, '.') < 2) {
                        $product_specs1->product_specs_value = round($spec_value, 2);
                    } else {
                        $product_specs1->product_specs_value = ucwords(trim($spec_value));
                    }
                } else {
                    $product_specs1->product_specs_value = ucwords(trim($spec_value));
                }
            }
        }

        $results["product_specs"] = $product_specs;

        $imageInfo = DB::connection(self::$main_db)
            // ->select("SELECT img_id,partNumber,fileName,flag from image_mapper WHERE partNumber='$partNumber'
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
            //     ELSE 21 END");
            ->select(
                "SELECT img_id, pni.partNumber, fileName FROM image_mapper im
                JOIN partNumberInfo pni ON im.part_id = pni.part_id
                WHERE pni.partNumber = ?
                GROUP BY im.fileName
                ORDER BY CASE
                WHEN fileName LIKE '%ZZ2_FRO.%' THEN 1 WHEN fileName LIKE '%P02_.%' THEN 2
                WHEN fileName LIKE '%P04_FRO.%' THEN 3 WHEN fileName LIKE '%P01_BAC.%' THEN 4
                WHEN fileName LIKE '%P01_TOP.%' THEN 5 WHEN fileName LIKE '%P01_BOT.%' THEN 6
                WHEN fileName LIKE '%P01_SID.%' THEN 7 WHEN fileName LIKE '%P01_LEF.%' THEN 8
                WHEN fileName LIKE '%P01_RIG.%' THEN 9 WHEN fileName LIKE '%P01_ANG.%' THEN 10
                WHEN fileName LIKE '%-F.%' THEN 11 WHEN fileName LIKE '%-E.%' THEN 12
                WHEN fileName LIKE '%-A.%' THEN 13 WHEN fileName LIKE '%-B.%' THEN 14
                WHEN fileName LIKE '%-C.%' THEN 15 WHEN fileName LIKE '%-D.%' THEN 16
                WHEN fileName LIKE '%-G.%' THEN 17 WHEN fileName LIKE '%-H.%' THEN 18
                WHEN fileName LIKE '%-K.%' THEN 19 WHEN fileName LIKE '%-I.%' THEN 20
                ELSE 21 END",
                [$partNumber]
            );

        // if(!empty($imageInfo)) {

        //     $image_count=0;

        //     foreach ($imageInfo as $key=>$imageInfo1) {

        //         $remoteFileUrl = $this->image_path.$imageInfo1->fileName;

        //         $options = [
        //             'http' => [
        //                 'method' => 'HEAD',
        //                 'ignore_errors' => true,
        //             ],
        //         ];
        //         $context = stream_context_create($options);

        //         $response = file_get_contents($remoteFileUrl, false, $context);

        //         if ($response !== false) {
        //             // Check the response status code
        //             $headers = $http_response_header; // This variable contains the response headers
        //             $statusCode = explode(' ', $headers[0])[1];
        //             if ($statusCode === '200') {
        //                 $image_count++;
        //             } else {
        //                 unset($imageInfo[$key]);
        //             }
        //         } else {
        //             unset($imageInfo[$key]);
        //         }

        //     }

        // }
        $results["imageInfo"] = array_values($imageInfo);

        $results["package"] = DB::connection(self::$main_db)
            // ->select("SELECT height,width,length,weight 
            // FROM package WHERE partNumber='$partNumber'");
            ->select("SELECT height,width,length,weight 
                                FROM package
                                JOIN partNumberInfo pni ON package.part_id = pni.part_id
                                WHERE pni.partNumber='$partNumber' GROUP BY pni.partNumber");

        $results["installation_instructions"] = DB::connection(self::$main_db)
            ->select("SELECT pr.resources_path FROM pgfilter_resources pr 
                JOIN pgfilter_resources_mapper prm ON pr.id = prm.resources_mapper_id 
                WHERE prm.resource_partnumber='$partNumber' AND pr.resource_type='4'");

        $results["product_bulletins"] = DB::connection(self::$main_db)
            ->select("SELECT pr.resources_path 
                FROM pgfilter_resources pr JOIN pgfilter_resources_mapper prm ON pr.id = prm.resources_mapper_id 
                JOIN wp_bsk_pdf_manager_pdfs bsk ON pr.resources_path=bsk.file_name
                WHERE prm.resource_partnumber='$partNumber' AND pr.resource_type='1' AND bsk.trash='0'");

        return json_encode($results);
    }

    public function productpage(Request $request, $partNumber)
    {
        set_time_limit(0);
        $partNumber = trim($partNumber ?? '');
        if ($partNumber === '') {
            return response()->json(['product_specs' => [], 'imageInfo' => [], 'package' => [], 'installation_instructions' => [], 'product_bulletins' => []]);
        }

        $product_specs = DB::connection(self::$main_db)->select(
            "SELECT ms.PAName, mp.value, ms.PAID
            FROM part_attributes_mapper mp
            LEFT JOIN part_attributes_master ms ON ms.PAID = mp.PAID
            JOIN partNumberInfo pni ON mp.part_id = pni.part_id
            WHERE pni.partNumber = ?
            GROUP BY ms.PAName, mp.value, ms.PAID
            ORDER BY FIELD(ms.PAName,'Shape','Gasket Material','Anti-Drain Back Valve',
                'Thread Size','Bypass Relief Valve','Burst Pressure','Micron Rating',
                'Gasket Thickness','Gasket Outside Diameter','Gasket Inside Diameter',
                'Outside Diameter','Height','Width','Length','Side C Length','Side B Length','Side A Length') DESC",
            [$partNumber]
        );

        $PAID = [14, 19, 24, 25, 31, 522, 531, 534, 568, 569, 570, 576, 577, 578, 579, 1908, 1172, 9035, 9036];
        $flag_height = 0;

        if (!empty($product_specs)) {

            foreach ($product_specs as $product_specs1) {

                if ($product_specs1->PAID == 19 && $flag_height == 1) {
                    continue;
                }

                $spec_value1 = '';
                $spec_value1 = strtoupper($product_specs1->value);

                //e.g 6.31 IN , 6.31IN
                if (preg_match('/^(\d+\.?\d*)IN$/', $spec_value1) == true || preg_match('/^(\d+\.?\d*) IN$/', $spec_value1) == true) {
                    $spec_value = str_replace('IN', '', $spec_value1);

                } else {
                    $spec_value = $product_specs1->value;
                }



                if (in_array($product_specs1->PAID, $PAID) && strpos($spec_value1, '/min') === false) {
                    $reg_line = false;
                    $reg_line = preg_match('/[|,±?\/]/', $spec_value); // check if | , / present in string
                    if (strpos($spec_value1, 'MM') == true && strpos($spec_value1, 'IN') == false) {

                        if (!$reg_line) {
                            $split_value_MM = explode("MM", $product_specs1->value);
                            $spec_value = trim($split_value_MM[0]);

                            if (is_numeric($spec_value)) {
                                $spec_value = $spec_value / 25.4; //convert mm value to inch
                            }
                        } elseif ($reg_line) {


                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                if (strpos($spec_value1, '±') !== false) {
                                    $spec_value = $components[0];
                                    if (is_numeric($spec_value)) {
                                        $spec_value = $spec_value / 25.4;
                                        $spec_value = round($spec_value, 2);
                                    } else {
                                        $spec_value = round($components[0], 2);
                                    }
                                } else {
                                    $spec_value = round($components[0], 2);
                                }

                            }
                        }
                    } elseif (strpos($spec_value1, 'IN') !== false) {
                        $spec_value = str_replace('IN', '', $spec_value1);
                        if ($reg_line) {

                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                $spec_value = round($components[0], 2);
                            }
                        }
                    } else {
                        if ($reg_line) {

                            $components = preg_split('/[|,±?\/]/', $spec_value);

                            if (count($components) > 0 && $components[0] != $spec_value) {
                                $spec_value = round($components[0], 2);
                            }
                        }
                    }

                }

                $split_value_PAID = "";
                if ($product_specs1->PAID == 627 || $product_specs1->PAID == -627) {
                    if (strpos($spec_value, 'KG') !== false) {
                        $split_value_PAID = explode(" KG", $spec_value);
                        $split_value_PAID[0] = round($split_value_PAID[0], 2);
                        $spec_value = $split_value_PAID[0] * 2.205;
                        $spec_value = round($spec_value, 2);
                        $spec_value = $spec_value . " LBS";
                    } elseif (strpos($spec_value, 'PG') !== false) {
                        $split_value_PAID = explode("PG", $spec_value);
                        $split_value_PAID[0] = round($split_value_PAID[0], 2);
                        $spec_value = $split_value_PAID[0] * 2.20462E-15;
                        $spec_value = $spec_value * 1E15;
                        $spec_value = round($spec_value, 2);
                        $spec_value = $spec_value . " LBS";
                    } else {
                        $spec_value = $spec_value;
                    }
                }


                if ($product_specs1->PAID == 31) {
                    $flag_height = 1;
                }
                $product_specs1->product_specs_name = $product_specs1->PAName;


                //value

                if (in_array($product_specs1->PAID, $PAID) && strpos($spec_value1, '/min') === false) {

                    //if '±' not present in the string and no two '.' present in string
                    $reg_exp = preg_match('/[a-zA-Z\"\'\/^±£$%&*()}{@#~?><>,|=_+¬-]/', $spec_value);
                    if (!$reg_exp && substr_count($spec_value, '.') < 2) {
                        $product_specs1->product_specs_value = round($spec_value, 2);
                    } else {
                        $product_specs1->product_specs_value = ucwords(trim($spec_value));
                    }
                } else {
                    $product_specs1->product_specs_value = ucwords(trim($spec_value));
                }
            }
        }

        $results["product_specs"] = $product_specs;

        $imageInfo = DB::connection(self::$main_db)->select(
            "SELECT img_id, pni.partNumber, fileName FROM image_mapper im
            JOIN partNumberInfo pni ON im.part_id = pni.part_id
            WHERE pni.partNumber = ?
            GROUP BY im.fileName
            ORDER BY CASE
                WHEN fileName LIKE '%ZZ2_FRO.%' THEN 1 WHEN fileName LIKE '%P02_.%' THEN 2
                WHEN fileName LIKE '%P04_FRO.%' THEN 3 WHEN fileName LIKE '%P01_BAC.%' THEN 4
                WHEN fileName LIKE '%P01_TOP.%' THEN 5 WHEN fileName LIKE '%P01_BOT.%' THEN 6
                WHEN fileName LIKE '%P01_SID.%' THEN 7 WHEN fileName LIKE '%P01_LEF.%' THEN 8
                WHEN fileName LIKE '%P01_RIG.%' THEN 9 WHEN fileName LIKE '%P01_ANG.%' THEN 10
                WHEN fileName LIKE '%-F.%' THEN 11 WHEN fileName LIKE '%-E.%' THEN 12
                WHEN fileName LIKE '%-A.%' THEN 13 WHEN fileName LIKE '%-B.%' THEN 14
                WHEN fileName LIKE '%-C.%' THEN 15 WHEN fileName LIKE '%-D.%' THEN 16
                WHEN fileName LIKE '%-G.%' THEN 17 WHEN fileName LIKE '%-H.%' THEN 18
                WHEN fileName LIKE '%-K.%' THEN 19 WHEN fileName LIKE '%-I.%' THEN 20
                ELSE 21 END",
            [$partNumber]
        );

        // if(!empty($imageInfo)) {

        //     $image_count=0;

        //     foreach ($imageInfo as $key=>$imageInfo1) {

        //         $remoteFileUrl = $this->image_path.$imageInfo1->fileName;

        //         $options = [
        //             'http' => [
        //                 'method' => 'HEAD',
        //                 'ignore_errors' => true,
        //             ],
        //         ];
        //         $context = stream_context_create($options);

        //         $response = file_get_contents($remoteFileUrl, false, $context);

        //         if ($response !== false) {
        //             // Check the response status code
        //             $headers = $http_response_header; // This variable contains the response headers
        //             $statusCode = explode(' ', $headers[0])[1];
        //             if ($statusCode === '200') {
        //                 $image_count++;
        //             } else {
        //                 unset($imageInfo[$key]);
        //             }
        //         } else {
        //             unset($imageInfo[$key]);
        //         }

        //     }

        // }
        $results["imageInfo"] = array_values($imageInfo);

        $results["package"] = DB::connection(self::$main_db)->select(
            "SELECT height, width, length, weight FROM package
            JOIN partNumberInfo pni ON package.part_id = pni.part_id
            WHERE pni.partNumber = ? GROUP BY pni.partNumber",
            [$partNumber]
        );

        $results["installation_instructions"] = DB::connection(self::$main_db)->select(
            "SELECT pr.resources_path FROM pgfilter_resources pr
            JOIN pgfilter_resources_mapper prm ON pr.id = prm.resources_mapper_id
            WHERE prm.resource_partnumber = ? AND pr.resource_type = '4'",
            [$partNumber]
        );

        $results["product_bulletins"] = DB::connection(self::$main_db)->select(
            "SELECT pr.resources_path FROM pgfilter_resources pr
            JOIN pgfilter_resources_mapper prm ON pr.id = prm.resources_mapper_id
            JOIN wp_bsk_pdf_manager_pdfs bsk ON pr.resources_path = bsk.file_name
            WHERE prm.resource_partnumber = ? AND pr.resource_type = '1' AND bsk.trash = '0'",
            [$partNumber]
        );

        return response()->json($results);
    }


    //Application API


    function group_nums($array)
    {
        $ret = array();
        $temp = array();
        foreach ($array as $val) {
            if (next($array) == ($val + 1))
                $temp[] = $val;
            else

                if (count($temp) > 0) {
                    $temp[] = $val;
                    $ret[] = $temp[0] . ':' . end($temp);
                    $temp = array();
                } else
                    $ret[] = $val;
        }
        return $ret;
    }




    public function prodapplication(Request $request, $partNumber)
    {
        //send EngineBaseID as EngineConfigID(as EngineConfigID label used in frontend)
        $results["product"] = DB::connection(self::$aces_vcdb)
            // ->select("SELECT DISTINCT CAST(a.YearID AS char) as year,TRIM(ma.MakeName) AS make,a.MakeID,a.ModelID,GROUP_CONCAT(DISTINCT a.EngineBaseID) AS EngineConfigID,TRIM(mo.ModelName) AS model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine,COUNT(a.id) AS numberofrec,min(a.YearID) AS minyear,max(a.YearID) AS maxyear 
            // FROM $this->pg_db.applications a JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
            // JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
            // JOIN $this->pg_secdb.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
            // JOIN $this->pg_secdb.enginebase eb ON a.EngineBaseID = eb.EngineBaseID 
            // LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
            // WHERE a.partnumber='$partNumber' 
            // GROUP BY make,model,engine ORDER BY make,model,engine");
            ->select("SELECT DISTINCT CAST(a.YearID AS char) as year,TRIM(ma.MakeName) AS make,a.MakeID,a.ModelID,GROUP_CONCAT(DISTINCT a.EngineBaseID) AS EngineConfigID,TRIM(mo.ModelName) AS model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine,COUNT(a.id) AS numberofrec,min(a.YearID) AS minyear,max(a.YearID) AS maxyear 
                            FROM $this->pg_db.applications a
                            JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                            JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                            JOIN $this->pg_secdb.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
                            JOIN $this->pg_secdb.enginebase eb ON a.EngineBaseID = eb.EngineBaseID 
                            LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID
                            JOIN $this->pg_db.partNumberInfo pni ON a.part_id = pni.part_id
                            WHERE pni.partNumber='$partNumber'
                            GROUP BY make,model,engine ORDER BY make,model,engine");

        return json_encode($results);
    }


    // public function prodapplication_newBK(Request $request, $partNumber)
    // {

    //     $product = DB::connection(self::$aces_vcdb)
    //         // ->select("SELECT STRAIGHT_JOIN a.YearID AS year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) AS model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine
    //         // FROM $this->pg_db.applications a JOIN make ma ON a.MakeID = ma.MakeID 
    //         // JOIN model mo ON a.ModelID = mo.ModelID 
    //         // JOIN engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
    //         // JOIN enginebase eb ON a.EngineBaseID = eb.EngineBaseID 
    //         // LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
    //         // WHERE a.partnumber='$partNumber' 
    //         // GROUP BY year,make,model,engine  
    //         // ORDER BY make,model,engine,year");
    //         ->select("SELECT a.YearID AS year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) AS model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine
    //             FROM $this->pg_db.applications a
    //             JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
    //             JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
    //             JOIN $this->pg_secdb.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
    //             JOIN $this->pg_secdb.enginebase eb ON a.EngineBaseID = eb.EngineBaseID 
    //             LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
    //             JOIN $this->pg_db.partNumberInfo pni ON a.part_id = pni.part_id
    //             WHERE pni.partNumber='$partNumber' 
    //             GROUP BY year,make,model,engine  
    //             ORDER BY make,model,engine,year");




    //     if (!empty($product)) {

    //         $year_range = [];
    //         $products_final = [];

    //         foreach ($product as $keys => $value) {


    //             $reuse_code = false;

    //             $current_year = $value->year;
    //             $current_mme = $value->make . " " . $value->model . " " . $value->engine;

    //             if (isset($product[$keys + 1])) {

    //                 $next_row = $product[$keys + 1];
    //                 $next_mme = $next_row->make . " " . $next_row->model . " " . $next_row->engine;

    //                 if ($current_mme == $next_mme) {

    //                     $next_year = $next_row->year;
    //                     if (($current_year + 1) == $next_year) {
    //                         array_push($year_range, $current_year);
    //                     } else {
    //                         $reuse_code = true;
    //                     }
    //                 } else {
    //                     $reuse_code = true;
    //                 }
    //             } else {
    //                 $reuse_code = true;
    //             }


    //             //if mme is different,last element of array,if ymm same but range years not incremented by 1
    //             if ($reuse_code == true) {

    //                 if (count($year_range) > 0) {

    //                     array_push($year_range, $current_year);

    //                     $min = min($year_range);
    //                     $max = max($year_range);
    //                     $min_max = $min . "-" . $max;


    //                     $value->year = $min_max;

    //                     $year_range = [];

    //                 } else {
    //                     $value->year = $current_year;
    //                 }

    //                 array_push($products_final, $value);
    //             }

    //         }

    //     }


    //     $results["applications"] = $products_final;

    //     return json_encode($results);
    // }

    public function prodapplication_new(Request $request, $partNumber)
    {
        set_time_limit(300);
        $partNumber = trim($partNumber ?? '');
        if ($partNumber === '') {
            return response()->json(['applications' => []]);
        }

        $product = DB::connection(self::$aces_vcdb)->select(
            "SELECT a.YearID AS year, TRIM(ma.MakeName) AS make, TRIM(mo.ModelName) AS model,
                CONCAT_WS(' ', CONCAT(NULLIF(TRIM(eb.Liter),'-'), 'L'), CONCAT(NULLIF(TRIM(eb.Cylinders), '-'), 'Cyl'),
                NULLIF(TRIM(eb.BlockType), '-'), NULLIF(ft.EngineDisplay, '-')) AS engine
            FROM {$this->pg_db}.applications a
            JOIN {$this->pg_secdb}.make ma ON a.MakeID = ma.MakeID
            JOIN {$this->pg_secdb}.model mo ON a.ModelID = mo.ModelID
            JOIN {$this->pg_secdb}.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID
            JOIN {$this->pg_secdb}.enginebase eb ON a.EngineBaseID = eb.EngineBaseID
            LEFT JOIN {$this->pg_db}.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID
            JOIN {$this->pg_db}.partNumberInfo pni ON a.part_id = pni.part_id
            WHERE pni.partNumber = ?
            GROUP BY year, make, model, engine
            ORDER BY make, model, engine, year",
            [$partNumber]
        );

        $products_final = [];
        $year_range = [];

        foreach ($product as $keys => $value) {
            $current_year = (int) $value->year;
            $current_mme = $value->make . ' ' . $value->model . ' ' . $value->engine;

            $reuse_code = !isset($product[$keys + 1])
                || $current_mme !== ($product[$keys + 1]->make . ' ' . $product[$keys + 1]->model . ' ' . $product[$keys + 1]->engine)
                || ($current_year + 1) !== (int) $product[$keys + 1]->year;

            if ($reuse_code) {
                if (count($year_range) > 0) {
                    $year_range[] = $current_year;
                    $value->year = min($year_range) . '-' . max($year_range);
                    $year_range = [];
                } else {
                    $value->year = $current_year;
                }
                $products_final[] = $value;
            } else {
                $year_range[] = $current_year;
            }
        }

        return response()->json(['applications' => $products_final]);
    }


    public function prodapplication_countBK(Request $request, $partNumber)
    {

        $product_count = DB::connection(self::$aces_vcdb)
            // ->select("SELECT COUNT(*) as row_count FROM (SELECT DISTINCT CAST(a.YearID AS char) as year,TRIM(ma.MakeName) as make,a.MakeID,a.ModelID,GROUP_CONCAT(DISTINCT a.EngineBaseID) as EngineBaseID,TRIM(mo.ModelName) as model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine,COUNT(a.id) as numberofrec,min(a.YearID) as minyear,max(a.YearID) as maxyear 
            //     FROM $this->pg_db.applications a JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
            //     JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
            //     JOIN $this->pg_secdb.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
            //     JOIN $this->pg_secdb.enginebase eb ON a.EngineBaseID = eb.EngineBaseID 
            //     LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
            //     WHERE a.partnumber='$partNumber' 
            //     GROUP BY make,model,engine 
            //     ORDER BY make,model,engine ) as cnt");
            ->select("SELECT COUNT(*) as row_count FROM (SELECT DISTINCT CAST(a.YearID AS char) as year,TRIM(ma.MakeName) as make,a.MakeID,a.ModelID,GROUP_CONCAT(DISTINCT a.EngineBaseID) as EngineBaseID,TRIM(mo.ModelName) as model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'), CONCAT(NULLIF(TRIM(`eb`.`Cylinders`), '-'), 'Cyl'), NULLIF(TRIM(`eb`.`BlockType`), '-'),NULLIF(`ft`.`EngineDisplay`, '-')) AS engine,COUNT(a.id) as numberofrec,min(a.YearID) as minyear,max(a.YearID) as maxyear 
                FROM $this->pg_db.applications a
                JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                JOIN $this->pg_secdb.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID 
                JOIN $this->pg_secdb.enginebase eb ON a.EngineBaseID = eb.EngineBaseID
                LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID  
                JOIN $this->pg_db.partNumberInfo pni ON a.part_id = pni.part_id
                WHERE pni.partNumber='$partNumber'
                GROUP BY make,model,engine 
                ORDER BY make,model,engine ) as cnt");

        return $product_count;
    }

    public function prodapplication_count(Request $request, $partNumber)
    {
        $partNumber = trim($partNumber ?? '');
        if ($partNumber === '') {
            return response()->json([['row_count' => 0]]);
        }

        $product_count = DB::connection(self::$aces_vcdb)->select(
            "SELECT COUNT(*) as row_count FROM (
                SELECT DISTINCT CAST(a.YearID AS char) as year, TRIM(ma.MakeName) as make, a.MakeID, a.ModelID,
                GROUP_CONCAT(DISTINCT a.EngineBaseID) as EngineBaseID, TRIM(mo.ModelName) as model,
                CONCAT_WS(' ', CONCAT(NULLIF(TRIM(eb.Liter),'-'), 'L'), CONCAT(NULLIF(TRIM(eb.Cylinders), '-'), 'Cyl'),
                NULLIF(TRIM(eb.BlockType), '-'), NULLIF(ft.EngineDisplay, '-')) AS engine,
                COUNT(a.id) as numberofrec, min(a.YearID) as minyear, max(a.YearID) as maxyear
                FROM {$this->pg_db}.applications a
                JOIN {$this->pg_secdb}.make ma ON a.MakeID = ma.MakeID
                JOIN {$this->pg_secdb}.model mo ON a.ModelID = mo.ModelID
                JOIN {$this->pg_secdb}.engineconfig ec ON a.EngineConfigID = ec.EngineConfigID
                JOIN {$this->pg_secdb}.enginebase eb ON a.EngineBaseID = eb.EngineBaseID
                LEFT JOIN {$this->pg_db}.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID
                JOIN {$this->pg_db}.partNumberInfo pni ON a.part_id = pni.part_id
                WHERE pni.partNumber = ?
                GROUP BY make, model, engine
                ORDER BY make, model, engine
            ) as cnt",
            [$partNumber]
        );

        return response()->json($product_count);
    }


    public function prodapplication1(Request $request, $partNumber, $makeID, $modelID, $engineConfigID, $min, $max)
    {
        $results["partyear"] = DB::connection(self::$main_db)
            // ->select("SELECT DISTINCT YearID as year FROM applications 
            // WHERE partnumber='$partNumber' AND MakeID='$makeID' AND ModelID='$modelID' AND EngineBaseID IN($engineConfigID) AND YearID BETWEEN $min AND $max 
            // GROUP BY YearID ORDER BY YearID");
            ->select("SELECT DISTINCT YearID as year FROM applications
                            JOIN partNumberInfo pni ON applications.part_id = pni.part_id
                            WHERE pni.partNumber='$partNumber' AND MakeID='$makeID' AND ModelID='$modelID' AND EngineBaseID IN($engineConfigID) AND YearID BETWEEN $min AND $max 
                            GROUP BY YearID ORDER BY YearID");

        return json_encode($results);
    }


    //Interchanges API
    public function partinterchanges(Request $request, $partNumber)
    {
        $results["product"] = DB::connection(self::$main_db)
            ->select("SELECT pm.partIntPartNumber,ib.BrandName as interchange_name 
                            FROM partInterchange_mapper pm
                            JOIN Interchangename_brandcode_mapper ib ON pm.partIntBrandID=ib.BrandID AND pm.partIntSubBrandID <=> ib.SubBrandID
                            JOIN partNumberInfo pni ON pm.part_id = pni.part_id
                            WHERE pni.partNumber='$partNumber'
                            GROUP BY pm.partIntPartNumber,ib.BrandName 
                            ORDER BY ib.BrandName");

        return json_encode($results);
    }


    // public function premiumcollection(Request $request,$partnumber)
    // {
    //   $productcount_premium=DB::connection(self::$aces_vcdb)
    //                             // ->select("SELECT y.YearID AS year, TRIM(ma.MakeName) AS make ,TRIM(mo.ModelName) AS model FROM $this->pg_db.applications a 
    //                             // JOIN year y ON a.YearID=y.YearID 
    //                             // JOIN make ma ON a.MakeID = ma.MakeID 
    //                             // JOIN model mo ON a.ModelID = mo.ModelID 
    //                             // WHERE a.partnumber='$partnumber' 
    //                             // ORDER BY y.YearID DESC limit 1");
    //                             ->select("SELECT a.YearID AS year, TRIM(ma.MakeName) AS make ,TRIM(mo.ModelName) AS model FROM $this->pg_db.applications a  
    //                             JOIN make ma ON a.MakeID = ma.MakeID 
    //                             JOIN model mo ON a.ModelID = mo.ModelID 
    //                             JOIN $this->pg_db.partNumberInfo pni ON a.part_id = pni.part_id
    //                             WHERE pni.partNumber='$partnumber' 
    //                             ORDER BY a.YearID DESC limit 1");

    //   return json_encode($productcount_premium);
    // }



    public function beahead(Request $request)
    {

        //   $beahead=DB::connection(self::$main_db)
        //             ->select("SELECT DISTINCT(be.partNumber),be.OE_partno,be.Description,be.available_date,be.application,be.filename,p.partType,pm.partTypename,p.partNumber AS pgpartnumber 
        //             FROM be_ahead be LEFT JOIN partNumberInfo p ON p.partNumber=be.partNumber 
        //             LEFT JOIN parttype_master pm ON pm.partTypeid=p.partType 
        //             ORDER BY beahead_order");
        $beahead = DB::connection(self::$main_db)
            ->select("SELECT DISTINCT(be.partNumber),be.OE_partno,be.Description,be.available_date,be.application,be.filename,p.PartTerminologyID as partType,pm.partTypename,p.partNumber AS pgpartnumber 
                FROM be_ahead be LEFT JOIN partNumberInfo p ON p.partNumber=be.partNumber 
                LEFT JOIN parttype_master pm ON pm.PartTerminologyID=p.PartTerminologyID 
                ORDER BY beahead_order");

        return json_encode($beahead);
    }

    public function beaheadymm(Request $request, $partnumber)
    {

        $productcount_beaheadymm = DB::connection(self::$aces_vcdb)
            // ->select("SELECT y.YearID AS year, TRIM(ma.MakeName) AS make ,TRIM(mo.ModelName) AS model 
            // FROM $this->pg_db.applications a JOIN year y ON a.YearID=y.YearID 
            // JOIN make ma ON a.MakeID = ma.MakeID 
            // JOIN model mo ON a.ModelID = mo.ModelID 
            // WHERE partnumber='$partnumber' 
            // ORDER BY y.YearID DESC LIMIT 1");

            ->select("SELECT a.YearID AS year, TRIM(ma.MakeName) AS make ,TRIM(mo.ModelName) AS model 
                                FROM $this->pg_db.applications a 
                                JOIN make ma ON a.MakeID = ma.MakeID 
                                JOIN model mo ON a.ModelID = mo.ModelID
                                JOIN $this->pg_db.partNumberInfo pni ON a.part_id = pni.part_id
                                WHERE pni.partNumber='$partnumber' 
                                ORDER BY a.YearID DESC LIMIT 1");

        return json_encode($productcount_beaheadymm);
    }


    // public function postContactForm(Request $request)
    // {

    //     $data = $request->json()->all();
    //     //Contact Email
    //     \Mail::send('contact_email',
    //          array(
    //              'name' => $data['full_name'],
    //              'email' => $data['email'],
    //              'company' => $data['company'],
    //              'phone_number' => $data['phone'],
    //              'user_message' => $data['message'],
    //              'optIn' => $data['emma_subscribe']
    //          ), function($message) use ($request)
    //           {
    //               $message->from('pgfilters@premiumguard.com');
    //               $message->subject('Online Form Submission from Mobile link of Pgfilters.com');
    //               $message->to('anan@premiumguard.com');
    //               $message->to('orders@pgfilters.com');
    //               $message->to('pgfilters@pgfilters.com');
    //           });

    //         // Emma subscription to Website list group

    //         // Authentication Variables
    //         if($data['emma_subscribe']=="true") {  
    //             $account_id = "1409233";
    //             $public_api_key = "a3706e94a506e0fac2b3";
    //             $private_api_key = "1b3a4d3000ed5965f539";

    //             $groups = array(9893073); //group id for product updates and news :9527505 and group id for testing: 10749137, Aishwarya Testing 2 :25439441, Website List:9893073
    //             // Member data other than email should be passed in an array called "fields"
    //             $member_data = array(
    //                 "email" => $data['email'],
    //                 "fields" => array(
    //                     'full-name'=>$data['full_name'],
    //                     'company'=>$data['company'],
    //                     'phone'=>$data['phone']
    //                 ),
    //                 "group_ids" => $groups
    //             );

    //             // Set URL
    //             $url = "https://api.e2ma.net/".$account_id."/members/add";

    //             // setup and execute the cURL command
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_USERPWD, $public_api_key . ":" . $private_api_key);
    //             curl_setopt($ch, CURLOPT_URL, $url);
    //             curl_setopt($ch, CURLOPT_POST, count($member_data));
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($member_data));
    //             curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //             curl_setopt($ch, CURLOPT_SSLVERSION, 6);
    //             $head = curl_exec($ch);
    //             $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //             curl_close($ch);
    //         }

    //     return ['success' => '1'];
    // }

    public function yearDrainPlugs(Request $request, $vehicletypeid)
    {
        return $data = DB::connection(self::$aces_vcdb)
            ->table('basevehicle')
            ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
            ->join('vehicletype', 'model.VehicleTypeID', '=', 'vehicletype.VehicleTypeID')
            ->select('basevehicle.YearID as year')->distinct()
            ->where('vehicletype.VehicleTypeGroupID', $vehicletypeid)
            ->where('basevehicle.YearID', '<', 2025)
            ->groupBy('basevehicle.YearID')
            ->orderBy('basevehicle.YearID', 'DESC')
            ->get();
    }

}
