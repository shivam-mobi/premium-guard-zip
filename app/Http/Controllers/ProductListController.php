<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;
use Config;

class ProductListController extends Controller
{

    public static $aces_vcdb = 'mysql_aces';
    public static $main_db = 'mysql';
    protected $pg_db;

    public function __construct() {
        $this->pg_db = Config::get('constants.pg_dbname');
    }
    
    public function productlist(Request $request,$baseVehicleId,$engineId,$qualifier_id,$qualifier_name) {

        $store_upcoming = [];
        $store_upcoming_name = [];
        $where_clause = [];
        $prevqualifier = [];

        $store_upcoming = array_unique(explode(",",$qualifier_id));
        $store_upcoming_name = array_unique(explode(",",urldecode($qualifier_name)));

        if($store_upcoming_name[0]=="noqualifier") {
            $store_upcoming_name = [];
        }

        $qualifierlist="";
        $qualifierspe="";

        if($store_upcoming[0]!="noqualifier") {

            if(count($store_upcoming)>1) {
                $qualifierspe.=",CONCAT_WS(',',";
                foreach ($store_upcoming as $value) {
                    $qual=explode(":",$value);
                    $where_clause[] = " and (a.".$qual[0]."=".$qual[1]." OR a.".$qual[0]." IS NULL)";
                    $prevqualifier[] = "GROUP_CONCAT(CONCAT('".$qual[0].":', IFNULL(a.".$qual[0].",'')))";
                }
                $qualifierspe.=implode(",",$prevqualifier);
                $qualifierspe.=") as f";
                $qualifierlist = implode($where_clause);
            }
            else {
                $qual=explode(":",$store_upcoming[0]);
                $qualifierlist = " and (a.".$qual[0]."=".$qual[1]." OR a.".$qual[0]." IS NULL)";
                $qualifierspe = ",CONCAT_WS(',',";
                $qualifierspe.="GROUP_CONCAT(CONCAT('".$qual[0].":', IFNULL(a.".$qual[0].",'')))";
                $qualifierspe.=") as f";
                $prevqualifier[]=$qual[0];
            }
        }

        $imageSubquery = "LEFT JOIN ( SELECT part_id, SUBSTRING_INDEX(GROUP_CONCAT(fileName ORDER BY img_id), ',', 1) as fileName FROM image_mapper GROUP BY part_id ) im ON p.part_id = im.part_id ";
        $partImageSelect = ", MAX(im.fileName) as part_image ";

        $partTypeArray = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT DISTINCT(p.partNumber),CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    // FROM partNumberInfo p LEFT JOIN applications a ON p.partNumber=a.partnumber 
                                    // JOIN parttype_master pm ON p.partType=pm.partTypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='1' 
                                    // GROUP BY p.partNumber ORDER BY FIELD(partTypeid,1,12,2,3,16,4,5) ASC,q DESC"));
                                    ->select("SELECT DISTINCT(p.partNumber),CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.PartTerminologyID as partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe $partImageSelect
                                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                    $imageSubquery
                                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID 
                                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='1' 
                                    GROUP BY p.partNumber ORDER BY pm.partTypeOrder ASC,q DESC");



        $results["partTypeArray"] = $this->tranformProductResult($partTypeArray);
        
        $CapsAndPlugsArray = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT p.partNumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    // FROM partNumberInfo p LEFT JOIN applications a ON p.partNumber=a.partnumber 
                                    // JOIN parttype_master pm ON p.partType=pm.partTypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='2' 
                                    // GROUP BY p.partNumber"));
                                    ->select("SELECT p.partNumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.PartTerminologyID as partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe $partImageSelect
                                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                    $imageSubquery
                                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID 
                                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='2' 
                                    GROUP BY p.partNumber");

        $results["CapsAndPlugsArray"]  = $this->tranformProductResult($CapsAndPlugsArray);

        $WiperBlades = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT DISTINCT a.partType,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,p.partNumber,pm.partTypename,a.partSubtype,a.BaseVehicleID,a.EngineBaseID,psm.partSubtypename $qualifierspe 
                                    // FROM applications a JOIN partNumberInfo p ON a.partnumber=p.partNumber 
                                    // JOIN parttype_master pm ON a.partType=pm.partTypeid 
                                    // JOIN partsubtype_master psm ON a.partSubtype=psm.partSubtypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist."  AND a.partSubtype IN(1,2,3,4) AND pm.partCategoryid='3' 
                                    // GROUP by a.partType,a.partSubtype
                                    // ORDER BY a.partSubtype,FIELD(pm.partTypename,'Rear Blade','Passenger Side','Driver Side')DESC"));
                                    ->select("SELECT DISTINCT p.PartTerminologyID as partType,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,p.partNumber,a.BaseVehicleID,a.EngineBaseID,ps.Position as partTypename $partImageSelect
                                    FROM applications a
                                    JOIN positions ps ON a.PositionID = ps.PositionID
                                    JOIN partNumberInfo p ON a.part_id=p.part_id 
                                    $imageSubquery
                                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID
                                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist."  AND pm.partCategoryid='3' 
                                    GROUP by p.partNumber,ps.PositionID
                                    ORDER BY FIELD(ps.PositionID,30,104,22,103) DESC");

        $results["WiperBlades"] = $this->tranformProductResult($WiperBlades);

        $fueltyeparray=DB::connection(self::$aces_vcdb)->select("SELECT FuelTypeName FROM `fueltype`");

        $store_fueltyename=[];

        foreach($fueltyeparray as $fueltyeparray1) {
            array_push($store_fueltyename,$fueltyeparray1->FuelTypeName);
        }

        $get_engine_name=[];

        for($i=0;$i<count($store_upcoming_name);$i++) {

            $trimmed_value_q=trim($store_upcoming_name[$i]);

            if(in_array($trimmed_value_q,$store_fueltyename)) {
                array_push($get_engine_name,$trimmed_value_q);
            }
        }

        if(count($get_engine_name)>0) {

            if(count($get_engine_name) > 1) {
                $query_parts = array();
                $string = 'a.engine LIKE ';
                foreach ($get_engine_name as $ge) {
                    $query_parts[] = "'%".$ge."%'";
                }
                $string.= implode(' OR a.engine LIKE ', $query_parts);
            }
            else {
                $string = "a.engine LIKE '%".$get_engine_name[0]."%'";
            }

            $fluidImageJoin = " LEFT JOIN partNumberInfo pn_fluid ON pn_fluid.partNumber = a.partnumber LEFT JOIN ( SELECT part_id, SUBSTRING_INDEX(GROUP_CONCAT(fileName ORDER BY img_id), ',', 1) as fileName FROM image_mapper GROUP BY part_id ) im_fluid ON pn_fluid.part_id = im_fluid.part_id ";
            $results["Fluids"] = DB::connection(self::$main_db)
                    ->select("SELECT DISTINCT a.partnumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,fm.partTypename,a.partTypeid,a.BaseVehicleID,a.EngineBaseID $qualifierspe , MAX(im_fluid.fileName) as part_image 
                    FROM `applications3` a JOIN `fluids_parttype_master` fm ON a.partTypeid=fm.partTypeid 
                    $fluidImageJoin
                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND ($string) 
                    GROUP BY a.partnumber,fm.partTypename 
                    ORDER BY fm.partTypename");
        }
        else {
            $fluidImageJoin = " LEFT JOIN partNumberInfo pn_fluid ON pn_fluid.partNumber = a.partnumber LEFT JOIN ( SELECT part_id, SUBSTRING_INDEX(GROUP_CONCAT(fileName ORDER BY img_id), ',', 1) as fileName FROM image_mapper GROUP BY part_id ) im_fluid ON pn_fluid.part_id = im_fluid.part_id ";
            $results["Fluids"] = DB::connection(self::$main_db)
            ->select("SELECT DISTINCT a.partnumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,fm.partTypename,a.partTypeid,a.BaseVehicleID,a.EngineBaseID $qualifierspe , MAX(im_fluid.fileName) as part_image 
            FROM `applications3` a JOIN `fluids_parttype_master` fm ON a.partTypeid=fm.partTypeid 
            $fluidImageJoin
            WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." 
            GROUP BY a.partnumber,fm.partTypename 
            ORDER BY fm.partTypename");
        }


        return json_encode($results);
    }



    public function tranformProductResult($resultarray) {

        foreach($resultarray as $ra) {

            $partCategory = $ra->partTypename;
            $change_case = strtolower(str_replace(' ', '_', $partCategory));
            $ra->partTypeCategory = $change_case;
        }

        return $resultarray;
 
    }

    //when need more info clicked
    public function productlist1($baseVehicleId,$engineId,$qualifier_id,$qualifier_name) {

        $store_upcoming = [];
        $store_upcoming_name = [];
        $where_clause = [];
        $prevqualifier = [];

        $store_upcoming = array_unique(explode(",",$qualifier_id));
        $store_upcoming_name = array_unique(explode(",",urldecode($qualifier_name)));

        if($store_upcoming_name[0]=="noqualifier") {
            $store_upcoming_name = [];
        }

        $qualifierlist="";
        $qualifierspe="";
        if($store_upcoming[0]!="noqualifier") {
            $qualifierspe.=",CONCAT_WS(',',";
            foreach ($store_upcoming as $value) {
                $qual=explode(":",$value);
                $where_clause[]=" and (a.".$qual[0]."=".$qual[1]." OR a.".$qual[0]." IS NULL)";
                $prevqualifier[]="GROUP_CONCAT(CONCAT('".$qual[0].":', IFNULL(a.".$qual[0].",'')))";
            }
            $qualifierspe.=implode(",",$prevqualifier);
            $qualifierspe.=") as f";
            $qualifierlist=implode($where_clause);

        }
        else {
            $qual=explode(":",$store_upcoming[0]);
            $qualifierlist=" and (a.".$qual[0]."=".$qual[1]." OR a.".$qual[0]." IS NULL)";
            $qualifierspe=",CONCAT_WS(',',";
            $qualifierspe.="GROUP_CONCAT(CONCAT('".$qual[0].":', IFNULL(a.".$qual[0].",'')))";
            $qualifierspe.=") as f";
            $prevqualifier[]=$qual[0];
        }

        $partTypeArray = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT DISTINCT(p.partNumber),CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    // FROM partNumberInfo p LEFT JOIN applications a ON p.partNumber=a.partnumber 
                                    // JOIN parttype_master pm on p.partType=pm.partTypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='1' 
                                    // GROUP BY p.partNumber ORDER BY FIELD(partTypeid,1,12,2,3,16,4,5) ASC,f DESC"));
                                    ->select("SELECT DISTINCT(p.partNumber),CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.PartTerminologyID as partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                    JOIN parttype_master pm on p.PartTerminologyID=pm.PartTerminologyID 
                                    WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='1' 
                                    GROUP BY p.partNumber ORDER BY pm.partTypeOrder ASC,f DESC");

        $results["partTypeArray"] = $this->tranformProductResult($partTypeArray);

        $CapsAndPlugsArray = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT p.partNumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    // FROM partNumberInfo p LEFT JOIN applications a ON p.partNumber=a.partnumber 
                                    // JOIN parttype_master pm ON p.partType=pm.partTypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='2' 
                                    // GROUP BY p.partNumber"));
                                    ->select("SELECT p.partNumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,p.PartTerminologyID as partType,pm.partTypename,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID 
                                    WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist." AND pm.partCategoryid='2' 
                                    GROUP BY p.partNumber"); 


        $results["CapsAndPlugsArray"]  = $this->tranformProductResult($CapsAndPlugsArray);

        $WiperBlades = DB::connection(self::$main_db)
                                    // ->select(DB::raw("SELECT DISTINCT a.partType,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,p.partNumber,pm.partTypename,a.partSubtype,a.BaseVehicleID,a.EngineBaseID,psm.partSubtypename $qualifierspe 
                                    // FROM applications a JOIN partNumberInfo p ON a.partnumber=p.partNumber 
                                    // JOIN parttype_master pm ON a.partType=pm.partTypeid 
                                    // JOIN partsubtype_master psm ON a.partSubtype=psm.partSubtypeid 
                                    // WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist."  AND a.partSubtype IN(1,2,3,4) and pm.partCategoryid='3'
                                    // GROUP by a.partType,a.partSubtype
                                    // ORDER BY a.partSubtype,FIELD(pm.partTypename,'Rear Blade','Passenger Side','Driver Side')DESC"));
                                    ->select("SELECT DISTINCT p.PartTerminologyID as partType,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,p.partNumber,a.BaseVehicleID,a.EngineBaseID, ps.Position as partTypename   $qualifierspe
                                    FROM applications a 
                                    JOIN positions ps ON a.PositionID = ps.PositionID
                                    JOIN partNumberInfo p ON a.part_id=p.part_id 
                                    JOIN parttype_master pm ON p.PartTerminologyID=pm.PartTerminologyID
                                    WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist."  AND pm.partCategoryid='3'
                                    GROUP by p.partNumber,ps.PositionID
                                    ORDER BY FIELD(ps.PositionID,30,104,22,103) DESC");


        $results["WiperBlades"] = $this->tranformProductResult($WiperBlades);

        $fueltyeparray=DB::connection(self::$aces_vcdb)->select("SELECT FuelTypeName FROM `fueltype`");

        $store_fueltyename=[];

        foreach($fueltyeparray as $fueltyeparray1) {
            array_push($store_fueltyename,$fueltyeparray1->FuelTypeName);
        }

        $get_engine_name=[];


        for($i=0;$i<count($store_upcoming_name);$i++) {
            $trimmed_value_q=trim($store_upcoming_name[$i]);

            if(in_array($trimmed_value_q,$store_fueltyename)) {
                array_push($get_engine_name,$trimmed_value_q);
            }
        }

        if(count($get_engine_name)>0) {

            if(count($get_engine_name) > 1) {
                $query_parts = array();
                $string = 'a.engine LIKE ';
                foreach ($get_engine_name as $ge) {
                    $query_parts[] = "'%".$ge."%'";
                }
                $string.= implode(' OR a.engine LIKE ', $query_parts);
            }
            else {
                $string = "a.engine LIKE '%".$get_engine_name[0]."%'";
            }


            $results["Fluids"] = DB::connection(self::$main_db)
                ->select("SELECT DISTINCT a.partnumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,fm.partTypename,a.partTypeid,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                FROM `applications3` a JOIN `fluids_parttype_master` fm ON a.partTypeid=fm.partTypeid 
                WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND ($string) 
                GROUP BY a.partnumber,fm.partTypename 
                ORDER BY fm.partTypename");
        }
        else {
            $results["Fluids"] = DB::connection(self::$main_db)
                ->select("SELECT DISTINCT a.partnumber,CONCAT_WS(',',GROUP_CONCAT(CONCAT('FuelTypeID:', IFNULL(a.FuelTypeID,''))),GROUP_CONCAT(CONCAT('AspirationID:', IFNULL(a.AspirationID,''))),GROUP_CONCAT(CONCAT('DriveTypeID:', IFNULL(a.DriveTypeID,''))),GROUP_CONCAT(CONCAT('EngineVINID:', IFNULL(a.EngineVINID,''))),GROUP_CONCAT(CONCAT('EngineDesignationID:', IFNULL(a.EngineDesignationID,''))),GROUP_CONCAT(CONCAT('CylinderHeadTypeID:', IFNULL(a.CylinderHeadTypeID,''))),GROUP_CONCAT(CONCAT('SubModelID:', IFNULL(a.SubModelID,''))),GROUP_CONCAT(CONCAT('TransmissionControlTypeID:', IFNULL(a.TransmissionControlTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionNumSpeedsID:', IFNULL(a.TransmissionNumSpeedsID,''))),GROUP_CONCAT(CONCAT('ValvesPerEngineID:', IFNULL(a.ValvesPerEngineID,''))),GROUP_CONCAT(CONCAT('FuelDeliveryTypeID:', IFNULL(a.FuelDeliveryTypeID,''))),GROUP_CONCAT(CONCAT('BedTypeID:', IFNULL(a.BedTypeID,''))),GROUP_CONCAT(CONCAT('BodyNumDoorsID:', IFNULL(a.BodyNumDoorsID,''))),GROUP_CONCAT(CONCAT('EngineMfrID:', IFNULL(a.EngineMfrID,''))),GROUP_CONCAT(CONCAT('FuelDeliverySubTypeID:', IFNULL(a.FuelDeliverySubTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemControlTypeID:', IFNULL(a.FuelSystemControlTypeID,''))),GROUP_CONCAT(CONCAT('FuelSystemDesignID:', IFNULL(a.FuelSystemDesignID,''))),GROUP_CONCAT(CONCAT('SteeringTypeID:', IFNULL(a.SteeringTypeID,''))),GROUP_CONCAT(CONCAT('TransmissionTypeID:', IFNULL(a.TransmissionTypeID,''))),GROUP_CONCAT(CONCAT('BedLengthID:', IFNULL(a.BedLengthID,''))),GROUP_CONCAT(CONCAT('BodyTypeID:', IFNULL(a.BodyTypeID,''))),GROUP_CONCAT(CONCAT('BrakeABSID:', IFNULL(a.BrakeABSID,''))),GROUP_CONCAT(CONCAT('BrakeSystemID:', IFNULL(a.BrakeSystemID,''))),GROUP_CONCAT(CONCAT('EngineVersionID:', IFNULL(a.EngineVersionID,''))),GROUP_CONCAT(CONCAT('FrontBrakeTypeID:', IFNULL(a.FrontBrakeTypeID,''))),GROUP_CONCAT(CONCAT('FrontSpringTypeID:', IFNULL(a.FrontSpringTypeID,''))),GROUP_CONCAT(CONCAT('IgnitionSystemTypeID:', IFNULL(a.IgnitionSystemTypeID,''))),GROUP_CONCAT(CONCAT('MfrBodyCodeID:', IFNULL(a.MfrBodyCodeID,''))),GROUP_CONCAT(CONCAT('PowerOutputID:', IFNULL(a.PowerOutputID,''))),GROUP_CONCAT(CONCAT('RearBrakeTypeID:', IFNULL(a.RearBrakeTypeID,''))),GROUP_CONCAT(CONCAT('RearSpringTypeID:', IFNULL(a.RearSpringTypeID,''))),GROUP_CONCAT(CONCAT('RegionID:', IFNULL(a.RegionID,''))),GROUP_CONCAT(CONCAT('SteeringSystemID:', IFNULL(a.SteeringSystemID,''))),GROUP_CONCAT(CONCAT('TransElecControlledID:', IFNULL(a.TransElecControlledID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrID:', IFNULL(a.TransmissionMfrID,''))),GROUP_CONCAT(CONCAT('TransmissionMfrCodeID:', IFNULL(a.TransmissionMfrCodeID,''))),GROUP_CONCAT(CONCAT('WheelBaseID:', IFNULL(a.WheelBaseID,'')))) as q,GROUP_CONCAT(DISTINCT a.note SEPARATOR '; ') as note_merged,a.note,fm.partTypename,a.partTypeid,a.BaseVehicleID,a.EngineBaseID $qualifierspe 
                FROM `applications3` a JOIN `fluids_parttype_master` fm ON a.partTypeid=fm.partTypeid 
                WHERE a.BaseVehicleID=$baseVehicleId and a.EngineBaseID IN($engineId)".$qualifierlist."
                GROUP BY a.partnumber,fm.partTypename 
                ORDER BY fm.partTypename");
        }

        return json_encode($results);
       
    }



    
    
}
