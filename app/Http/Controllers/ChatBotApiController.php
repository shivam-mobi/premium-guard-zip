<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
use Illuminate\Support\Facades\Log;

class ChatBotApiController extends Controller
{
    public static $aces_vcdb = 'mysql_aces'; 
    public static $main_db = 'mysql';

  
    public function __construct() {
        $this->pg_db = Config::get('constants.pg_dbname');
    }

    /**
     * Display a listing of Year.
     *
     * @return \Illuminate\Http\Response
    */


    public function dynamicWhatsappChatbot(Request $request,$searchterm) {

        //searchterm seperated by space
        if($searchterm!="") {

            $i=0;
            $products = [];

            //Applications
            $appl_string = str_replace(" ", "%' and dsf.YMME_Concatenated LIKE '%", $searchterm);
            // $pro=DB::connection(self::$aces_vcdb)->select("SELECT STRAIGHT_JOIN ids.BaseVehicleID,eb.EngineBaseID,CAST(bv.YearID AS char) as year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) as model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
            //         FROM pgi_web_dynamic_search_flat dsf JOIN pgi_web_dynamic_search_flat_ids ids ON dsf.YMME_ID=ids.YMME_Target_ID 
            //         JOIN basevehicle bv ON ids.BaseVehicleID=bv.BaseVehicleID 
            //         JOIN make ma ON bv.MakeID = ma.MakeID 
            //         JOIN model mo ON bv.ModelID = mo.ModelID 
            //         JOIN engineconfig ec ON ids.EngineConfigID = ec.EngineConfigID 
            //         JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
            //         LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
            //         WHERE dsf.YMME_Concatenated LIKE '%$appl_string%' 
            //         GROUP BY year,make,model,engine");
            $pro=DB::connection(self::$aces_vcdb)->select("SELECT STRAIGHT_JOIN dsf.BaseVehicleID,eb.EngineBaseID,CAST(bv.YearID AS char) as year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) as model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
                    FROM $this->pg_db.pgi_web_dynamic_search_flat dsf
                    JOIN basevehicle bv ON dsf.BaseVehicleID=bv.BaseVehicleID 
                    JOIN make ma ON bv.MakeID = ma.MakeID 
                    JOIN model mo ON bv.ModelID = mo.ModelID 
                    JOIN engineconfig ec ON dsf.EngineConfigID = ec.EngineConfigID 
                    JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
                    LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
                    WHERE dsf.YMME_Concatenated LIKE '%$appl_string%' 
                    GROUP BY year,make,model,engine");

            if (!empty($pro)) {
                foreach ($pro as $key => $value) {

                        $products[$i]["BaseVehicleID"] = $value->BaseVehicleID;
                        $products[$i]["EngineBaseID"] = $value->EngineBaseID;
                        $products[$i]["Label"] = $value->year." ".$value->make." ".$value->model." ".$value->engine;
                        $i++;
                }
            }

            return json_encode($products);
            

        }
        else {
        //    Log::info('No searchterm found in chatbot api dynamic');
        }

    }

    public function productsWhatsappChatbot(Request $request,$bvid,$enginebaseid) {

        if($bvid!="" && $enginebaseid!="") {

            // $results["partTypeArray"] = DB::connection(self::$main_db)->select("SELECT DISTINCT(p.partNumber),pm.partTypename,a.note 
            //         FROM $this->pg_db.partNumberInfo p JOIN $this->pg_db.applications a ON p.partNumber=a.partnumber 
            //         JOIN $this->pg_db.parttype_master pm ON p.partType=pm.partTypeid 
            //         WHERE a.BaseVehicleID=$bvid AND a.EngineBaseID IN($enginebaseid) AND pm.partCategoryid='1' 
            //         GROUP BY p.partNumber ORDER BY FIELD(partTypeid,1,12,2,3,16,4,5) ASC");
            $results["partTypeArray"] = DB::connection(self::$main_db)->select("SELECT DISTINCT(p.partNumber),pm.partTypename,a.note 
            FROM $this->pg_db.partNumberInfo p JOIN $this->pg_db.applications a ON p.part_id=a.part_id 
            JOIN $this->pg_db.parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID 
            WHERE a.BaseVehicleID=$bvid AND a.EngineBaseID IN($enginebaseid) AND pm.partCategoryid='1' 
            GROUP BY p.partNumber ORDER BY pm.partTypeOrder ASC");

            // $results["CapsAndPlugsArray"] = DB::connection(self::$main_db)->select("SELECT p.partNumber,pm.partTypename,a.note
            //         FROM partNumberInfo p JOIN applications a ON p.partNumber=a.partnumber 
            //         JOIN parttype_master pm ON p.partType=pm.partTypeid 
            //         WHERE a.BaseVehicleID=$bvid AND a.EngineBaseID IN($enginebaseid) AND pm.partCategoryid='2' 
            //         GROUP by p.partNumber");
            $results["CapsAndPlugsArray"] = DB::connection(self::$main_db)->select("SELECT p.partNumber,pm.partTypename,a.note
                    FROM partNumberInfo p JOIN applications a ON p.part_id=a.part_id 
                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID 
                    WHERE a.BaseVehicleID=$bvid AND a.EngineBaseID IN($enginebaseid) AND pm.partCategoryid='2' 
                    GROUP by p.partNumber");

            // $results["WiperBlades"] = DB::connection(self::$main_db)->select("SELECT DISTINCT p.partNumber,pm.partTypename,psm.partSubtypename,a.note 
            //         FROM applications a JOIN partNumberInfo p ON a.partnumber=p.partNumber 
            //         JOIN parttype_master pm ON a.partType=pm.partTypeid 
            //         JOIN partsubtype_master psm ON a.partSubtype=psm.partSubtypeid 
            //         WHERE a.BaseVehicleID=$bvid and a.EngineBaseID IN($enginebaseid) AND a.partSubtype IN(1,2,3,4) AND pm.partCategoryid='3' 
            //         GROUP BY a.partType,a.partSubtype 
            //         ORDER BY a.partSubtype,FIELD(pm.partTypename,'Rear Blade','Passenger Side','Driver Side')DESC");
            // $results["WiperBlades"] = DB::connection(self::$main_db)->select("SELECT DISTINCT p.partNumber,pm.partTypename,psm.partSubtypename,a.note 
            //         FROM applications a
            //         JOIN partNumberInfo p ON a.part_id=p.part_id 
            //         JOIN parttype_master pm ON a.partType=pm.PartTerminologyID 
            //         JOIN partsubtype_master psm ON a.partSubtype=psm.partSubtypeid 
            //         WHERE a.BaseVehicleID=$bvid and a.EngineBaseID IN($enginebaseid) AND a.partSubtype IN(1,2,3,4) AND pm.partCategoryid='3' 
            //         GROUP BY a.partType,a.partSubtype 
            //         ORDER BY a.partSubtype,FIELD(pm.partTypename,'Rear Blade','Passenger Side','Driver Side')DESC");
            $results["WiperBlades"] = DB::connection(self::$main_db)
            ->select("SELECT p.partNumber,
                ps.Position as partTypename,
                GROUP_CONCAT(DISTINCT a.note) AS notes
                FROM applications a
                JOIN positions ps ON a.PositionID = ps.PositionID
                JOIN partNumberInfo p ON a.part_id = p.part_id
                JOIN parttype_master pm ON p.PartTerminologyID = pm.PartTerminologyID
                WHERE a.BaseVehicleID = $bvid
                AND a.EngineBaseID IN ($enginebaseid)
                AND pm.partCategoryid = '3'
                GROUP BY p.partNumber
                ORDER BY FIELD(ps.PositionID,30,104,22,103) DESC");

            $results["Fluids"] = DB::connection(self::$main_db)->select("SELECT DISTINCT a.partnumber,a.note,fm.partTypename 
                    FROM `applications3` a JOIN `fluids_parttype_master` fm ON a.partTypeid=fm.partTypeid 
                    WHERE a.BaseVehicleID=$bvid AND a.EngineBaseID IN($enginebaseid) AND a.partnumber!='' 
                    GROUP BY a.partnumber,fm.partTypename ORDER BY fm.partTypename");


            return json_encode($results);

        }
        else {
            // Log::info('bvid OR enginebaseid is empty');
        }

    }

    public function yearWhatsappChatbot(Request $request) {

        return $results = DB::connection(self::$aces_vcdb)->table('basevehicle')
            ->select('basevehicle.YearID as year')->distinct()
            ->groupBy('basevehicle.YearID')
            ->orderBy('basevehicle.YearID', 'DESC')
            ->get();

    }

    public function makeWhatsappChatbot(Request $request,$year) {

        return $results = DB::connection(self::$aces_vcdb)->table('basevehicle')
            ->select('make.MakeID as MakeID','make.MakeName as make')->distinct()
            ->join('make', 'basevehicle.MakeID', '=', 'make.MakeID')
            ->where('basevehicle.YearID',$year)
            ->groupBy('make.MakeName')
            ->orderBy('make.MakeName', 'ASC')
            ->get();
        
    }

    public function modelWhatsappChatbot(Request $request,$year,$makeID) {

        return $results = DB::connection(self::$aces_vcdb)->table('basevehicle')
            ->select('model.ModelName as model','basevehicle.BaseVehicleID')->distinct()
            ->join('model', 'basevehicle.ModelID', '=', 'model.ModelID')
            ->where('basevehicle.YearID',$year)
            ->where('basevehicle.MakeID',$makeID)
            ->groupBy('model.ModelName')
            ->orderBy('model.ModelName', 'ASC')
            ->get();
        
    }


    public function engineWhatsappChatbot(Request $request,$bvid) {

        return $results = DB::connection(self::$aces_vcdb)
                ->select("SELECT CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine,GROUP_CONCAT(DISTINCT eb.EngineBaseID) as EngineBaseID 
                    FROM vehicle v JOIN vehicletoengineconfig vtec ON v.VehicleID = vtec.VehicleID AND v.BaseVehicleID='$bvid' 
                    JOIN engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID 
                    JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
                    LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft on ec.FuelTypeID = ft.FuelTypeID 
                    GROUP by engine");
    } 

 
}
