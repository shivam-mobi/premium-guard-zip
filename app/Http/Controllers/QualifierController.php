<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Config;
use Log;


class QualifierController extends Controller
{


    public static $aces_vcdb = 'mysql_aces'; 
    public static $main_db = 'mysql';

    protected $pg_db;

    public function __construct() {
        $this->pg_db = Config::get('constants.pg_dbname');
    }



    function areArraysIdentical($arrays) {
                    
        $sortedArrays = $arrays; // Make a copy to keep the original arrays intact
        array_walk($sortedArrays, 'sort');

   
        $firstSortedArray = $sortedArrays[0];

        $same_value = 1;
        foreach ($sortedArrays as $sortedArray) {

            //if partnumbers from first array are not matching with other 2 arrays -- Break the loop
            if ($sortedArray !== $firstSortedArray) {
                $same_value = 0;
                break;
            }
            else {
                continue;
            }
        }

        return $same_value;
    }


    public function indexBK(Request $request,$baseVehicleId,$engineId,$request_qualifierSelected,$parttype,$partsubtype) {

        // Filtering request to get unique values
        $qualifierSelected = implode(',', array_unique(explode(",", $request_qualifierSelected)));

        $qualifier_main_table=[
            'AspirationID' => 'aspiration.AspirationID',
            'BedLengthID' => 'bedlength.BedLengthID',
            'BedTypeID' => 'bedtype.BedTypeID',
            'BodyNumDoorsID' => 'bodynumdoors.BodyNumDoorsID',
            'BodyTypeID' => 'bodytype.BodyTypeID',
            'BrakeABSID' => 'brakeabs.BrakeABSID',
            'BrakeSystemID' => 'brakesystem.BrakeSystemID',
            'CylinderHeadTypeID' => 'cylinderheadtype.CylinderHeadTypeID',
            'DriveTypeID' => 'drivetype.DriveTypeID',
            'EngineDesignationID' => 'enginedesignation.EngineDesignationID',
            'EngineMfrID' => 'mfr.MfrID',
            'EngineVersionID' => 'engineversion.EngineVersionID',
            'EngineVINID' => 'enginevin.EngineVINID',
            'FrontBrakeTypeID' => 'braketype_front.BrakeTypeID',
            'FrontSpringTypeID' => 'springtype_front.SpringTypeID',
            'FuelDeliverySubTypeID' => 'fueldeliverysubtype.FuelDeliverySubTypeID',
            'FuelDeliveryTypeID' => 'fueldeliverytype.FuelDeliveryTypeID',
            'FuelSystemControlTypeID' => 'fuelsystemcontroltype.FuelSystemControlTypeID',
            'FuelSystemDesignID' => 'fuelsystemdesign.FuelSystemDesignID',
            'FuelTypeID' => 'fueltype.FuelTypeID',
            'IgnitionSystemTypeID' => 'ignitionsystemtype.IgnitionSystemTypeID',
            'MfrBodyCodeID' => 'mfrbodycode.MfrBodyCodeID',
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => 'poweroutput.PowerOutputID',
            'RearBrakeTypeID' => 'braketype_rear.BrakeTypeID',
            'RearSpringTypeID' => 'springtype_rear.SpringTypeID',
            'RegionID' => 'region.RegionID',
            'SteeringSystemID' => 'steeringsystem.SteeringSystemID',
            'SteeringTypeID' => 'steeringtype.SteeringTypeID',
            'SubModelID' => 'submodel.SubModelID',
            'TransElecControlledID' => 'eleccontrolled.ElecControlledID',
            'TransmissionControlTypeID' => 'transmissioncontroltype.TransmissionControlTypeID',
            'TransmissionMfrCodeID' => 'transmissionmfrcode.TransmissionMfrCodeID',
            'TransmissionMfrID' => 'mfr1.MfrID',
            'TransmissionNumSpeedsID' => 'transmissionnumspeeds.TransmissionNumSpeedsID',
            'TransmissionTypeID' => 'transmissiontype.TransmissionTypeID',
            'ValvesPerEngineID' => 'valves.ValvesID',
            'WheelBaseID' => 'wheelbase.WheelBaseID'
        ];

        
        $store_upcoming=[];
        
        $explode_part=explode(",",$parttype);
        $store_upcoming=array_unique(explode(",",$qualifierSelected));
        
        $qualifierlist = '';
        $firstQualifier = reset($store_upcoming);
        if ($firstQualifier && $firstQualifier !== 'noqualifier') {
            $where_clause = [];
                foreach ($store_upcoming as $value) {
                $qual = explode(':', $value);
                if (isset($qualifier_main_table[$qual[0]])) {
                    $where_clause[] = ' AND ' . $qualifier_main_table[$qual[0]] . '=' . (int) ($qual[1] ?? 0);
                }
            }
            $qualifierlist = implode(' ', $where_clause);
        }

        $fueltype_table = 'fueltype';


        $fluids_from_query = DB::connection(self::$main_db)->table('fluids_parttype_master')->pluck('partTypeid')->toArray();

        $save_fluid_pt = [];
        $save_normal_pt = [];
        foreach ($explode_part as $partId) {
            if (in_array($partId, $fluids_from_query)) {
                $save_fluid_pt[] = $partId;
            } else {
                $save_normal_pt[] = $partId;
            }
        }
        

        //We use left joins in case the VCDB isn't fully populated

        $vcdb_table_joins = [
            'basevehicle' => 'LEFT JOIN basevehicle ON vehicle.BaseVehicleID = basevehicle.BaseVehicleID',
            'region' => 'LEFT JOIN region ON vehicle.RegionID = region.RegionID',
            'submodel' => 'LEFT JOIN submodel ON vehicle.SubmodelID = submodel.SubModelID',
            'vehicletospringtypeconfig' => 'LEFT JOIN vehicletospringtypeconfig ON vehicle.VehicleID = vehicletospringtypeconfig.VehicleID',
            'springtype_front' => 'LEFT JOIN springtypeconfig ON vehicletospringtypeconfig.SpringTypeConfigID = springtypeconfig.SpringTypeConfigID',
            'springtype' => 'LEFT JOIN springtype springtype_front ON springtypeconfig.FrontSpringTypeID = springtype_front.SpringTypeID',
            'springtype_rear' => 'LEFT JOIN springtype springtype_rear ON springtypeconfig.RearSpringTypeID = springtype_rear.SpringTypeID',
            'vehicletosteeringconfig' => 'LEFT JOIN vehicletosteeringconfig ON vehicle.VehicleID = vehicletosteeringconfig.VehicleID',
            'steeringconfig' => 'LEFT JOIN steeringconfig ON vehicletosteeringconfig.SteeringConfigID = steeringconfig.SteeringConfigID',
            'steeringtype' => 'LEFT JOIN steeringtype ON steeringconfig.SteeringTypeID = steeringtype.SteeringTypeID',
            'steeringsystem' => 'LEFT JOIN steeringsystem ON steeringconfig.SteeringSystemID = steeringsystem.SteeringSystemID',
            'vehicletobedconfig' => 'LEFT JOIN vehicletobedconfig ON vehicle.VehicleID = vehicletobedconfig.VehicleID',
            'bedconfig' => 'LEFT JOIN bedconfig ON vehicletobedconfig.BedConfigID = bedconfig.BedConfigID',
            'bedtype' => 'LEFT JOIN bedtype ON bedconfig.BedTypeID = bedtype.BedTypeID',
            'bedlength' => 'LEFT JOIN bedlength ON bedconfig.BedLengthID = bedlength.BedLengthID',
            'vehicletobrakeconfig' => 'LEFT JOIN vehicletobrakeconfig ON vehicle.VehicleID = vehicletobrakeconfig.VehicleID',
            'braketype_front' => 'LEFT JOIN brakeconfig ON vehicletobrakeconfig.BrakeConfigID = brakeconfig.BrakeConfigID',
            'braketype' => 'LEFT JOIN braketype braketype_front ON brakeconfig.FrontBrakeTypeID = braketype_front.BrakeTypeID',
            'braketype_rear' => 'LEFT JOIN braketype braketype_rear ON brakeconfig.RearBrakeTypeID = braketype_rear.BrakeTypeID',
            'brakeabs' => 'LEFT JOIN brakeabs ON brakeconfig.BrakeABSID = brakeabs.BrakeABSID',
            'brakesystem' => 'LEFT JOIN brakesystem ON brakeconfig.BrakeSystemID = brakesystem.BrakeSystemID',
            'vehicletodrivetype' => 'LEFT JOIN vehicletodrivetype ON vehicle.VehicleID = vehicletodrivetype.VehicleID',
            'drivetype' => 'LEFT JOIN drivetype ON vehicletodrivetype.DriveTypeID = drivetype.DriveTypeID',
            'vehicletowheelbase' => 'LEFT JOIN vehicletowheelbase ON vehicle.VehicleID = vehicletowheelbase.VehicleID',
            'wheelbase' => 'LEFT JOIN wheelbase ON vehicletowheelbase.WheelbaseID = wheelbase.WheelBaseID',
            'vehicletobodystyleconfig' => 'LEFT JOIN vehicletobodystyleconfig ON vehicle.VehicleID = vehicletobodystyleconfig.VehicleID',
            'bodystyleconfig' => 'LEFT JOIN bodystyleconfig ON vehicletobodystyleconfig.BodyStyleConfigID = bodystyleconfig.BodyStyleConfigID',
            'bodynumdoors' => 'LEFT JOIN bodynumdoors ON bodystyleconfig.BodyNumDoorsID = bodynumdoors.BodyNumDoorsID',
            'bodytype' => 'LEFT JOIN bodytype ON bodystyleconfig.BodyTypeID = bodytype.BodyTypeID',
            'vehicletomfrbodycode' => 'LEFT JOIN vehicletomfrbodycode ON vehicle.VehicleID = vehicletomfrbodycode.VehicleID',
            'mfrbodycode' => 'LEFT JOIN mfrbodycode ON vehicletomfrbodycode.MfrBodyCodeID = mfrbodycode.MfrBodyCodeID',
            'vehicletotransmission' => 'LEFT JOIN vehicletotransmission ON vehicle.VehicleID = vehicletotransmission.VehicleID',
            'transmission' => 'LEFT JOIN transmission ON vehicletotransmission.TransmissionID = transmission.TransmissionID',
            'eleccontrolled' => 'LEFT JOIN eleccontrolled ON transmission.TransmissionElecControlledID = eleccontrolled.ElecControlledID',
            'transmissionbase' => 'LEFT JOIN transmissionbase ON transmission.TransmissionBaseID = transmissionbase.TransmissionBaseID',
            'transmissiontype' => 'LEFT JOIN transmissiontype ON transmissionbase.TransmissionTypeID = transmissiontype.TransmissionTypeID',
            'transmissioncontroltype' => 'LEFT JOIN transmissioncontroltype ON transmissionbase.TransmissionControlTypeID = transmissioncontroltype.TransmissionControlTypeID',
            'transmissionnumspeeds' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr_transmission' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr' => 'LEFT JOIN mfr mfr1 ON transmission.TransmissionMfrID = mfr1.MfrID',
            'transmissionmfrcode' => 'LEFT JOIN transmissionmfrcode ON transmission.TransmissionMfrCodeID = transmissionmfrcode.TransmissionMfrCodeID',
            'vehicletoengineconfig' => 'LEFT JOIN vehicletoengineconfig ON vehicle.VehicleID = vehicletoengineconfig.VehicleID',
            'engineconfig' => 'LEFT JOIN engineconfig ON vehicletoengineconfig.EngineConfigID = engineconfig.EngineConfigID',
            'ignitionsystemtype' => 'LEFT JOIN ignitionsystemtype ON engineconfig.IgnitionSystemTypeID = ignitionsystemtype.IgnitionSystemTypeID',
            'cylinderheadtype' => 'LEFT JOIN cylinderheadtype ON engineconfig.CylinderHeadTypeID = cylinderheadtype.CylinderHeadTypeID',
            'aspiration' => 'LEFT JOIN aspiration ON engineconfig.AspirationID = aspiration.AspirationID',
            'fueltype' => "LEFT JOIN $fueltype_table ON engineconfig.FuelTypeID = fueltype.FuelTypeID",
            'valves' => 'LEFT JOIN valves ON engineconfig.ValvesID = valves.ValvesID',
            'enginevin' => 'LEFT JOIN enginevin ON engineconfig.EngineVINID = enginevin.EngineVINID',
            'mfr_engine' => 'LEFT JOIN engineversion ON engineconfig.EngineVersionID = engineversion.EngineVersionID',
            'engine_mfr' => 'LEFT JOIN mfr  ON engineconfig.EngineMfrID = mfr.MfrID',
            'fueldeliveryconfig' => 'LEFT JOIN fueldeliveryconfig ON engineconfig.FuelDeliveryConfigID = fueldeliveryconfig.FuelDeliveryConfigID',
            'fueldeliverytype' => 'LEFT JOIN fueldeliverytype ON fueldeliveryconfig.FuelDeliveryTypeID = fueldeliverytype.FuelDeliveryTypeID',
            'fuelsystemcontroltype' => 'LEFT JOIN fuelsystemcontroltype ON fueldeliveryconfig.FuelSystemControlTypeID = fuelsystemcontroltype.FuelSystemControlTypeID',
            'fueldeliverysubtype' => 'LEFT JOIN fueldeliverysubtype ON fueldeliveryconfig.FuelDeliverySubTypeID = fueldeliverysubtype.FuelDeliverySubTypeID',
            'fuelsystemdesign' => 'LEFT JOIN fuelsystemdesign ON fueldeliveryconfig.FuelSystemDesignID = fuelsystemdesign.FuelSystemDesignID',
            'enginedesignation' => 'LEFT JOIN enginedesignation ON engineconfig.EngineDesignationID = enginedesignation.EngineDesignationID',
            'enginebase' => 'LEFT JOIN enginebase ON engineconfig.EngineBaseID = enginebase.EngineBaseID'
            
        ];


        $vcdb_tables_per_qualifier = [
            'AspirationID' => ['vehicletoengineconfig','engineconfig','aspiration'],
            'BedLengthID' => ['vehicletobedconfig','bedconfig','bedlength'],
            'BedTypeID' => ['vehicletobedconfig','bedconfig','bedtype'],
            'BodyNumDoorsID' => ['vehicletobodystyleconfig','bodystyleconfig','bodynumdoors'],
            'BodyTypeID' => ['vehicletobodystyleconfig','bodystyleconfig','bodytype'],
            'BrakeABSID' => ['vehicletobrakeconfig','braketype_front','brakeabs'],
            'BrakeSystemID' => ['vehicletobrakeconfig','braketype_front','brakesystem'],
            'CylinderHeadTypeID' => ['vehicletoengineconfig','engineconfig','cylinderheadtype'],
            'DriveTypeID' => ['vehicletodrivetype','drivetype'],
            'EngineDesignationID' => ['vehicletoengineconfig','engineconfig','enginedesignation'],
            'EngineMfrID' => ['vehicletoengineconfig','engineconfig','engine_mfr'],//actual table name is mfr only
            'EngineVersionID' => ['vehicletoengineconfig','engineconfig','mfr_engine'],
            'EngineVINID' => ['vehicletoengineconfig','engineconfig','enginevin'],
            'FrontBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype'],
            'FrontSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype'],
            'FuelDeliverySubTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverysubtype'],
            'FuelDeliveryTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverytype'],
            'FuelSystemControlTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemcontroltype'],
            'FuelSystemDesignID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemdesign'],
            'FuelTypeID' => ['vehicletoengineconfig','engineconfig','fueltype'],
            'IgnitionSystemTypeID' => ['vehicletoengineconfig','engineconfig','ignitionsystemtype'],
            'MfrBodyCodeID' => ['vehicletomfrbodycode','mfrbodycode'],
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => ['vehicletoengineconfig','engineconfig','poweroutput'],
            'RearBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype_rear'],
            'RearSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype_rear'],
            'RegionID' => ['region'],
            'SteeringSystemID' => ['vehicletosteeringconfig','steeringconfig','steeringsystem'],
            'SteeringTypeID' => ['vehicletosteeringconfig','steeringconfig','steeringtype'],
            'SubModelID' => ['submodel'],
            'TransElecControlledID' => ['vehicletotransmission','transmission','eleccontrolled'],
            'TransmissionControlTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissioncontroltype'],
            'TransmissionMfrCodeID' => ['vehicletotransmission','transmission','transmissionmfrcode'],
            'TransmissionMfrID' => ['vehicletotransmission','transmission','mfr'],
            'TransmissionNumSpeedsID' => ['vehicletotransmission','transmission','transmissionbase','transmissionnumspeeds'],
            'TransmissionTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissiontype'],
            'ValvesPerEngineID' => ['vehicletoengineconfig','engineconfig','valves'],
            'WheelBaseID' => ['vehicletowheelbase','wheelbase']
        ];

        //Example questions (get the real one's off of John):
        //Accompanied by an optional graphic (like the one I sent in a Word document previously):

        $question_graphics = [
            'EngineVINID' => '/path/to/a/graphic',
        ];

        //We don't use position as it's a part specific qualifier not vehicle specific
        //Strings contains both single and double quotes so we use heredoc syntax.
        $vcdb_selects_per_qualifier['AspirationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',aspiration.AspirationID,'":{"Value":"',TRIM(aspiration.AspirationName),'","IsQualifierInDM":',IF(aspiration.AspirationID [CONDITION],'true','false'),'}') ORDER BY aspiration.AspirationName SEPARATOR ','),'}') AS AspirationID
        EOT;
        $vcdb_selects_per_qualifier['BedLengthID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedlength.BedLengthID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(bedlength.BedLength),'in'),CONCAT(TRIM(bedlength.BedLengthMetric),'cm')),'","IsQualifierInDM":',IF(bedlength.BedLengthID [CONDITION],'true','false'),'}') ORDER BY bedlength.BedLength,BedLengthMetric SEPARATOR ','),'}') AS BedLengthID
        EOT;

        $vcdb_selects_per_qualifier['BedTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedtype.BedTypeID,'":{"Value":"',TRIM(bedtype.BedTypeName),'","IsQualifierInDM":',IF(bedtype.BedTypeID [CONDITION],'true','false'),'}') ORDER BY bedtype.BedTypeName SEPARATOR ','),'}') AS BedTypeID
        EOT;

        $vcdb_selects_per_qualifier['BodyNumDoorsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodynumdoors.BodyNumDoorsID,'":{"Value":"',TRIM(bodynumdoors.BodyNumDoors),'","IsQualifierInDM":',IF(bodynumdoors.BodyNumDoorsID [CONDITION],'true','false'),'}') ORDER BY bodynumdoors.BodyNumDoors SEPARATOR ','),'}') AS BodyNumDoorsID
        EOT;
        $vcdb_selects_per_qualifier['BodyTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodytype.BodyTypeID,'":{"Value":"',TRIM(bodytype.BodyTypeName),'","IsQualifierInDM":',IF(bodytype.BodyTypeID [CONDITION],'true','false'),'}') ORDER BY bodytype.BodyTypeName SEPARATOR ','),'}') AS BodyTypeID
        EOT;
        $vcdb_selects_per_qualifier['BrakeABSID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakeabs.BrakeABSID,'":{"Value":"',TRIM(brakeabs.BrakeABSName),'","IsQualifierInDM":',IF(brakeabs.BrakeABSID [CONDITION],'true','false'),'}') ORDER BY brakeabs.BrakeABSName SEPARATOR ','),'}') AS BrakeABSID
        EOT;
        $vcdb_selects_per_qualifier['BrakeSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakesystem.BrakeSystemID,'":{"Value":"',TRIM(brakesystem.BrakeSystemName),'","IsQualifierInDM":',IF(brakesystem.BrakeSystemID [CONDITION],'true','false'),'}') ORDER BY brakesystem.BrakeSystemName SEPARATOR ','),'}') AS BrakeSystemID
        EOT;
        $vcdb_selects_per_qualifier['CylinderHeadTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',cylinderheadtype.CylinderHeadTypeID,'":{"Value":"',TRIM(cylinderheadtype.CylinderHeadTypeName),'","IsQualifierInDM":',IF(cylinderheadtype.CylinderHeadTypeID [CONDITION],'true','false'),'}') ORDER BY cylinderheadtype.CylinderHeadTypeName SEPARATOR ','),'}') AS CylinderHeadTypeID
        EOT;
        $vcdb_selects_per_qualifier['DriveTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',drivetype.DriveTypeID,'":{"Value":"',TRIM(drivetype.DriveTypeName),'","IsQualifierInDM":',IF(drivetype.DriveTypeID [CONDITION],'true','false'),'}') ORDER BY drivetype.DriveTypeName SEPARATOR ','),'}') AS DriveTypeID
        EOT;
        $vcdb_selects_per_qualifier['EngineDesignationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginedesignation.EngineDesignationID,'":{"Value":"',TRIM(enginedesignation.EngineDesignationName),'","IsQualifierInDM":',IF(enginedesignation.EngineDesignationID [CONDITION],'true','false'),'}') ORDER BY enginedesignation.EngineDesignationName SEPARATOR ','),'}') AS EngineDesignationID
        EOT;
        $vcdb_selects_per_qualifier['EngineMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS EngineMfrID
        EOT;
        $vcdb_selects_per_qualifier['EngineVersionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',engineversion.EngineVersionID,'":{"Value":"',TRIM(engineversion.EngineVersion),'","IsQualifierInDM":',IF(engineversion.EngineVersionID [CONDITION],'true','false'),'}') ORDER BY engineversion.EngineVersion SEPARATOR ','),'}') AS EngineVersionID
        EOT;
        $vcdb_selects_per_qualifier['EngineVINID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginevin.EngineVINID,'":{"Value":"',TRIM(enginevin.EngineVINName),'","IsQualifierInDM":',IF(enginevin.EngineVINID [CONDITION],'true','false'),'}') ORDER BY enginevin.EngineVINName SEPARATOR ','),'}') AS EngineVINID
        EOT;
        $vcdb_selects_per_qualifier['FrontBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_front.BrakeTypeID,'":{"Value":"',TRIM(braketype_front.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_front.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_front.BrakeTypeName SEPARATOR ','),'}') AS FrontBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['FrontSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_front.SpringTypeID,'":{"Value":"',TRIM(springtype_front.SpringTypeName),'","IsQualifierInDM":',IF(springtype_front.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_front.SpringTypeName SEPARATOR ','),'}') AS FrontSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliverySubTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverysubtype.FuelDeliverySubTypeID,'":{"Value":"',TRIM(fueldeliverysubtype.FuelDeliverySubTypeName),'","IsQualifierInDM":',IF(fueldeliverysubtype.FuelDeliverySubTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverysubtype.FuelDeliverySubTypeName SEPARATOR ','),'}') AS FuelDeliverySubTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliveryTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverytype.FuelDeliveryTypeID,'":{"Value":"',TRIM(fueldeliverytype.FuelDeliveryTypeName),'","IsQualifierInDM":',IF(fueldeliverytype.FuelDeliveryTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverytype.FuelDeliveryTypeName SEPARATOR ','),'}') AS FuelDeliveryTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemcontroltype.FuelSystemControlTypeID,'":{"Value":"',TRIM(fuelsystemcontroltype.FuelSystemControlTypeName),'","IsQualifierInDM":',IF(fuelsystemcontroltype.FuelSystemControlTypeID [CONDITION],'true','false'),'}') ORDER BY fuelsystemcontroltype.FuelSystemControlTypeName SEPARATOR ','),'}') AS FuelSystemControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemDesignID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemdesign.FuelSystemDesignID,'":{"Value":"',TRIM(fuelsystemdesign.FuelSystemDesignName),'","IsQualifierInDM":',IF(fuelsystemdesign.FuelSystemDesignID [CONDITION],'true','false'),'}') ORDER BY fuelsystemdesign.FuelSystemDesignName SEPARATOR ','),'}') AS FuelSystemDesignID
        EOT;
        $vcdb_selects_per_qualifier['FuelTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueltype.FuelTypeID,'":{"Value":"',TRIM(fueltype.FuelTypeName),'","IsQualifierInDM":',IF(fueltype.FuelTypeID [CONDITION],'true','false'),'}') ORDER BY fueltype.FuelTypeName SEPARATOR ','),'}') AS FuelTypeID
        EOT;
        $vcdb_selects_per_qualifier['IgnitionSystemTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',ignitionsystemtype.IgnitionSystemTypeID,'":{"Value":"',TRIM(ignitionsystemtype.IgnitionSystemTypeName),'","IsQualifierInDM":',IF(ignitionsystemtype.IgnitionSystemTypeID [CONDITION],'true','false'),'}') ORDER BY ignitionsystemtype.IgnitionSystemTypeName SEPARATOR ','),'}') AS IgnitionSystemTypeID
        EOT;
        $vcdb_selects_per_qualifier['MfrBodyCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfrbodycode.MfrBodyCodeID,'":{"Value":"',TRIM(mfrbodycode.MfrBodyCodeName),'","IsQualifierInDM":',IF(mfrbodycode.MfrBodyCodeID [CONDITION],'true','false'),'}') ORDER BY mfrbodycode.MfrBodyCodeName SEPARATOR ','),'}') AS MfrBodyCodeID
        EOT;
        $vcdb_selects_per_qualifier['PositionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',positions.PositionID,'":{"Value":"',TRIM(positions.Position),'","IsQualifierInDM":',IF(positions.PositionID [CONDITION],'true','false'),'}') ORDER BY positions.Position SEPARATOR ','),'}') AS PositionID
        EOT;
        $vcdb_selects_per_qualifier['PowerOutputID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',poweroutput.PowerOutputID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(poweroutput.HorserPower),'HP'),CONCAT(TRIM(poweroutput.KilowattPower),'kW')),'","IsQualifierInDM":',IF(poweroutput.PowerOutputID [CONDITION],'true','false'),'}') ORDER BY poweroutput.HorsePower,KilowattPower SEPARATOR ','),'}') AS PowerOutputID
        EOT;
        $vcdb_selects_per_qualifier['RearBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_rear.BrakeTypeID,'":{"Value":"',TRIM(braketype_rear.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_rear.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_rear.BrakeTypeName SEPARATOR ','),'}') AS RearBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['RearSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_rear.SpringTypeID,'":{"Value":"',TRIM(springtype_rear.SpringTypeName),'","IsQualifierInDM":',IF(springtype_rear.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_rear.SpringTypeName SEPARATOR ','),'}') AS RearSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['RegionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',region.RegionID,'":{"Value":"',TRIM(region.RegionName),'","IsQualifierInDM":',IF(region.RegionID [CONDITION],'true','false'),'}') ORDER BY region.RegionAbbr,RegionName SEPARATOR ','),'}') AS RegionID
        EOT;
        $vcdb_selects_per_qualifier['SteeringSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringsystem.SteeringSystemID,'":{"Value":"',TRIM(steeringsystem.SteeringSystemName),'","IsQualifierInDM":',IF(steeringsystem.SteeringSystemID [CONDITION],'true','false'),'}') ORDER BY steeringsystem.SteeringSystemName SEPARATOR ','),'}') AS SteeringSystemID
        EOT;
        $vcdb_selects_per_qualifier['SteeringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringtype.SteeringTypeID,'":{"Value":"',TRIM(steeringtype.SteeringTypeName),'","IsQualifierInDM":',IF(steeringtype.SteeringTypeID [CONDITION],'true','false'),'}') ORDER BY steeringtype.SteeringTypeName SEPARATOR ','),'}') AS SteeringTypeID
        EOT;
        $vcdb_selects_per_qualifier['SubModelID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',submodel.SubModelID,'":{"Value":"',TRIM(submodel.SubModelName),'","IsQualifierInDM":',IF(submodel.SubModelID [CONDITION],'true','false'),'}') ORDER BY submodel.SubModelName SEPARATOR ','),'}') AS SubModelID
        EOT;
        $vcdb_selects_per_qualifier['TransElecControlledID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',eleccontrolled.ElecControlledID,'":{"Value":"',TRIM(eleccontrolled.ElecControlled),'","IsQualifierInDM":',IF(eleccontrolled.ElecControlledID [CONDITION],'true','false'),'}') ORDER BY eleccontrolled.ElecControlled SEPARATOR ','),'}') AS TransElecControlledID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissioncontroltype.TransmissionControlTypeID,'":{"Value":"',TRIM(transmissioncontroltype.TransmissionControlTypeName),'","IsQualifierInDM":',IF(transmissioncontroltype.TransmissionControlTypeID [CONDITION],'true','false'),'}') ORDER BY transmissioncontroltype.TransmissionControlTypeName SEPARATOR ','),'}') AS TransmissionControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionmfrcode.TransmissionMfrCodeID,'":{"Value":"',TRIM(transmissionmfrcode.TransmissionMfrCode),'","IsQualifierInDM":',IF(transmissionmfrcode.TransmissionMfrCodeID [CONDITION],'true','false'),'}') ORDER BY transmissionmfrcode.TransmissionMfrCode SEPARATOR ','),'}') AS TransmissionMfrCodeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr1.MfrID,'":{"Value":"',TRIM(mfr1.MfrName),'","IsQualifierInDM":',IF(mfr1.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr1.MfrName SEPARATOR ','),'}') AS TransmissionMfrID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionNumSpeedsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionnumspeeds.TransmissionNumSpeedsID,'":{"Value":"',TRIM(transmissionnumspeeds.TransmissionNumSpeeds),'","IsQualifierInDM":',IF(transmissionnumspeeds.TransmissionNumSpeedsID [CONDITION],'true','false'),'}') ORDER BY transmissionnumspeeds.TransmissionNumSpeeds SEPARATOR ','),'}') AS TransmissionNumSpeedsID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissiontype.TransmissionTypeID,'":{"Value":"',TRIM(transmissiontype.TransmissionTypeName),'","IsQualifierInDM":',IF(transmissiontype.TransmissionTypeID [CONDITION],'true','false'),'}') ORDER BY transmissiontype.TransmissionTypeName SEPARATOR ','),'}') AS TransmissionTypeID
        EOT;
        $vcdb_selects_per_qualifier['ValvesPerEngineID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',valves.ValvesID,'":{"Value":"',TRIM(valves.ValvesPerEngine),'","IsQualifierInDM":',IF(valves.ValvesID [CONDITION],'true','false'),'}') ORDER BY valves.ValvesPerEngine SEPARATOR ','),'}') AS ValvesPerEngineID
        EOT;
        $vcdb_selects_per_qualifier['WheelBaseID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',wheelbase.WheelBaseID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(wheelbase.WheelBase),'in'),CONCAT(TRIM(wheelbase.WheelBaseMetric),'cm')),'","IsQualifierInDM":',IF(wheelbase.WheelBaseID [CONDITION],'true','false'),'}') ORDER BY wheelbase.WheelBase,WheelBaseMetric SEPARATOR ','),'}') AS WheelBaseID
        EOT;


        $vehicle_definition = [
            'VehicleID' => 0,
            'BaseVehicleID' => 0,
            'YearID' => 0,
            'MakeID' => 0,
            'ModelID' => 0,
            'EngineConfigID' => 0,
            'EngineBaseID' => 0,
            
            /*The above are vehicle identifiers - typically a VehicleID or BaseVehicleID will be given
              or year, make, model (for which the basevehicle table will be needed)
              and one of EngineConfigID or EngineBaseID is needed.
              If the API's XML only returns values then more tables, e.g. make, model, will be needed.
             */    
              'AspirationID' => "Aspiration",
              'BedLengthID' => "Bed Length",
              'BedTypeID' => "Bed Type",
              'BodyNumDoorsID' => "Number of Doors",
              'BodyTypeID' => "Body Type",
              'BrakeABSID' => "ABS Type",
              'BrakeSystemID' => "Brake System",
              'CylinderHeadTypeID' => "Cylinder Head",
              'DriveTypeID' => "Drive Type",
              'EngineDesignationID' => "Engine Design",
              'EngineMfrID' => "Engine Mfr",
              'EngineVersionID' => "Engine Version",
              'EngineVINID' => "VIN",
              'FrontBrakeTypeID' => "Front Brake",
              'FrontSpringTypeID' => "Front Spring",
              'FuelDeliverySubTypeID' => "Fuel Del. Sub Type",
              'FuelDeliveryTypeID' => "Fuel Del. Type",
              'FuelSystemControlTypeID' => "Fuel Sys. Control",
              'FuelSystemDesignID' => "Fuel Sys. Design",
              'FuelTypeID' => "Fuel",
              'IgnitionSystemTypeID' => "Ignition",
              'MfrBodyCodeID' => "Body Code",
              'PositionID' => 0,
              'PowerOutputID' => "Power Output",
              'RearBrakeTypeID' => "Rear Brake",
              'RearSpringTypeID' => "Rear Spring",
              'RegionID' => "Region",
              'SteeringSystemID' => "Steering System",
              'SteeringTypeID' => "Steering",
              'SubModelID' => "SubModel",
              'TransElecControlledID' => "Trans Elec Ctrl",
              'TransmissionControlTypeID' => "Trans Ctrl Type",
              'TransmissionMfrCodeID' => "Trans Mfr Code",
              'TransmissionMfrID' => "Transmission Mfr",
              'TransmissionNumSpeedsID' => "Trans Num Speeds",
              'TransmissionTypeID' => "Trans Type",
              'ValvesPerEngineID' => "Valves Per Engine",
              'WheelBaseID' => "Wheel Base",
        
        ];

        $save_fluis_pt_string=implode(",",$save_fluid_pt);
        $save_normal_pt_string=implode(",",$save_normal_pt);

        
        if($save_fluis_pt_string!="" && ($save_normal_pt_string!="" || $save_normal_pt_string=="")) {
            $data_qualifiers_select = <<<EOT
            SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelTypeID,0) ORDER BY IFNULL(af.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.AspirationID,0) ORDER BY IFNULL(af.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.DriveTypeID,0) ORDER BY IFNULL(af.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineVINID,0) ORDER BY IFNULL(af.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineDesignationID,0) ORDER BY IFNULL(af.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.CylinderHeadTypeID,0) ORDER BY IFNULL(af.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SubModelID,0) ORDER BY IFNULL(af.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionControlTypeID,0) ORDER BY IFNULL(af.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionNumSpeedsID,0) ORDER BY IFNULL(af.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.ValvesPerEngineID,0) ORDER BY IFNULL(af.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelDeliveryTypeID,0) ORDER BY IFNULL(af.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BedTypeID,0) ORDER BY IFNULL(af.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BodyNumDoorsID,0) ORDER BY IFNULL(af.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineMfrID,0) ORDER BY IFNULL(af.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelDeliverySubTypeID,0) ORDER BY IFNULL(af.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelSystemControlTypeID,0) ORDER BY IFNULL(af.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelSystemDesignID,0) ORDER BY IFNULL(af.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SteeringTypeID,0) ORDER BY IFNULL(af.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionTypeID,0) ORDER BY IFNULL(af.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BedLengthID,0) ORDER BY IFNULL(af.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BodyTypeID,0) ORDER BY IFNULL(af.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BrakeABSID,0) ORDER BY IFNULL(af.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BrakeSystemID,0) ORDER BY IFNULL(af.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineVersionID,0) ORDER BY IFNULL(af.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FrontBrakeTypeID,0) ORDER BY IFNULL(af.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FrontSpringTypeID,0) ORDER BY IFNULL(af.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.IgnitionSystemTypeID,0) ORDER BY IFNULL(af.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.MfrBodyCodeID,0) ORDER BY IFNULL(af.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.PowerOutputID,0) ORDER BY IFNULL(af.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RearBrakeTypeID,0) ORDER BY IFNULL(af.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RearSpringTypeID,0) ORDER BY IFNULL(af.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RegionID,0) ORDER BY IFNULL(af.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SteeringSystemID,0) ORDER BY IFNULL(af.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransElecControlledID,0) ORDER BY IFNULL(af.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionMfrID,0) ORDER BY IFNULL(af.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionMfrCodeID,0) ORDER BY IFNULL(af.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.WheelBaseID,0) ORDER BY IFNULL(af.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
            EOT;
            }
            else {
            $data_qualifiers_select = <<<EOT
            SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
            EOT;
        }

        if(in_array("11",$explode_part) || in_array("15",$explode_part) || in_array("17",$explode_part)) {

            if($save_fluis_pt_string!="" && $save_normal_pt_string!="") { // both need more info clicked (for fluids and caps/wiper/filters) 
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                //             WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16".','.$save_normal_pt_string.") OR a.partSubtype IN(1,2,3,4)) AND af.partTypeid IN (".$save_fluis_pt_string.")");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                            WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832".','.$save_normal_pt_string.") AND af.partTypeid IN (".$save_fluis_pt_string.")");

            }
            elseif($save_fluis_pt_string!="" && $save_normal_pt_string=="") { // only first fluids need more info clicked
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16) OR a.partSubtype IN(1,2,3,4)) AND af.partTypeid IN (".$save_fluis_pt_string.")");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832) AND af.partTypeid IN (".$save_fluis_pt_string.")");
            }
            else { // only first caps/wiper/filters need more info clicked
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a  JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16".','.$save_normal_pt_string.") OR a.partSubtype IN(1,2,3,4))");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a  JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832".','.$save_normal_pt_string.")");
            }
        }
        else {

            if($parttype!="") {
        
                if($save_fluis_pt_string!="" && $save_normal_pt_string!="" ) { // both need more info clicked (for fluids and caps/wiper/filters)

                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    // WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.partTypeid IN (1,2,3,7,12,13,16".','.$save_normal_pt_string.")");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.PartTerminologyID IN (5340,6192,6832".','.$save_normal_pt_string.")");
                }
                elseif($save_fluis_pt_string!="" && $save_normal_pt_string=="") {
                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    // WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.partTypeid IN (1,2,3,7,12,13,16)");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.PartTerminologyID IN (5340,6192,6832)");
                }
                else { // only first caps/wiper/filters need more info clicked
                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid  
                    // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.partTypeid IN (1,2,3,7,12,13,16".','.$save_normal_pt_string.")");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID  
                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.PartTerminologyID IN (5340,6192,6832".','.$save_normal_pt_string.")");
                }
        
            }
            else  {
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid  WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.partTypeid IN (1,2,3,7,12,13,16)");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID  WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.PartTerminologyID IN (5340,6192,6832)");   
            }
        }


        $a=$results[0];
        $get_data_json=$a->data_json;

        $f="";


        if($get_data_json==null) {
            $f="fail";
            return json_encode($f);
            }
            else {
            $vcdb_tables = [];
            $vcdb_selects = [];
            $replace_count = 1;
            $result=[];

            $get_data_json=json_decode($get_data_json,true);

            foreach ($get_data_json as $qualifier_type => $qualifier_ids)  {
                if($qualifier_type != 'PositionID') {

                    if($qualifier_ids!=NULL) {
                        $result = array_diff($qualifier_ids, [0]);
                    }

                    if(count($result) >= 1 || $qualifier_ids[0] != 0) {

                        if(!empty($vehicle_definition[$qualifier_type])) {

                            $vcdb_tables = array_merge($vcdb_tables,$vcdb_tables_per_qualifier[$qualifier_type]);
                            $original_select = $vcdb_selects_per_qualifier[$qualifier_type];

                            if($qualifier_ids[0] == 0) {
                                //Any criteria will do here so as IDs can't be negative this will always return true
                                $final_select = str_replace('[CONDITION]','!= -1000',$original_select,$replace_count);

                            }
                            else {
                                $final_select = str_replace('[CONDITION]',('IN(' . implode(',', $qualifier_ids) . ')'),$original_select,$replace_count);
                            }
                            $vcdb_selects[] = $final_select;

                        }
                    }

                }
            }//foreach

            if(count($vcdb_tables) == 0) {
                $f="fail";
                return json_encode($f);
            }
            else {
                $vcdb_tables[] = 'vehicletoengineconfig';
                $vcdb_tables[] = 'engineconfig';

                $vcdb_tables_unique = array_unique($vcdb_tables);
                $vcdb_qualifiers_select = 'SELECT ' . implode(', ',$vcdb_selects) . ' FROM vehicle';

                foreach($vcdb_tables_unique as $table) {
                    $vcdb_qualifiers_select = $vcdb_qualifiers_select . ' ' . $vcdb_table_joins[$table];
                }

                $vcdb_qualifiers_select= DB::connection(self::$aces_vcdb)->select($vcdb_qualifiers_select . ' WHERE vehicle.BaseVehicleID ='.$baseVehicleId.'  AND engineconfig.EngineBaseID IN('.$engineId.')'.$qualifierlist);
                  
            }

            if(empty($vcdb_qualifiers_select)) {
                $f="fail";
                return json_encode($f);
            }
            else {

                $get_value_of_q["values"]=[];
                $get_value_of_q["name"]=[];
                $qualifier_name="";

                for($i=0;$i<1;$i++) {
                    foreach($vcdb_qualifiers_select[$i] as $qualifier_type => $qualifier_type_json) {

                        $stop_process=0;
                        $matched_value=0;
                        foreach($store_upcoming as $store_upcoming1) {

                            $ex=explode(":",$store_upcoming1);
                            if($ex[0]==$qualifier_type) {
                                $matched_value=1;
                            }
                        }

                        if($matched_value==0) {
                            $qualifier_type_json_array = json_decode($qualifier_type_json,true);
                            $qualifier_name=$vehicle_definition[$qualifier_type];
                            array_push($get_value_of_q["name"],$qualifier_name);

                            foreach($qualifier_type_json_array as $qualifier_id => $qualifier_detail) {
                                $header="";
                                $header='{"id":"'.$qualifier_type.":".$qualifier_id.'","values":"'.$qualifier_detail['Value'].'"}';
                                $header=json_decode($header);
                                array_push($get_value_of_q["values"],$header);
                            }

                        }
                        else {
                            continue;
                        }

                        if($stop_process==0) {
                            break;
                        }

                    }

                    return $get_value_of_q;
                }
            }

        }

    }

    public function index(Request $request, $baseVehicleId, $engineId, $request_qualifierSelected, $parttype, $partsubtype)
    {
        $baseVehicleId = (int) $baseVehicleId;
        $engineId = implode(',', array_map('intval', explode(',', $engineId ?? '')));

        // Filtering request to get unique values
        $qualifierSelected = implode(',', array_unique(explode(',', $request_qualifierSelected ?? '')));

        $qualifier_main_table=[
            'AspirationID' => 'aspiration.AspirationID',
            'BedLengthID' => 'bedlength.BedLengthID',
            'BedTypeID' => 'bedtype.BedTypeID',
            'BodyNumDoorsID' => 'bodynumdoors.BodyNumDoorsID',
            'BodyTypeID' => 'bodytype.BodyTypeID',
            'BrakeABSID' => 'brakeabs.BrakeABSID',
            'BrakeSystemID' => 'brakesystem.BrakeSystemID',
            'CylinderHeadTypeID' => 'cylinderheadtype.CylinderHeadTypeID',
            'DriveTypeID' => 'drivetype.DriveTypeID',
            'EngineDesignationID' => 'enginedesignation.EngineDesignationID',
            'EngineMfrID' => 'mfr.MfrID',
            'EngineVersionID' => 'engineversion.EngineVersionID',
            'EngineVINID' => 'enginevin.EngineVINID',
            'FrontBrakeTypeID' => 'braketype_front.BrakeTypeID',
            'FrontSpringTypeID' => 'springtype_front.SpringTypeID',
            'FuelDeliverySubTypeID' => 'fueldeliverysubtype.FuelDeliverySubTypeID',
            'FuelDeliveryTypeID' => 'fueldeliverytype.FuelDeliveryTypeID',
            'FuelSystemControlTypeID' => 'fuelsystemcontroltype.FuelSystemControlTypeID',
            'FuelSystemDesignID' => 'fuelsystemdesign.FuelSystemDesignID',
            'FuelTypeID' => 'fueltype.FuelTypeID',
            'IgnitionSystemTypeID' => 'ignitionsystemtype.IgnitionSystemTypeID',
            'MfrBodyCodeID' => 'mfrbodycode.MfrBodyCodeID',
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => 'poweroutput.PowerOutputID',
            'RearBrakeTypeID' => 'braketype_rear.BrakeTypeID',
            'RearSpringTypeID' => 'springtype_rear.SpringTypeID',
            'RegionID' => 'region.RegionID',
            'SteeringSystemID' => 'steeringsystem.SteeringSystemID',
            'SteeringTypeID' => 'steeringtype.SteeringTypeID',
            'SubModelID' => 'submodel.SubModelID',
            'TransElecControlledID' => 'eleccontrolled.ElecControlledID',
            'TransmissionControlTypeID' => 'transmissioncontroltype.TransmissionControlTypeID',
            'TransmissionMfrCodeID' => 'transmissionmfrcode.TransmissionMfrCodeID',
            'TransmissionMfrID' => 'mfr1.MfrID',
            'TransmissionNumSpeedsID' => 'transmissionnumspeeds.TransmissionNumSpeedsID',
            'TransmissionTypeID' => 'transmissiontype.TransmissionTypeID',
            'ValvesPerEngineID' => 'valves.ValvesID',
            'WheelBaseID' => 'wheelbase.WheelBaseID'
        ];

        
        $store_upcoming=[];
        
        $explode_part=explode(",",$parttype);
        $store_upcoming=array_unique(explode(",",$qualifierSelected));
        
        $qualifierlist = '';
        $firstQualifier = reset($store_upcoming);
        if ($firstQualifier && $firstQualifier !== 'noqualifier') {
            $where_clause = [];
            foreach ($store_upcoming as $value) {
                $qual = explode(':', $value);
                if (isset($qualifier_main_table[$qual[0]])) {
                    $where_clause[] = ' AND ' . $qualifier_main_table[$qual[0]] . '=' . (int) ($qual[1] ?? 0);
                }
            }
            $qualifierlist = implode(' ', $where_clause);
        }

        $fueltype_table = 'fueltype';


        $fluids_from_query = DB::connection(self::$main_db)->table('fluids_parttype_master')->pluck('partTypeid')->toArray();

        $save_fluid_pt = [];
        $save_normal_pt = [];
        foreach ($explode_part as $partId) {
            if (in_array($partId, $fluids_from_query)) {
                $save_fluid_pt[] = $partId;
            } else {
                $save_normal_pt[] = $partId;
            }
        }
        

        //We use left joins in case the VCDB isn't fully populated

        $vcdb_table_joins = [
            'basevehicle' => 'LEFT JOIN basevehicle ON vehicle.BaseVehicleID = basevehicle.BaseVehicleID',
            'region' => 'LEFT JOIN region ON vehicle.RegionID = region.RegionID',
            'submodel' => 'LEFT JOIN submodel ON vehicle.SubmodelID = submodel.SubModelID',
            'vehicletospringtypeconfig' => 'LEFT JOIN vehicletospringtypeconfig ON vehicle.VehicleID = vehicletospringtypeconfig.VehicleID',
            'springtype_front' => 'LEFT JOIN springtypeconfig ON vehicletospringtypeconfig.SpringTypeConfigID = springtypeconfig.SpringTypeConfigID',
            'springtype' => 'LEFT JOIN springtype springtype_front ON springtypeconfig.FrontSpringTypeID = springtype_front.SpringTypeID',
            'springtype_rear' => 'LEFT JOIN springtype springtype_rear ON springtypeconfig.RearSpringTypeID = springtype_rear.SpringTypeID',
            'vehicletosteeringconfig' => 'LEFT JOIN vehicletosteeringconfig ON vehicle.VehicleID = vehicletosteeringconfig.VehicleID',
            'steeringconfig' => 'LEFT JOIN steeringconfig ON vehicletosteeringconfig.SteeringConfigID = steeringconfig.SteeringConfigID',
            'steeringtype' => 'LEFT JOIN steeringtype ON steeringconfig.SteeringTypeID = steeringtype.SteeringTypeID',
            'steeringsystem' => 'LEFT JOIN steeringsystem ON steeringconfig.SteeringSystemID = steeringsystem.SteeringSystemID',
            'vehicletobedconfig' => 'LEFT JOIN vehicletobedconfig ON vehicle.VehicleID = vehicletobedconfig.VehicleID',
            'bedconfig' => 'LEFT JOIN bedconfig ON vehicletobedconfig.BedConfigID = bedconfig.BedConfigID',
            'bedtype' => 'LEFT JOIN bedtype ON bedconfig.BedTypeID = bedtype.BedTypeID',
            'bedlength' => 'LEFT JOIN bedlength ON bedconfig.BedLengthID = bedlength.BedLengthID',
            'vehicletobrakeconfig' => 'LEFT JOIN vehicletobrakeconfig ON vehicle.VehicleID = vehicletobrakeconfig.VehicleID',
            'braketype_front' => 'LEFT JOIN brakeconfig ON vehicletobrakeconfig.BrakeConfigID = brakeconfig.BrakeConfigID',
            'braketype' => 'LEFT JOIN braketype braketype_front ON brakeconfig.FrontBrakeTypeID = braketype_front.BrakeTypeID',
            'braketype_rear' => 'LEFT JOIN braketype braketype_rear ON brakeconfig.RearBrakeTypeID = braketype_rear.BrakeTypeID',
            'brakeabs' => 'LEFT JOIN brakeabs ON brakeconfig.BrakeABSID = brakeabs.BrakeABSID',
            'brakesystem' => 'LEFT JOIN brakesystem ON brakeconfig.BrakeSystemID = brakesystem.BrakeSystemID',
            'vehicletodrivetype' => 'LEFT JOIN vehicletodrivetype ON vehicle.VehicleID = vehicletodrivetype.VehicleID',
            'drivetype' => 'LEFT JOIN drivetype ON vehicletodrivetype.DriveTypeID = drivetype.DriveTypeID',
            'vehicletowheelbase' => 'LEFT JOIN vehicletowheelbase ON vehicle.VehicleID = vehicletowheelbase.VehicleID',
            'wheelbase' => 'LEFT JOIN wheelbase ON vehicletowheelbase.WheelbaseID = wheelbase.WheelBaseID',
            'vehicletobodystyleconfig' => 'LEFT JOIN vehicletobodystyleconfig ON vehicle.VehicleID = vehicletobodystyleconfig.VehicleID',
            'bodystyleconfig' => 'LEFT JOIN bodystyleconfig ON vehicletobodystyleconfig.BodyStyleConfigID = bodystyleconfig.BodyStyleConfigID',
            'bodynumdoors' => 'LEFT JOIN bodynumdoors ON bodystyleconfig.BodyNumDoorsID = bodynumdoors.BodyNumDoorsID',
            'bodytype' => 'LEFT JOIN bodytype ON bodystyleconfig.BodyTypeID = bodytype.BodyTypeID',
            'vehicletomfrbodycode' => 'LEFT JOIN vehicletomfrbodycode ON vehicle.VehicleID = vehicletomfrbodycode.VehicleID',
            'mfrbodycode' => 'LEFT JOIN mfrbodycode ON vehicletomfrbodycode.MfrBodyCodeID = mfrbodycode.MfrBodyCodeID',
            'vehicletotransmission' => 'LEFT JOIN vehicletotransmission ON vehicle.VehicleID = vehicletotransmission.VehicleID',
            'transmission' => 'LEFT JOIN transmission ON vehicletotransmission.TransmissionID = transmission.TransmissionID',
            'eleccontrolled' => 'LEFT JOIN eleccontrolled ON transmission.TransmissionElecControlledID = eleccontrolled.ElecControlledID',
            'transmissionbase' => 'LEFT JOIN transmissionbase ON transmission.TransmissionBaseID = transmissionbase.TransmissionBaseID',
            'transmissiontype' => 'LEFT JOIN transmissiontype ON transmissionbase.TransmissionTypeID = transmissiontype.TransmissionTypeID',
            'transmissioncontroltype' => 'LEFT JOIN transmissioncontroltype ON transmissionbase.TransmissionControlTypeID = transmissioncontroltype.TransmissionControlTypeID',
            'transmissionnumspeeds' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr_transmission' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr' => 'LEFT JOIN mfr mfr1 ON transmission.TransmissionMfrID = mfr1.MfrID',
            'transmissionmfrcode' => 'LEFT JOIN transmissionmfrcode ON transmission.TransmissionMfrCodeID = transmissionmfrcode.TransmissionMfrCodeID',
            'vehicletoengineconfig' => 'LEFT JOIN vehicletoengineconfig ON vehicle.VehicleID = vehicletoengineconfig.VehicleID',
            'engineconfig' => 'LEFT JOIN engineconfig ON vehicletoengineconfig.EngineConfigID = engineconfig.EngineConfigID',
            'ignitionsystemtype' => 'LEFT JOIN ignitionsystemtype ON engineconfig.IgnitionSystemTypeID = ignitionsystemtype.IgnitionSystemTypeID',
            'cylinderheadtype' => 'LEFT JOIN cylinderheadtype ON engineconfig.CylinderHeadTypeID = cylinderheadtype.CylinderHeadTypeID',
            'aspiration' => 'LEFT JOIN aspiration ON engineconfig.AspirationID = aspiration.AspirationID',
            'fueltype' => "LEFT JOIN $fueltype_table ON engineconfig.FuelTypeID = fueltype.FuelTypeID",
            'valves' => 'LEFT JOIN valves ON engineconfig.ValvesID = valves.ValvesID',
            'enginevin' => 'LEFT JOIN enginevin ON engineconfig.EngineVINID = enginevin.EngineVINID',
            'mfr_engine' => 'LEFT JOIN engineversion ON engineconfig.EngineVersionID = engineversion.EngineVersionID',
            'engine_mfr' => 'LEFT JOIN mfr  ON engineconfig.EngineMfrID = mfr.MfrID',
            'fueldeliveryconfig' => 'LEFT JOIN fueldeliveryconfig ON engineconfig.FuelDeliveryConfigID = fueldeliveryconfig.FuelDeliveryConfigID',
            'fueldeliverytype' => 'LEFT JOIN fueldeliverytype ON fueldeliveryconfig.FuelDeliveryTypeID = fueldeliverytype.FuelDeliveryTypeID',
            'fuelsystemcontroltype' => 'LEFT JOIN fuelsystemcontroltype ON fueldeliveryconfig.FuelSystemControlTypeID = fuelsystemcontroltype.FuelSystemControlTypeID',
            'fueldeliverysubtype' => 'LEFT JOIN fueldeliverysubtype ON fueldeliveryconfig.FuelDeliverySubTypeID = fueldeliverysubtype.FuelDeliverySubTypeID',
            'fuelsystemdesign' => 'LEFT JOIN fuelsystemdesign ON fueldeliveryconfig.FuelSystemDesignID = fuelsystemdesign.FuelSystemDesignID',
            'enginedesignation' => 'LEFT JOIN enginedesignation ON engineconfig.EngineDesignationID = enginedesignation.EngineDesignationID',
            'enginebase' => 'LEFT JOIN enginebase ON engineconfig.EngineBaseID = enginebase.EngineBaseID'
            
        ];


        $vcdb_tables_per_qualifier = [
            'AspirationID' => ['vehicletoengineconfig','engineconfig','aspiration'],
            'BedLengthID' => ['vehicletobedconfig','bedconfig','bedlength'],
            'BedTypeID' => ['vehicletobedconfig','bedconfig','bedtype'],
            'BodyNumDoorsID' => ['vehicletobodystyleconfig','bodystyleconfig','bodynumdoors'],
            'BodyTypeID' => ['vehicletobodystyleconfig','bodystyleconfig','bodytype'],
            'BrakeABSID' => ['vehicletobrakeconfig','braketype_front','brakeabs'],
            'BrakeSystemID' => ['vehicletobrakeconfig','braketype_front','brakesystem'],
            'CylinderHeadTypeID' => ['vehicletoengineconfig','engineconfig','cylinderheadtype'],
            'DriveTypeID' => ['vehicletodrivetype','drivetype'],
            'EngineDesignationID' => ['vehicletoengineconfig','engineconfig','enginedesignation'],
            'EngineMfrID' => ['vehicletoengineconfig','engineconfig','engine_mfr'],//actual table name is mfr only
            'EngineVersionID' => ['vehicletoengineconfig','engineconfig','mfr_engine'],
            'EngineVINID' => ['vehicletoengineconfig','engineconfig','enginevin'],
            'FrontBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype'],
            'FrontSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype'],
            'FuelDeliverySubTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverysubtype'],
            'FuelDeliveryTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverytype'],
            'FuelSystemControlTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemcontroltype'],
            'FuelSystemDesignID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemdesign'],
            'FuelTypeID' => ['vehicletoengineconfig','engineconfig','fueltype'],
            'IgnitionSystemTypeID' => ['vehicletoengineconfig','engineconfig','ignitionsystemtype'],
            'MfrBodyCodeID' => ['vehicletomfrbodycode','mfrbodycode'],
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => ['vehicletoengineconfig','engineconfig','poweroutput'],
            'RearBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype_rear'],
            'RearSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype_rear'],
            'RegionID' => ['region'],
            'SteeringSystemID' => ['vehicletosteeringconfig','steeringconfig','steeringsystem'],
            'SteeringTypeID' => ['vehicletosteeringconfig','steeringconfig','steeringtype'],
            'SubModelID' => ['submodel'],
            'TransElecControlledID' => ['vehicletotransmission','transmission','eleccontrolled'],
            'TransmissionControlTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissioncontroltype'],
            'TransmissionMfrCodeID' => ['vehicletotransmission','transmission','transmissionmfrcode'],
            'TransmissionMfrID' => ['vehicletotransmission','transmission','mfr'],
            'TransmissionNumSpeedsID' => ['vehicletotransmission','transmission','transmissionbase','transmissionnumspeeds'],
            'TransmissionTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissiontype'],
            'ValvesPerEngineID' => ['vehicletoengineconfig','engineconfig','valves'],
            'WheelBaseID' => ['vehicletowheelbase','wheelbase']
        ];

        //Example questions (get the real one's off of John):
        //Accompanied by an optional graphic (like the one I sent in a Word document previously):

        $question_graphics = [
            'EngineVINID' => '/path/to/a/graphic',
        ];

        //We don't use position as it's a part specific qualifier not vehicle specific
        //Strings contains both single and double quotes so we use heredoc syntax.
        $vcdb_selects_per_qualifier['AspirationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',aspiration.AspirationID,'":{"Value":"',TRIM(aspiration.AspirationName),'","IsQualifierInDM":',IF(aspiration.AspirationID [CONDITION],'true','false'),'}') ORDER BY aspiration.AspirationName SEPARATOR ','),'}') AS AspirationID
        EOT;
        $vcdb_selects_per_qualifier['BedLengthID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedlength.BedLengthID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(bedlength.BedLength),'in'),CONCAT(TRIM(bedlength.BedLengthMetric),'cm')),'","IsQualifierInDM":',IF(bedlength.BedLengthID [CONDITION],'true','false'),'}') ORDER BY bedlength.BedLength,BedLengthMetric SEPARATOR ','),'}') AS BedLengthID
        EOT;

        $vcdb_selects_per_qualifier['BedTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedtype.BedTypeID,'":{"Value":"',TRIM(bedtype.BedTypeName),'","IsQualifierInDM":',IF(bedtype.BedTypeID [CONDITION],'true','false'),'}') ORDER BY bedtype.BedTypeName SEPARATOR ','),'}') AS BedTypeID
        EOT;

        $vcdb_selects_per_qualifier['BodyNumDoorsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodynumdoors.BodyNumDoorsID,'":{"Value":"',TRIM(bodynumdoors.BodyNumDoors),'","IsQualifierInDM":',IF(bodynumdoors.BodyNumDoorsID [CONDITION],'true','false'),'}') ORDER BY bodynumdoors.BodyNumDoors SEPARATOR ','),'}') AS BodyNumDoorsID
        EOT;
        $vcdb_selects_per_qualifier['BodyTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodytype.BodyTypeID,'":{"Value":"',TRIM(bodytype.BodyTypeName),'","IsQualifierInDM":',IF(bodytype.BodyTypeID [CONDITION],'true','false'),'}') ORDER BY bodytype.BodyTypeName SEPARATOR ','),'}') AS BodyTypeID
        EOT;
        $vcdb_selects_per_qualifier['BrakeABSID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakeabs.BrakeABSID,'":{"Value":"',TRIM(brakeabs.BrakeABSName),'","IsQualifierInDM":',IF(brakeabs.BrakeABSID [CONDITION],'true','false'),'}') ORDER BY brakeabs.BrakeABSName SEPARATOR ','),'}') AS BrakeABSID
        EOT;
        $vcdb_selects_per_qualifier['BrakeSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakesystem.BrakeSystemID,'":{"Value":"',TRIM(brakesystem.BrakeSystemName),'","IsQualifierInDM":',IF(brakesystem.BrakeSystemID [CONDITION],'true','false'),'}') ORDER BY brakesystem.BrakeSystemName SEPARATOR ','),'}') AS BrakeSystemID
        EOT;
        $vcdb_selects_per_qualifier['CylinderHeadTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',cylinderheadtype.CylinderHeadTypeID,'":{"Value":"',TRIM(cylinderheadtype.CylinderHeadTypeName),'","IsQualifierInDM":',IF(cylinderheadtype.CylinderHeadTypeID [CONDITION],'true','false'),'}') ORDER BY cylinderheadtype.CylinderHeadTypeName SEPARATOR ','),'}') AS CylinderHeadTypeID
        EOT;
        $vcdb_selects_per_qualifier['DriveTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',drivetype.DriveTypeID,'":{"Value":"',TRIM(drivetype.DriveTypeName),'","IsQualifierInDM":',IF(drivetype.DriveTypeID [CONDITION],'true','false'),'}') ORDER BY drivetype.DriveTypeName SEPARATOR ','),'}') AS DriveTypeID
        EOT;
        $vcdb_selects_per_qualifier['EngineDesignationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginedesignation.EngineDesignationID,'":{"Value":"',TRIM(enginedesignation.EngineDesignationName),'","IsQualifierInDM":',IF(enginedesignation.EngineDesignationID [CONDITION],'true','false'),'}') ORDER BY enginedesignation.EngineDesignationName SEPARATOR ','),'}') AS EngineDesignationID
        EOT;
        $vcdb_selects_per_qualifier['EngineMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS EngineMfrID
        EOT;
        $vcdb_selects_per_qualifier['EngineVersionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',engineversion.EngineVersionID,'":{"Value":"',TRIM(engineversion.EngineVersion),'","IsQualifierInDM":',IF(engineversion.EngineVersionID [CONDITION],'true','false'),'}') ORDER BY engineversion.EngineVersion SEPARATOR ','),'}') AS EngineVersionID
        EOT;
        $vcdb_selects_per_qualifier['EngineVINID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginevin.EngineVINID,'":{"Value":"',TRIM(enginevin.EngineVINName),'","IsQualifierInDM":',IF(enginevin.EngineVINID [CONDITION],'true','false'),'}') ORDER BY enginevin.EngineVINName SEPARATOR ','),'}') AS EngineVINID
        EOT;
        $vcdb_selects_per_qualifier['FrontBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_front.BrakeTypeID,'":{"Value":"',TRIM(braketype_front.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_front.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_front.BrakeTypeName SEPARATOR ','),'}') AS FrontBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['FrontSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_front.SpringTypeID,'":{"Value":"',TRIM(springtype_front.SpringTypeName),'","IsQualifierInDM":',IF(springtype_front.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_front.SpringTypeName SEPARATOR ','),'}') AS FrontSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliverySubTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverysubtype.FuelDeliverySubTypeID,'":{"Value":"',TRIM(fueldeliverysubtype.FuelDeliverySubTypeName),'","IsQualifierInDM":',IF(fueldeliverysubtype.FuelDeliverySubTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverysubtype.FuelDeliverySubTypeName SEPARATOR ','),'}') AS FuelDeliverySubTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliveryTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverytype.FuelDeliveryTypeID,'":{"Value":"',TRIM(fueldeliverytype.FuelDeliveryTypeName),'","IsQualifierInDM":',IF(fueldeliverytype.FuelDeliveryTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverytype.FuelDeliveryTypeName SEPARATOR ','),'}') AS FuelDeliveryTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemcontroltype.FuelSystemControlTypeID,'":{"Value":"',TRIM(fuelsystemcontroltype.FuelSystemControlTypeName),'","IsQualifierInDM":',IF(fuelsystemcontroltype.FuelSystemControlTypeID [CONDITION],'true','false'),'}') ORDER BY fuelsystemcontroltype.FuelSystemControlTypeName SEPARATOR ','),'}') AS FuelSystemControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemDesignID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemdesign.FuelSystemDesignID,'":{"Value":"',TRIM(fuelsystemdesign.FuelSystemDesignName),'","IsQualifierInDM":',IF(fuelsystemdesign.FuelSystemDesignID [CONDITION],'true','false'),'}') ORDER BY fuelsystemdesign.FuelSystemDesignName SEPARATOR ','),'}') AS FuelSystemDesignID
        EOT;
        $vcdb_selects_per_qualifier['FuelTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueltype.FuelTypeID,'":{"Value":"',TRIM(fueltype.FuelTypeName),'","IsQualifierInDM":',IF(fueltype.FuelTypeID [CONDITION],'true','false'),'}') ORDER BY fueltype.FuelTypeName SEPARATOR ','),'}') AS FuelTypeID
        EOT;
        $vcdb_selects_per_qualifier['IgnitionSystemTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',ignitionsystemtype.IgnitionSystemTypeID,'":{"Value":"',TRIM(ignitionsystemtype.IgnitionSystemTypeName),'","IsQualifierInDM":',IF(ignitionsystemtype.IgnitionSystemTypeID [CONDITION],'true','false'),'}') ORDER BY ignitionsystemtype.IgnitionSystemTypeName SEPARATOR ','),'}') AS IgnitionSystemTypeID
        EOT;
        $vcdb_selects_per_qualifier['MfrBodyCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfrbodycode.MfrBodyCodeID,'":{"Value":"',TRIM(mfrbodycode.MfrBodyCodeName),'","IsQualifierInDM":',IF(mfrbodycode.MfrBodyCodeID [CONDITION],'true','false'),'}') ORDER BY mfrbodycode.MfrBodyCodeName SEPARATOR ','),'}') AS MfrBodyCodeID
        EOT;
        $vcdb_selects_per_qualifier['PositionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',positions.PositionID,'":{"Value":"',TRIM(positions.Position),'","IsQualifierInDM":',IF(positions.PositionID [CONDITION],'true','false'),'}') ORDER BY positions.Position SEPARATOR ','),'}') AS PositionID
        EOT;
        $vcdb_selects_per_qualifier['PowerOutputID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',poweroutput.PowerOutputID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(poweroutput.HorserPower),'HP'),CONCAT(TRIM(poweroutput.KilowattPower),'kW')),'","IsQualifierInDM":',IF(poweroutput.PowerOutputID [CONDITION],'true','false'),'}') ORDER BY poweroutput.HorsePower,KilowattPower SEPARATOR ','),'}') AS PowerOutputID
        EOT;
        $vcdb_selects_per_qualifier['RearBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_rear.BrakeTypeID,'":{"Value":"',TRIM(braketype_rear.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_rear.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_rear.BrakeTypeName SEPARATOR ','),'}') AS RearBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['RearSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_rear.SpringTypeID,'":{"Value":"',TRIM(springtype_rear.SpringTypeName),'","IsQualifierInDM":',IF(springtype_rear.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_rear.SpringTypeName SEPARATOR ','),'}') AS RearSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['RegionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',region.RegionID,'":{"Value":"',TRIM(region.RegionName),'","IsQualifierInDM":',IF(region.RegionID [CONDITION],'true','false'),'}') ORDER BY region.RegionAbbr,RegionName SEPARATOR ','),'}') AS RegionID
        EOT;
        $vcdb_selects_per_qualifier['SteeringSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringsystem.SteeringSystemID,'":{"Value":"',TRIM(steeringsystem.SteeringSystemName),'","IsQualifierInDM":',IF(steeringsystem.SteeringSystemID [CONDITION],'true','false'),'}') ORDER BY steeringsystem.SteeringSystemName SEPARATOR ','),'}') AS SteeringSystemID
        EOT;
        $vcdb_selects_per_qualifier['SteeringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringtype.SteeringTypeID,'":{"Value":"',TRIM(steeringtype.SteeringTypeName),'","IsQualifierInDM":',IF(steeringtype.SteeringTypeID [CONDITION],'true','false'),'}') ORDER BY steeringtype.SteeringTypeName SEPARATOR ','),'}') AS SteeringTypeID
        EOT;
        $vcdb_selects_per_qualifier['SubModelID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',submodel.SubModelID,'":{"Value":"',TRIM(submodel.SubModelName),'","IsQualifierInDM":',IF(submodel.SubModelID [CONDITION],'true','false'),'}') ORDER BY submodel.SubModelName SEPARATOR ','),'}') AS SubModelID
        EOT;
        $vcdb_selects_per_qualifier['TransElecControlledID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',eleccontrolled.ElecControlledID,'":{"Value":"',TRIM(eleccontrolled.ElecControlled),'","IsQualifierInDM":',IF(eleccontrolled.ElecControlledID [CONDITION],'true','false'),'}') ORDER BY eleccontrolled.ElecControlled SEPARATOR ','),'}') AS TransElecControlledID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissioncontroltype.TransmissionControlTypeID,'":{"Value":"',TRIM(transmissioncontroltype.TransmissionControlTypeName),'","IsQualifierInDM":',IF(transmissioncontroltype.TransmissionControlTypeID [CONDITION],'true','false'),'}') ORDER BY transmissioncontroltype.TransmissionControlTypeName SEPARATOR ','),'}') AS TransmissionControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionmfrcode.TransmissionMfrCodeID,'":{"Value":"',TRIM(transmissionmfrcode.TransmissionMfrCode),'","IsQualifierInDM":',IF(transmissionmfrcode.TransmissionMfrCodeID [CONDITION],'true','false'),'}') ORDER BY transmissionmfrcode.TransmissionMfrCode SEPARATOR ','),'}') AS TransmissionMfrCodeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr1.MfrID,'":{"Value":"',TRIM(mfr1.MfrName),'","IsQualifierInDM":',IF(mfr1.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr1.MfrName SEPARATOR ','),'}') AS TransmissionMfrID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionNumSpeedsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionnumspeeds.TransmissionNumSpeedsID,'":{"Value":"',TRIM(transmissionnumspeeds.TransmissionNumSpeeds),'","IsQualifierInDM":',IF(transmissionnumspeeds.TransmissionNumSpeedsID [CONDITION],'true','false'),'}') ORDER BY transmissionnumspeeds.TransmissionNumSpeeds SEPARATOR ','),'}') AS TransmissionNumSpeedsID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissiontype.TransmissionTypeID,'":{"Value":"',TRIM(transmissiontype.TransmissionTypeName),'","IsQualifierInDM":',IF(transmissiontype.TransmissionTypeID [CONDITION],'true','false'),'}') ORDER BY transmissiontype.TransmissionTypeName SEPARATOR ','),'}') AS TransmissionTypeID
        EOT;
        $vcdb_selects_per_qualifier['ValvesPerEngineID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',valves.ValvesID,'":{"Value":"',TRIM(valves.ValvesPerEngine),'","IsQualifierInDM":',IF(valves.ValvesID [CONDITION],'true','false'),'}') ORDER BY valves.ValvesPerEngine SEPARATOR ','),'}') AS ValvesPerEngineID
        EOT;
        $vcdb_selects_per_qualifier['WheelBaseID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',wheelbase.WheelBaseID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(wheelbase.WheelBase),'in'),CONCAT(TRIM(wheelbase.WheelBaseMetric),'cm')),'","IsQualifierInDM":',IF(wheelbase.WheelBaseID [CONDITION],'true','false'),'}') ORDER BY wheelbase.WheelBase,WheelBaseMetric SEPARATOR ','),'}') AS WheelBaseID
        EOT;


        $vehicle_definition = [
            'VehicleID' => 0,
            'BaseVehicleID' => 0,
            'YearID' => 0,
            'MakeID' => 0,
            'ModelID' => 0,
            'EngineConfigID' => 0,
            'EngineBaseID' => 0,
            
            /*The above are vehicle identifiers - typically a VehicleID or BaseVehicleID will be given
              or year, make, model (for which the basevehicle table will be needed)
              and one of EngineConfigID or EngineBaseID is needed.
              If the API's XML only returns values then more tables, e.g. make, model, will be needed.
             */    
              'AspirationID' => "Aspiration",
              'BedLengthID' => "Bed Length",
              'BedTypeID' => "Bed Type",
              'BodyNumDoorsID' => "Number of Doors",
              'BodyTypeID' => "Body Type",
              'BrakeABSID' => "ABS Type",
              'BrakeSystemID' => "Brake System",
              'CylinderHeadTypeID' => "Cylinder Head",
              'DriveTypeID' => "Drive Type",
              'EngineDesignationID' => "Engine Design",
              'EngineMfrID' => "Engine Mfr",
              'EngineVersionID' => "Engine Version",
              'EngineVINID' => "VIN",
              'FrontBrakeTypeID' => "Front Brake",
              'FrontSpringTypeID' => "Front Spring",
              'FuelDeliverySubTypeID' => "Fuel Del. Sub Type",
              'FuelDeliveryTypeID' => "Fuel Del. Type",
              'FuelSystemControlTypeID' => "Fuel Sys. Control",
              'FuelSystemDesignID' => "Fuel Sys. Design",
              'FuelTypeID' => "Fuel",
              'IgnitionSystemTypeID' => "Ignition",
              'MfrBodyCodeID' => "Body Code",
              'PositionID' => 0,
              'PowerOutputID' => "Power Output",
              'RearBrakeTypeID' => "Rear Brake",
              'RearSpringTypeID' => "Rear Spring",
              'RegionID' => "Region",
              'SteeringSystemID' => "Steering System",
              'SteeringTypeID' => "Steering",
              'SubModelID' => "SubModel",
              'TransElecControlledID' => "Trans Elec Ctrl",
              'TransmissionControlTypeID' => "Trans Ctrl Type",
              'TransmissionMfrCodeID' => "Trans Mfr Code",
              'TransmissionMfrID' => "Transmission Mfr",
              'TransmissionNumSpeedsID' => "Trans Num Speeds",
              'TransmissionTypeID' => "Trans Type",
              'ValvesPerEngineID' => "Valves Per Engine",
              'WheelBaseID' => "Wheel Base",
        
        ];

        $save_fluis_pt_string=implode(",",$save_fluid_pt);
        $save_normal_pt_string=implode(",",$save_normal_pt);

        
        if($save_fluis_pt_string!="" && ($save_normal_pt_string!="" || $save_normal_pt_string=="")) {
            $data_qualifiers_select = <<<EOT
            SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelTypeID,0) ORDER BY IFNULL(af.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.AspirationID,0) ORDER BY IFNULL(af.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.DriveTypeID,0) ORDER BY IFNULL(af.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineVINID,0) ORDER BY IFNULL(af.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineDesignationID,0) ORDER BY IFNULL(af.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.CylinderHeadTypeID,0) ORDER BY IFNULL(af.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SubModelID,0) ORDER BY IFNULL(af.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionControlTypeID,0) ORDER BY IFNULL(af.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionNumSpeedsID,0) ORDER BY IFNULL(af.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.ValvesPerEngineID,0) ORDER BY IFNULL(af.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelDeliveryTypeID,0) ORDER BY IFNULL(af.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BedTypeID,0) ORDER BY IFNULL(af.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BodyNumDoorsID,0) ORDER BY IFNULL(af.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineMfrID,0) ORDER BY IFNULL(af.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelDeliverySubTypeID,0) ORDER BY IFNULL(af.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelSystemControlTypeID,0) ORDER BY IFNULL(af.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FuelSystemDesignID,0) ORDER BY IFNULL(af.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SteeringTypeID,0) ORDER BY IFNULL(af.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionTypeID,0) ORDER BY IFNULL(af.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BedLengthID,0) ORDER BY IFNULL(af.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BodyTypeID,0) ORDER BY IFNULL(af.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BrakeABSID,0) ORDER BY IFNULL(af.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.BrakeSystemID,0) ORDER BY IFNULL(af.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.EngineVersionID,0) ORDER BY IFNULL(af.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FrontBrakeTypeID,0) ORDER BY IFNULL(af.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.FrontSpringTypeID,0) ORDER BY IFNULL(af.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.IgnitionSystemTypeID,0) ORDER BY IFNULL(af.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.MfrBodyCodeID,0) ORDER BY IFNULL(af.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.PowerOutputID,0) ORDER BY IFNULL(af.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RearBrakeTypeID,0) ORDER BY IFNULL(af.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RearSpringTypeID,0) ORDER BY IFNULL(af.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.RegionID,0) ORDER BY IFNULL(af.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.SteeringSystemID,0) ORDER BY IFNULL(af.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransElecControlledID,0) ORDER BY IFNULL(af.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionMfrID,0) ORDER BY IFNULL(af.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.TransmissionMfrCodeID,0) ORDER BY IFNULL(af.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(af.WheelBaseID,0) ORDER BY IFNULL(af.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),',',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
            EOT;
        }
        else {
            $data_qualifiers_select = <<<EOT
            SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
            EOT;
        }

        if(in_array("11",$explode_part) || in_array("15",$explode_part) || in_array("17",$explode_part)) {

            if($save_fluis_pt_string!="" && $save_normal_pt_string!="") { // both need more info clicked (for fluids and caps/wiper/filters) 
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                //             WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16".','.$save_normal_pt_string.") OR a.partSubtype IN(1,2,3,4)) AND af.partTypeid IN (".$save_fluis_pt_string.")");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                            WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832".','.$save_normal_pt_string.") AND af.partTypeid IN (".$save_fluis_pt_string.")");

            }
            elseif($save_fluis_pt_string!="" && $save_normal_pt_string=="") { // only first fluids need more info clicked
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16) OR a.partSubtype IN(1,2,3,4)) AND af.partTypeid IN (".$save_fluis_pt_string.")");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832) AND af.partTypeid IN (".$save_fluis_pt_string.")");
            }
            else { // only first caps/wiper/filters need more info clicked
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a  JOIN partNumberInfo pi ON a.partnumber=pi.partNumber  
                // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND (pi.partType IN(1,2,3,7,12,13,16".','.$save_normal_pt_string.") OR a.partSubtype IN(1,2,3,4))");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a  JOIN partNumberInfo pi ON a.part_id=pi.part_id  
                WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND pi.PartTerminologyID IN(5340,6192,6832".','.$save_normal_pt_string.")");
            }
        }
        else {

            if($parttype!="") {
        
                if($save_fluis_pt_string!="" && $save_normal_pt_string!="" ) { // both need more info clicked (for fluids and caps/wiper/filters)

                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    // WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.partTypeid IN (1,2,3,7,12,13,16".','.$save_normal_pt_string.")");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.PartTerminologyID IN (5340,6192,6832".','.$save_normal_pt_string.")");
                }
                elseif($save_fluis_pt_string!="" && $save_normal_pt_string=="") {
                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    // WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.partTypeid IN (1,2,3,7,12,13,16)");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID JOIN applications3 af ON a.BaseVehicleID=af.BaseVehicleID AND a.EngineBaseID=af.EngineBaseID 
                    WHERE af.BaseVehicleID=$baseVehicleId AND af.EngineBaseID IN($engineId) AND af.partTypeid IN (".$save_fluis_pt_string.") AND pm.PartTerminologyID IN (5340,6192,6832)");
                }
                else { // only first caps/wiper/filters need more info clicked
                    // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid  
                    // WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.partTypeid IN (1,2,3,7,12,13,16".','.$save_normal_pt_string.")");
                    $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID  
                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.PartTerminologyID IN (5340,6192,6832".','.$save_normal_pt_string.")");
                }
        
            }
            else  {
                // $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.partnumber=pi.partNumber join parttype_master pm on pi.partType=pm.partTypeid  WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.partTypeid IN (1,2,3,7,12,13,16)");
                $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id join parttype_master pm on pi.PartTerminologyID=pm.PartTerminologyID  WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND  pm.PartTerminologyID IN (5340,6192,6832)");   
            }
        }


        $a=$results[0];
        $get_data_json=$a->data_json;

        $f="";


        if($get_data_json==null) {
            $f="fail";
            return json_encode($f);
        }
        else {
            $vcdb_tables = [];
            $vcdb_selects = [];
            $replace_count = 1;
            $result=[];

            $get_data_json=json_decode($get_data_json,true);

            foreach ($get_data_json as $qualifier_type => $qualifier_ids)  {
                if($qualifier_type != 'PositionID') {

                    if($qualifier_ids!=NULL) {
                        $result = array_diff($qualifier_ids, [0]);
                    }

                    if(count($result) >= 1 || $qualifier_ids[0] != 0) {

                        if(!empty($vehicle_definition[$qualifier_type])) {

                            $vcdb_tables = array_merge($vcdb_tables,$vcdb_tables_per_qualifier[$qualifier_type]);
                            $original_select = $vcdb_selects_per_qualifier[$qualifier_type];

                            if($qualifier_ids[0] == 0) {
                                //Any criteria will do here so as IDs can't be negative this will always return true
                                $final_select = str_replace('[CONDITION]','!= -1000',$original_select,$replace_count);

                            }
                            else {
                                $final_select = str_replace('[CONDITION]',('IN(' . implode(',', $qualifier_ids) . ')'),$original_select,$replace_count);
                            }
                            $vcdb_selects[] = $final_select;

                        }
                    }

                }
            }//foreach

            if(count($vcdb_tables) == 0) {
                $f="fail";
                return json_encode($f);
            }
            else {
                $vcdb_tables[] = 'vehicletoengineconfig';
                $vcdb_tables[] = 'engineconfig';

                $vcdb_tables_unique = array_unique($vcdb_tables);
                $vcdb_qualifiers_select = 'SELECT ' . implode(', ',$vcdb_selects) . ' FROM vehicle';

                foreach($vcdb_tables_unique as $table) {
                    $vcdb_qualifiers_select = $vcdb_qualifiers_select . ' ' . $vcdb_table_joins[$table];
                }

                $vcdb_qualifiers_select= DB::connection(self::$aces_vcdb)->select($vcdb_qualifiers_select . ' WHERE vehicle.BaseVehicleID ='.$baseVehicleId.'  AND engineconfig.EngineBaseID IN('.$engineId.')'.$qualifierlist);
                  
            }

            if(empty($vcdb_qualifiers_select)) {
                $f="fail";
                return json_encode($f);
            }
            else {

                $get_value_of_q["values"]=[];
                $get_value_of_q["name"]=[];
                $qualifier_name="";

                for($i=0;$i<1;$i++) {
                    foreach($vcdb_qualifiers_select[$i] as $qualifier_type => $qualifier_type_json) {

                        $stop_process=0;
                        $matched_value=0;
                        foreach($store_upcoming as $store_upcoming1) {

                            $ex=explode(":",$store_upcoming1);
                            if($ex[0]==$qualifier_type) {
                                $matched_value=1;
                            }
                        }

                        if($matched_value==0) {
                            $qualifier_type_json_array = json_decode($qualifier_type_json,true);
                            $qualifier_name=$vehicle_definition[$qualifier_type];
                            array_push($get_value_of_q["name"],$qualifier_name);

                            foreach($qualifier_type_json_array as $qualifier_id => $qualifier_detail) {
                                $header="";
                                $header='{"id":"'.$qualifier_type.":".$qualifier_id.'","values":"'.$qualifier_detail['Value'].'"}';
                                $header=json_decode($header);
                                array_push($get_value_of_q["values"],$header);
                            }

                        }
                        else {
                            continue;
                        }

                        if($stop_process==0) {
                            break;
                        }

                    }

                    return $get_value_of_q;
                }
            }

        }

    }
    


    //qualifierCollectionPureFlow
    public function qualifiercheckinengine(Request $request,$baseVehicleId,$engineId,$qualifierSelected,$parttype,$partsubtype) {

        $qualifier_main_table=[
            'AspirationID' => 'aspiration.AspirationID',
            'BedLengthID' => 'bedlength.BedLengthID',
            'BedTypeID' => 'bedtype.BedTypeID',
            'BodyNumDoorsID' => 'bodynumdoors.BodyNumDoorsID',
            'BodyTypeID' => 'bodytype.BodyTypeID',
            'BrakeABSID' => 'brakeabs.BrakeABSID',
            'BrakeSystemID' => 'brakesystem.BrakeSystemID',
            'CylinderHeadTypeID' => 'cylinderheadtype.CylinderHeadTypeID',
            'DriveTypeID' => 'drivetype.DriveTypeID',
            'EngineDesignationID' => 'enginedesignation.EngineDesignationID',
            'EngineMfrID' => 'mfr.MfrID',
            'EngineVersionID' => 'engineversion.EngineVersionID',
            'EngineVINID' => 'enginevin.EngineVINID',
            'FrontBrakeTypeID' => 'braketype_front.BrakeTypeID',
            'FrontSpringTypeID' => 'springtype_front.SpringTypeID',
            'FuelDeliverySubTypeID' => 'fueldeliverysubtype.FuelDeliverySubTypeID',
            'FuelDeliveryTypeID' => 'fueldeliverytype.FuelDeliveryTypeID',
            'FuelSystemControlTypeID' => 'fuelsystemcontroltype.FuelSystemControlTypeID',
            'FuelSystemDesignID' => 'fuelsystemdesign.FuelSystemDesignID',
            'FuelTypeID' => 'fueltype.FuelTypeID',
            'IgnitionSystemTypeID' => 'ignitionsystemtype.IgnitionSystemTypeID',
            'MfrBodyCodeID' => 'mfrbodycode.MfrBodyCodeID',
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => 'poweroutput.PowerOutputID',
            'RearBrakeTypeID' => 'braketype_rear.BrakeTypeID',
            'RearSpringTypeID' => 'springtype_rear.SpringTypeID',
            'RegionID' => 'region.RegionID',
            'SteeringSystemID' => 'steeringsystem.SteeringSystemID',
            'SteeringTypeID' => 'steeringtype.SteeringTypeID',
            'SubModelID' => 'submodel.SubModelID',
            'TransElecControlledID' => 'eleccontrolled.ElecControlledID',
            'TransmissionControlTypeID' => 'transmissioncontroltype.TransmissionControlTypeID',
            'TransmissionMfrCodeID' => 'transmissionmfrcode.TransmissionMfrCodeID',
            'TransmissionMfrID' => 'mfr.MfrID',
            'TransmissionNumSpeedsID' => 'transmissionnumspeeds.TransmissionNumSpeedsID',
            'TransmissionTypeID' => 'transmissiontype.TransmissionTypeID',
            'ValvesPerEngineID' => 'valves.ValvesID',
            'WheelBaseID' => 'wheelbase.WheelBaseID'
        ];

        
        $store_upcoming=[];
        
        $explode_part=explode(",",$parttype);
        $store_upcoming=explode(",",$qualifierSelected);
        
        $qualifierlist="";
        if($store_upcoming[0]!="noqualifier") {
            if(count($store_upcoming)>1) {
                foreach ($store_upcoming as $value) {
                    $qual=explode(":",$value);
                    $where_clause[]=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                    $prevqualifier[]=$qual[0];
                }
                $qualifierlist=implode(" ",$where_clause);
            }
            else {
                $qual=explode(":",$qualifierSelected);
                $qualifierlist=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                $prevqualifier[]=$qual[0];
            }
        }

        $fueltype_table = 'fueltype';


      
        $send_fluid_pt=[];
        $send_normal_pt='';
        $save_normal_pt=[];
                
                
        for($i=0;$i<count($explode_part);$i++) {
                
            $send_normal_pt=$explode_part[$i];
            array_push($save_normal_pt,$send_normal_pt);
                
        }
        

        //We use left joins in case the VCDB isn't fully populated

        $vcdb_table_joins = [
            'basevehicle' => 'LEFT JOIN basevehicle ON vehicle.BaseVehicleID = basevehicle.BaseVehicleID',
            'region' => 'LEFT JOIN region ON vehicle.RegionID = region.RegionID',
            'submodel' => 'LEFT JOIN submodel ON vehicle.SubmodelID = submodel.SubModelID',
            'vehicletospringtypeconfig' => 'LEFT JOIN vehicletospringtypeconfig ON vehicle.VehicleID = vehicletospringtypeconfig.VehicleID',
            'springtype_front' => 'LEFT JOIN springtypeconfig ON vehicletospringtypeconfig.SpringTypeConfigID = springtypeconfig.SpringTypeConfigID',
            'springtype' => 'LEFT JOIN springtype springtype_front ON springtypeconfig.FrontSpringTypeID = springtype_front.SpringTypeID',
            'springtype_rear' => 'LEFT JOIN springtype springtype_rear ON springtypeconfig.RearSpringTypeID = springtype_rear.SpringTypeID',
            'vehicletosteeringconfig' => 'LEFT JOIN vehicletosteeringconfig ON vehicle.VehicleID = vehicletosteeringconfig.VehicleID',
            'steeringconfig' => 'LEFT JOIN steeringconfig ON vehicletosteeringconfig.SteeringConfigID = steeringconfig.SteeringConfigID',
            'steeringtype' => 'LEFT JOIN steeringtype ON steeringconfig.SteeringTypeID = steeringtype.SteeringTypeID',
            'steeringsystem' => 'LEFT JOIN steeringsystem ON steeringconfig.SteeringSystemID = steeringsystem.SteeringSystemID',
            'vehicletobedconfig' => 'LEFT JOIN vehicletobedconfig ON vehicle.VehicleID = vehicletobedconfig.VehicleID',
            'bedconfig' => 'LEFT JOIN bedconfig ON vehicletobedconfig.BedConfigID = bedconfig.BedConfigID',
            'bedtype' => 'LEFT JOIN bedtype ON bedconfig.BedTypeID = bedtype.BedTypeID',
            'bedlength' => 'LEFT JOIN bedlength ON bedconfig.BedLengthID = bedlength.BedLengthID',
            'vehicletobrakeconfig' => 'LEFT JOIN vehicletobrakeconfig ON vehicle.VehicleID = vehicletobrakeconfig.VehicleID',
            'braketype_front' => 'LEFT JOIN brakeconfig ON vehicletobrakeconfig.BrakeConfigID = brakeconfig.BrakeConfigID',
            'braketype' => 'LEFT JOIN braketype braketype_front ON brakeconfig.FrontBrakeTypeID = braketype_front.BrakeTypeID',
            'braketype_rear' => 'LEFT JOIN braketype braketype_rear ON brakeconfig.RearBrakeTypeID = braketype_rear.BrakeTypeID',
            'brakeabs' => 'LEFT JOIN brakeabs ON brakeconfig.BrakeABSID = brakeabs.BrakeABSID',
            'brakesystem' => 'LEFT JOIN brakesystem ON brakeconfig.BrakeSystemID = brakesystem.BrakeSystemID',
            'vehicletodrivetype' => 'LEFT JOIN vehicletodrivetype ON vehicle.VehicleID = vehicletodrivetype.VehicleID',
            'drivetype' => 'LEFT JOIN drivetype ON vehicletodrivetype.DriveTypeID = drivetype.DriveTypeID',
            'vehicletowheelbase' => 'LEFT JOIN vehicletowheelbase ON vehicle.VehicleID = vehicletowheelbase.VehicleID',
            'wheelbase' => 'LEFT JOIN wheelbase ON vehicletowheelbase.WheelbaseID = wheelbase.WheelBaseID',
            'vehicletobodystyleconfig' => 'LEFT JOIN vehicletobodystyleconfig ON vehicle.VehicleID = vehicletobodystyleconfig.VehicleID',
            'bodystyleconfig' => 'LEFT JOIN bodystyleconfig ON vehicletobodystyleconfig.BodyStyleConfigID = bodystyleconfig.BodyStyleConfigID',
            'bodynumdoors' => 'LEFT JOIN bodynumdoors ON bodystyleconfig.BodyNumDoorsID = bodynumdoors.BodyNumDoorsID',
            'bodytype' => 'LEFT JOIN bodytype ON bodystyleconfig.BodyTypeID = bodytype.BodyTypeID',
            'vehicletomfrbodycode' => 'LEFT JOIN vehicletomfrbodycode ON vehicle.VehicleID = vehicletomfrbodycode.VehicleID',
            'mfrbodycode' => 'LEFT JOIN mfrbodycode ON vehicletomfrbodycode.MfrBodyCodeID = mfrbodycode.MfrBodyCodeID',
            'vehicletotransmission' => 'LEFT JOIN vehicletotransmission ON vehicle.VehicleID = vehicletotransmission.VehicleID',
            'transmission' => 'LEFT JOIN transmission ON vehicletotransmission.TransmissionID = transmission.TransmissionID',
            'eleccontrolled' => 'LEFT JOIN eleccontrolled ON transmission.TransmissionElecControlledID = eleccontrolled.ElecControlledID',
            'transmissionbase' => 'LEFT JOIN transmissionbase ON transmission.TransmissionBaseID = transmissionbase.TransmissionBaseID',
            'transmissiontype' => 'LEFT JOIN transmissiontype ON transmissionbase.TransmissionTypeID = transmissiontype.TransmissionTypeID',
            'transmissioncontroltype' => 'LEFT JOIN transmissioncontroltype ON transmissionbase.TransmissionControlTypeID = transmissioncontroltype.TransmissionControlTypeID',
            'transmissionnumspeeds' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr_transmission' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr' => 'LEFT JOIN mfr ON transmission.TransmissionMfrID = mfr.MfrID',
            'transmissionmfrcode' => 'LEFT JOIN transmissionmfrcode ON transmission.TransmissionMfrCodeID = transmissionmfrcode.TransmissionMfrCodeID',
            'vehicletoengineconfig' => 'LEFT JOIN vehicletoengineconfig ON vehicle.VehicleID = vehicletoengineconfig.VehicleID',
            'engineconfig' => 'LEFT JOIN engineconfig ON vehicletoengineconfig.EngineConfigID = engineconfig.EngineConfigID',
            'ignitionsystemtype' => 'LEFT JOIN ignitionsystemtype ON engineconfig.IgnitionSystemTypeID = ignitionsystemtype.IgnitionSystemTypeID',
            'cylinderheadtype' => 'LEFT JOIN cylinderheadtype ON engineconfig.CylinderHeadTypeID = cylinderheadtype.CylinderHeadTypeID',
            'aspiration' => 'LEFT JOIN aspiration ON engineconfig.AspirationID = aspiration.AspirationID',
            'fueltype' => "LEFT JOIN $fueltype_table ON engineconfig.FuelTypeID = fueltype.FuelTypeID",
            'valves' => 'LEFT JOIN valves ON engineconfig.ValvesID = valves.ValvesID',
            'enginevin' => 'LEFT JOIN enginevin ON engineconfig.EngineVINID = enginevin.EngineVINID',
            'mfr_engine' => 'LEFT JOIN engineversion ON engineconfig.EngineVersionID = engineversion.EngineVersionID',
            'engine_mfr' => 'LEFT JOIN mfr  ON engineconfig.EngineMfrID = mfr.MfrID',
            'fueldeliveryconfig' => 'LEFT JOIN fueldeliveryconfig ON engineconfig.FuelDeliveryConfigID = fueldeliveryconfig.FuelDeliveryConfigID',
            'fueldeliverytype' => 'LEFT JOIN fueldeliverytype ON fueldeliveryconfig.FuelDeliveryTypeID = fueldeliverytype.FuelDeliveryTypeID',
            'fuelsystemcontroltype' => 'LEFT JOIN fuelsystemcontroltype ON fueldeliveryconfig.FuelSystemControlTypeID = fuelsystemcontroltype.FuelSystemControlTypeID',
            'fueldeliverysubtype' => 'LEFT JOIN fueldeliverysubtype ON fueldeliveryconfig.FuelDeliverySubTypeID = fueldeliverysubtype.FuelDeliverySubTypeID',
            'fuelsystemdesign' => 'LEFT JOIN fuelsystemdesign ON fueldeliveryconfig.FuelSystemDesignID = fuelsystemdesign.FuelSystemDesignID',
            'enginedesignation' => 'LEFT JOIN enginedesignation ON engineconfig.EngineDesignationID = enginedesignation.EngineDesignationID',
            'enginebase' => 'LEFT JOIN enginebase ON engineconfig.EngineBaseID = enginebase.EngineBaseID'
            
        ];


        $vcdb_tables_per_qualifier = [
            'AspirationID' => ['vehicletoengineconfig','engineconfig','aspiration'],
            'BedLengthID' => ['vehicletobedconfig','bedconfig','bedlength'],
            'BedTypeID' => ['vehicletobedconfig','bedconfig','bedtype'],
            'BodyNumDoorsID' => ['vehicletobodystyleconfig','bodystyleconfig','bodynumdoors'],
            'BodyTypeID' => ['vehicletobodystyleconfig','bodystyleconfig','bodytype'],
            'BrakeABSID' => ['vehicletobrakeconfig','braketype_front','brakeabs'],
            'BrakeSystemID' => ['vehicletobrakeconfig','braketype_front','brakesystem'],
            'CylinderHeadTypeID' => ['vehicletoengineconfig','engineconfig','cylinderheadtype'],
            'DriveTypeID' => ['vehicletodrivetype','drivetype'],
            'EngineDesignationID' => ['vehicletoengineconfig','engineconfig','enginedesignation'],
            'EngineMfrID' => ['vehicletoengineconfig','engineconfig','engine_mfr'],//actual table name is mfr only
            'EngineVersionID' => ['vehicletoengineconfig','engineconfig','mfr_engine'],
            'EngineVINID' => ['vehicletoengineconfig','engineconfig','enginevin'],
            'FrontBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype'],
            'FrontSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype'],
            'FuelDeliverySubTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverysubtype'],
            'FuelDeliveryTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverytype'],
            'FuelSystemControlTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemcontroltype'],
            'FuelSystemDesignID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemdesign'],
            'FuelTypeID' => ['vehicletoengineconfig','engineconfig','fueltype'],
            'IgnitionSystemTypeID' => ['vehicletoengineconfig','engineconfig','ignitionsystemtype'],
            'MfrBodyCodeID' => ['vehicletomfrbodycode','mfrbodycode'],
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => ['vehicletoengineconfig','engineconfig','poweroutput'],
            'RearBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype_rear'],
            'RearSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype_rear'],
            'RegionID' => ['region'],
            'SteeringSystemID' => ['vehicletosteeringconfig','steeringconfig','steeringsystem'],
            'SteeringTypeID' => ['vehicletosteeringconfig','steeringconfig','steeringtype'],
            'SubModelID' => ['submodel'],
            'TransElecControlledID' => ['vehicletotransmission','transmission','eleccontrolled'],
            'TransmissionControlTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissioncontroltype'],
            'TransmissionMfrCodeID' => ['vehicletotransmission','transmission','transmissionmfrcode'],
            'TransmissionMfrID' => ['vehicletotransmission','transmission','mfr'],
            'TransmissionNumSpeedsID' => ['vehicletotransmission','transmission','transmissionbase','transmissionnumspeeds'],
            'TransmissionTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissiontype'],
            'ValvesPerEngineID' => ['vehicletoengineconfig','engineconfig','valves'],
            'WheelBaseID' => ['vehicletowheelbase','wheelbase']
        ];

        //Example questions (get the real one's off of John):
        //Accompanied by an optional graphic (like the one I sent in a Word document previously):

        $question_graphics = [
            'EngineVINID' => '/path/to/a/graphic',
        ];

        //We don't use position as it's a part specific qualifier not vehicle specific
        //Strings contains both single and double quotes so we use heredoc syntax.
        $vcdb_selects_per_qualifier['AspirationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',aspiration.AspirationID,'":{"Value":"',TRIM(aspiration.AspirationName),'","IsQualifierInDM":',IF(aspiration.AspirationID [CONDITION],'true','false'),'}') ORDER BY aspiration.AspirationName SEPARATOR ','),'}') AS AspirationID
        EOT;
        $vcdb_selects_per_qualifier['BedLengthID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedlength.BedLengthID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(bedlength.BedLength),'in'),CONCAT(TRIM(bedlength.BedLengthMetric),'cm')),'","IsQualifierInDM":',IF(bedlength.BedLengthID [CONDITION],'true','false'),'}') ORDER BY bedlength.BedLength,BedLengthMetric SEPARATOR ','),'}') AS BedLengthID
        EOT;

        $vcdb_selects_per_qualifier['BedTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedtype.BedTypeID,'":{"Value":"',TRIM(bedtype.BedTypeName),'","IsQualifierInDM":',IF(bedtype.BedTypeID [CONDITION],'true','false'),'}') ORDER BY bedtype.BedTypeName SEPARATOR ','),'}') AS BedTypeID
        EOT;

        $vcdb_selects_per_qualifier['BodyNumDoorsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodynumdoors.BodyNumDoorsID,'":{"Value":"',TRIM(bodynumdoors.BodyNumDoors),'","IsQualifierInDM":',IF(bodynumdoors.BodyNumDoorsID [CONDITION],'true','false'),'}') ORDER BY bodynumdoors.BodyNumDoors SEPARATOR ','),'}') AS BodyNumDoorsID
        EOT;
        $vcdb_selects_per_qualifier['BodyTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodytype.BodyTypeID,'":{"Value":"',TRIM(bodytype.BodyTypeName),'","IsQualifierInDM":',IF(bodytype.BodyTypeID [CONDITION],'true','false'),'}') ORDER BY bodytype.BodyTypeName SEPARATOR ','),'}') AS BodyTypeID
        EOT;
        $vcdb_selects_per_qualifier['BrakeABSID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakeabs.BrakeABSID,'":{"Value":"',TRIM(brakeabs.BrakeABSName),'","IsQualifierInDM":',IF(brakeabs.BrakeABSID [CONDITION],'true','false'),'}') ORDER BY brakeabs.BrakeABSName SEPARATOR ','),'}') AS BrakeABSID
        EOT;
        $vcdb_selects_per_qualifier['BrakeSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakesystem.BrakeSystemID,'":{"Value":"',TRIM(brakesystem.BrakeSystemName),'","IsQualifierInDM":',IF(brakesystem.BrakeSystemID [CONDITION],'true','false'),'}') ORDER BY brakesystem.BrakeSystemName SEPARATOR ','),'}') AS BrakeSystemID
        EOT;
        $vcdb_selects_per_qualifier['CylinderHeadTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',cylinderheadtype.CylinderHeadTypeID,'":{"Value":"',TRIM(cylinderheadtype.CylinderHeadTypeName),'","IsQualifierInDM":',IF(cylinderheadtype.CylinderHeadTypeID [CONDITION],'true','false'),'}') ORDER BY cylinderheadtype.CylinderHeadTypeName SEPARATOR ','),'}') AS CylinderHeadTypeID
        EOT;
        $vcdb_selects_per_qualifier['DriveTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',drivetype.DriveTypeID,'":{"Value":"',TRIM(drivetype.DriveTypeName),'","IsQualifierInDM":',IF(drivetype.DriveTypeID [CONDITION],'true','false'),'}') ORDER BY drivetype.DriveTypeName SEPARATOR ','),'}') AS DriveTypeID
        EOT;
        $vcdb_selects_per_qualifier['EngineDesignationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginedesignation.EngineDesignationID,'":{"Value":"',TRIM(enginedesignation.EngineDesignationName),'","IsQualifierInDM":',IF(enginedesignation.EngineDesignationID [CONDITION],'true','false'),'}') ORDER BY enginedesignation.EngineDesignationName SEPARATOR ','),'}') AS EngineDesignationID
        EOT;
        $vcdb_selects_per_qualifier['EngineMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS EngineMfrID
        EOT;
        $vcdb_selects_per_qualifier['EngineVersionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',engineversion.EngineVersionID,'":{"Value":"',TRIM(engineversion.EngineVersion),'","IsQualifierInDM":',IF(engineversion.EngineVersionID [CONDITION],'true','false'),'}') ORDER BY engineversion.EngineVersion SEPARATOR ','),'}') AS EngineVersionID
        EOT;
        $vcdb_selects_per_qualifier['EngineVINID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginevin.EngineVINID,'":{"Value":"',TRIM(enginevin.EngineVINName),'","IsQualifierInDM":',IF(enginevin.EngineVINID [CONDITION],'true','false'),'}') ORDER BY enginevin.EngineVINName SEPARATOR ','),'}') AS EngineVINID
        EOT;
        $vcdb_selects_per_qualifier['FrontBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_front.BrakeTypeID,'":{"Value":"',TRIM(braketype_front.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_front.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_front.BrakeTypeName SEPARATOR ','),'}') AS FrontBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['FrontSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_front.SpringTypeID,'":{"Value":"',TRIM(springtype_front.SpringTypeName),'","IsQualifierInDM":',IF(springtype_front.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_front.SpringTypeName SEPARATOR ','),'}') AS FrontSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliverySubTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverysubtype.FuelDeliverySubTypeID,'":{"Value":"',TRIM(fueldeliverysubtype.FuelDeliverySubTypeName),'","IsQualifierInDM":',IF(fueldeliverysubtype.FuelDeliverySubTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverysubtype.FuelDeliverySubTypeName SEPARATOR ','),'}') AS FuelDeliverySubTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliveryTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverytype.FuelDeliveryTypeID,'":{"Value":"',TRIM(fueldeliverytype.FuelDeliveryTypeName),'","IsQualifierInDM":',IF(fueldeliverytype.FuelDeliveryTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverytype.FuelDeliveryTypeName SEPARATOR ','),'}') AS FuelDeliveryTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemcontroltype.FuelSystemControlTypeID,'":{"Value":"',TRIM(fuelsystemcontroltype.FuelSystemControlTypeName),'","IsQualifierInDM":',IF(fuelsystemcontroltype.FuelSystemControlTypeID [CONDITION],'true','false'),'}') ORDER BY fuelsystemcontroltype.FuelSystemControlTypeName SEPARATOR ','),'}') AS FuelSystemControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemDesignID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemdesign.FuelSystemDesignID,'":{"Value":"',TRIM(fuelsystemdesign.FuelSystemDesignName),'","IsQualifierInDM":',IF(fuelsystemdesign.FuelSystemDesignID [CONDITION],'true','false'),'}') ORDER BY fuelsystemdesign.FuelSystemDesignName SEPARATOR ','),'}') AS FuelSystemDesignID
        EOT;
        $vcdb_selects_per_qualifier['FuelTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueltype.FuelTypeID,'":{"Value":"',TRIM(fueltype.FuelTypeName),'","IsQualifierInDM":',IF(fueltype.FuelTypeID [CONDITION],'true','false'),'}') ORDER BY fueltype.FuelTypeName SEPARATOR ','),'}') AS FuelTypeID
        EOT;
        $vcdb_selects_per_qualifier['IgnitionSystemTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',ignitionsystemtype.IgnitionSystemTypeID,'":{"Value":"',TRIM(ignitionsystemtype.IgnitionSystemTypeName),'","IsQualifierInDM":',IF(ignitionsystemtype.IgnitionSystemTypeID [CONDITION],'true','false'),'}') ORDER BY ignitionsystemtype.IgnitionSystemTypeName SEPARATOR ','),'}') AS IgnitionSystemTypeID
        EOT;
        $vcdb_selects_per_qualifier['MfrBodyCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfrbodycode.MfrBodyCodeID,'":{"Value":"',TRIM(mfrbodycode.MfrBodyCodeName),'","IsQualifierInDM":',IF(mfrbodycode.MfrBodyCodeID [CONDITION],'true','false'),'}') ORDER BY mfrbodycode.MfrBodyCodeName SEPARATOR ','),'}') AS MfrBodyCodeID
        EOT;
        $vcdb_selects_per_qualifier['PositionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',positions.PositionID,'":{"Value":"',TRIM(positions.Position),'","IsQualifierInDM":',IF(positions.PositionID [CONDITION],'true','false'),'}') ORDER BY positions.Position SEPARATOR ','),'}') AS PositionID
        EOT;
        $vcdb_selects_per_qualifier['PowerOutputID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',poweroutput.PowerOutputID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(poweroutput.HorserPower),'HP'),CONCAT(TRIM(poweroutput.KilowattPower),'kW')),'","IsQualifierInDM":',IF(poweroutput.PowerOutputID [CONDITION],'true','false'),'}') ORDER BY poweroutput.HorsePower,KilowattPower SEPARATOR ','),'}') AS PowerOutputID
        EOT;
        $vcdb_selects_per_qualifier['RearBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_rear.BrakeTypeID,'":{"Value":"',TRIM(braketype_rear.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_rear.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_rear.BrakeTypeName SEPARATOR ','),'}') AS RearBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['RearSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_rear.SpringTypeID,'":{"Value":"',TRIM(springtype_rear.SpringTypeName),'","IsQualifierInDM":',IF(springtype_rear.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_rear.SpringTypeName SEPARATOR ','),'}') AS RearSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['RegionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',region.RegionID,'":{"Value":"',TRIM(region.RegionName),'","IsQualifierInDM":',IF(region.RegionID [CONDITION],'true','false'),'}') ORDER BY region.RegionAbbr,RegionName SEPARATOR ','),'}') AS RegionID
        EOT;
        $vcdb_selects_per_qualifier['SteeringSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringsystem.SteeringSystemID,'":{"Value":"',TRIM(steeringsystem.SteeringSystemName),'","IsQualifierInDM":',IF(steeringsystem.SteeringSystemID [CONDITION],'true','false'),'}') ORDER BY steeringsystem.SteeringSystemName SEPARATOR ','),'}') AS SteeringSystemID
        EOT;
        $vcdb_selects_per_qualifier['SteeringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringtype.SteeringTypeID,'":{"Value":"',TRIM(steeringtype.SteeringTypeName),'","IsQualifierInDM":',IF(steeringtype.SteeringTypeID [CONDITION],'true','false'),'}') ORDER BY steeringtype.SteeringTypeName SEPARATOR ','),'}') AS SteeringTypeID
        EOT;
        $vcdb_selects_per_qualifier['SubModelID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',submodel.SubModelID,'":{"Value":"',TRIM(submodel.SubModelName),'","IsQualifierInDM":',IF(submodel.SubModelID [CONDITION],'true','false'),'}') ORDER BY submodel.SubModelName SEPARATOR ','),'}') AS SubModelID
        EOT;
        $vcdb_selects_per_qualifier['TransElecControlledID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',eleccontrolled.ElecControlledID,'":{"Value":"',TRIM(eleccontrolled.ElecControlled),'","IsQualifierInDM":',IF(eleccontrolled.ElecControlledID [CONDITION],'true','false'),'}') ORDER BY eleccontrolled.ElecControlled SEPARATOR ','),'}') AS TransElecControlledID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissioncontroltype.TransmissionControlTypeID,'":{"Value":"',TRIM(transmissioncontroltype.TransmissionControlTypeName),'","IsQualifierInDM":',IF(transmissioncontroltype.TransmissionControlTypeID [CONDITION],'true','false'),'}') ORDER BY transmissioncontroltype.TransmissionControlTypeName SEPARATOR ','),'}') AS TransmissionControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionmfrcode.TransmissionMfrCodeID,'":{"Value":"',TRIM(transmissionmfrcode.TransmissionMfrCode),'","IsQualifierInDM":',IF(transmissionmfrcode.TransmissionMfrCodeID [CONDITION],'true','false'),'}') ORDER BY transmissionmfrcode.TransmissionMfrCode SEPARATOR ','),'}') AS TransmissionMfrCodeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS TransmissionMfrID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionNumSpeedsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionnumspeeds.TransmissionNumSpeedsID,'":{"Value":"',TRIM(transmissionnumspeeds.TransmissionNumSpeeds),'","IsQualifierInDM":',IF(transmissionnumspeeds.TransmissionNumSpeedsID [CONDITION],'true','false'),'}') ORDER BY transmissionnumspeeds.TransmissionNumSpeeds SEPARATOR ','),'}') AS TransmissionNumSpeedsID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissiontype.TransmissionTypeID,'":{"Value":"',TRIM(transmissiontype.TransmissionTypeName),'","IsQualifierInDM":',IF(transmissiontype.TransmissionTypeID [CONDITION],'true','false'),'}') ORDER BY transmissiontype.TransmissionTypeName SEPARATOR ','),'}') AS TransmissionTypeID
        EOT;
        $vcdb_selects_per_qualifier['ValvesPerEngineID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',valves.ValvesID,'":{"Value":"',TRIM(valves.ValvesPerEngine),'","IsQualifierInDM":',IF(valves.ValvesID [CONDITION],'true','false'),'}') ORDER BY valves.ValvesPerEngine SEPARATOR ','),'}') AS ValvesPerEngineID
        EOT;
        $vcdb_selects_per_qualifier['WheelBaseID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',wheelbase.WheelBaseID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(wheelbase.WheelBase),'in'),CONCAT(TRIM(wheelbase.WheelBaseMetric),'cm')),'","IsQualifierInDM":',IF(wheelbase.WheelBaseID [CONDITION],'true','false'),'}') ORDER BY wheelbase.WheelBase,WheelBaseMetric SEPARATOR ','),'}') AS WheelBaseID
        EOT;


        $vehicle_definition = [
            'VehicleID' => 0,
            'BaseVehicleID' => 0,
            'YearID' => 0,
            'MakeID' => 0,
            'ModelID' => 0,
            'EngineConfigID' => 0,
            'EngineBaseID' => 0,
            
            /*The above are vehicle identifiers - typically a VehicleID or BaseVehicleID will be given
              or year, make, model (for which the basevehicle table will be needed)
              and one of EngineConfigID or EngineBaseID is needed.
              If the API's XML only returns values then more tables, e.g. make, model, will be needed.
             */    
              'AspirationID' => "Aspiration",
              'BedLengthID' => "Bed Length",
              'BedTypeID' => "Bed Type",
              'BodyNumDoorsID' => "Number of Doors",
              'BodyTypeID' => "Body Type",
              'BrakeABSID' => "ABS Type",
              'BrakeSystemID' => "Brake System",
              'CylinderHeadTypeID' => "Cylinder Head",
              'DriveTypeID' => "Drive Type",
              'EngineDesignationID' => "Engine Design",
              'EngineMfrID' => "Engine Mfr",
              'EngineVersionID' => "Engine Version",
              'EngineVINID' => "VIN",
              'FrontBrakeTypeID' => "Front Brake",
              'FrontSpringTypeID' => "Front Spring",
              'FuelDeliverySubTypeID' => "Fuel Del. Sub Type",
              'FuelDeliveryTypeID' => "Fuel Del. Type",
              'FuelSystemControlTypeID' => "Fuel Sys. Control",
              'FuelSystemDesignID' => "Fuel Sys. Design",
              'FuelTypeID' => "Fuel",
              'IgnitionSystemTypeID' => "Ignition",
              'MfrBodyCodeID' => "Body Code",
              'PositionID' => 0,
              'PowerOutputID' => "Power Output",
              'RearBrakeTypeID' => "Rear Brake",
              'RearSpringTypeID' => "Rear Spring",
              'RegionID' => "Region",
              'SteeringSystemID' => "Steering System",
              'SteeringTypeID' => "Steering",
              'SubModelID' => "SubModel",
              'TransElecControlledID' => "Trans Elec Ctrl",
              'TransmissionControlTypeID' => "Trans Ctrl Type",
              'TransmissionMfrCodeID' => "Trans Mfr Code",
              'TransmissionMfrID' => "Transmission Mfr",
              'TransmissionNumSpeedsID' => "Trans Num Speeds",
              'TransmissionTypeID' => "Trans Type",
              'ValvesPerEngineID' => "Valves Per Engine",
              'WheelBaseID' => "Wheel Base",
        
        ];

        $save_normal_pt_string=implode(",",$save_normal_pt);

        
        $data_qualifiers_select = <<<EOT
        SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
        EOT;
     
        
        $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND a.MfrLabel LIKE '%pureflow%'");

        $a=$results[0];
        $get_data_json=$a->data_json;

        $f="";

        if($get_data_json==null) {
            $f="fail";
            return json_encode($f);
        }
        else {

            
            $vcdb_tables = [];
            $vcdb_selects = [];
            $replace_count = 1;
            $result=[];

            $get_data_json=json_decode($get_data_json,true);

            foreach ($get_data_json as $qualifier_type => $qualifier_ids)  {
                if($qualifier_type != 'PositionID') {

                    if($qualifier_ids!=NULL) {
                        $result = array_diff($qualifier_ids, [0]);
                    }

                    if(count($result) >= 1 || $qualifier_ids[0] != 0) {

                        if(!empty($vehicle_definition[$qualifier_type])) {

                            $vcdb_tables = array_merge($vcdb_tables,$vcdb_tables_per_qualifier[$qualifier_type]);
                            $original_select = $vcdb_selects_per_qualifier[$qualifier_type];

                            if($qualifier_ids[0] == 0) {
                                //Any criteria will do here so as IDs can't be negative this will always return true
                                $final_select = str_replace('[CONDITION]','!= -1000',$original_select,$replace_count);

                            }
                            else {
                                $final_select = str_replace('[CONDITION]',('IN(' . implode(',', $qualifier_ids) . ')'),$original_select,$replace_count);
                            }
                            $vcdb_selects[] = $final_select;

                        }
                    }

                }
            }//foreach

            if(count($vcdb_tables) == 0) {
                $f="fail";
                return json_encode($f);
            }
            else {
                $vcdb_tables[] = 'vehicletoengineconfig';
                $vcdb_tables[] = 'engineconfig';

                $vcdb_tables_unique = array_unique($vcdb_tables);
                $vcdb_qualifiers_select = 'SELECT ' . implode(', ',$vcdb_selects) . ' FROM vehicle';

                foreach($vcdb_tables_unique as $table) {
                    $vcdb_qualifiers_select = $vcdb_qualifiers_select . ' ' . $vcdb_table_joins[$table];
                }

                $vcdb_qualifiers_select= DB::connection(self::$aces_vcdb)->select($vcdb_qualifiers_select . ' WHERE vehicle.BaseVehicleID ='.$baseVehicleId.'  AND engineconfig.EngineBaseID IN('.$engineId.')'.$qualifierlist);
                  
            }
            

            //get total qualifier count
            // $get_all_count = count($vcdb_qualifiers_select);
           
            if(empty($vcdb_qualifiers_select)) {
                $f="fail";
                return json_encode($f);
            }
            else {

                for($i=0;$i<1;$i++) {

                    foreach($vcdb_qualifiers_select[$i] as $qualifier_type => $qualifier_type_json) {

                        $matched_value=0;
                        
                        foreach($store_upcoming as $store_upcoming1) {
                            $ex=explode(":",$store_upcoming1);
                            if($ex[0]==$qualifier_type) {
                                $matched_value=1;
                            }
                        }

                        if($matched_value==0) {
                           
                            $qualifier_type_json_array = json_decode($qualifier_type_json,true);
                    
                            $qualifierlist="";
                            $result = [];

                            $show = 'true';
                    
                            $qual_count = count($qualifier_type_json_array);

                           
                            if($qual_count>1) {
                                //check count of partnumbers and its value for each qualifier value
                                foreach($qualifier_type_json_array as $qualifier_id => $qualifier_detail) {

                                    $qualifierlist = "";
                                    $qualifierlist = " and (a.".$qualifier_type."=".$qualifier_id." OR a.".$qualifier_type." IS NULL)";
                                    
                                    $partTypeArray = DB::connection(self::$main_db)
                                        ->select("SELECT p.partNumber 
                                        FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                        WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND a.MfrLabel LIKE '%pureflow%' 
                                        GROUP BY p.partNumber");


                                    $final_result = collect($partTypeArray)->pluck('partNumber')->toArray();
                                    array_push($result,$final_result);
                                
                                }

                            
                                // If partnumbers from first array are matching with other 2 arrays(all same) - Then do not show engine dropdown  
                                if ($this->areArraysIdentical($result)) {
                                    $show = 'false';
                                }
                                else {
                                    $show = 'true';
                                }
                                
                            }
                            else {
                                $show = 'false';
                            }
                            
                            

                            if($show=='false') {
                                break;
                            }

                        }
                        else {
                            continue;
                        }

                        
                    }

                    

                }

               
                return $show;


                //return $newObject;
            }

        }

    }




    public function qualifiercheck(Request $request,$baseVehicleId,$engineId,$qualifierSelected,$parttype,$partsubtype) {

        $qualifier_main_table=[
            'AspirationID' => 'aspiration.AspirationID',
            'BedLengthID' => 'bedlength.BedLengthID',
            'BedTypeID' => 'bedtype.BedTypeID',
            'BodyNumDoorsID' => 'bodynumdoors.BodyNumDoorsID',
            'BodyTypeID' => 'bodytype.BodyTypeID',
            'BrakeABSID' => 'brakeabs.BrakeABSID',
            'BrakeSystemID' => 'brakesystem.BrakeSystemID',
            'CylinderHeadTypeID' => 'cylinderheadtype.CylinderHeadTypeID',
            'DriveTypeID' => 'drivetype.DriveTypeID',
            'EngineDesignationID' => 'enginedesignation.EngineDesignationID',
            'EngineMfrID' => 'mfr.MfrID',
            'EngineVersionID' => 'engineversion.EngineVersionID',
            'EngineVINID' => 'enginevin.EngineVINID',
            'FrontBrakeTypeID' => 'braketype_front.BrakeTypeID',
            'FrontSpringTypeID' => 'springtype_front.SpringTypeID',
            'FuelDeliverySubTypeID' => 'fueldeliverysubtype.FuelDeliverySubTypeID',
            'FuelDeliveryTypeID' => 'fueldeliverytype.FuelDeliveryTypeID',
            'FuelSystemControlTypeID' => 'fuelsystemcontroltype.FuelSystemControlTypeID',
            'FuelSystemDesignID' => 'fuelsystemdesign.FuelSystemDesignID',
            'FuelTypeID' => 'fueltype.FuelTypeID',
            'IgnitionSystemTypeID' => 'ignitionsystemtype.IgnitionSystemTypeID',
            'MfrBodyCodeID' => 'mfrbodycode.MfrBodyCodeID',
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => 'poweroutput.PowerOutputID',
            'RearBrakeTypeID' => 'braketype_rear.BrakeTypeID',
            'RearSpringTypeID' => 'springtype_rear.SpringTypeID',
            'RegionID' => 'region.RegionID',
            'SteeringSystemID' => 'steeringsystem.SteeringSystemID',
            'SteeringTypeID' => 'steeringtype.SteeringTypeID',
            'SubModelID' => 'submodel.SubModelID',
            'TransElecControlledID' => 'eleccontrolled.ElecControlledID',
            'TransmissionControlTypeID' => 'transmissioncontroltype.TransmissionControlTypeID',
            'TransmissionMfrCodeID' => 'transmissionmfrcode.TransmissionMfrCodeID',
            'TransmissionMfrID' => 'mfr.MfrID',
            'TransmissionNumSpeedsID' => 'transmissionnumspeeds.TransmissionNumSpeedsID',
            'TransmissionTypeID' => 'transmissiontype.TransmissionTypeID',
            'ValvesPerEngineID' => 'valves.ValvesID',
            'WheelBaseID' => 'wheelbase.WheelBaseID'
        ];

        
        $store_upcoming=[];
        
        $explode_part=explode(",",$parttype);
        $store_upcoming=explode(",",$qualifierSelected);
        
        $qualifierlist="";
        if($store_upcoming[0]!="noqualifier") {
            if(count($store_upcoming)>1) {
                foreach ($store_upcoming as $value) {
                    $qual=explode(":",$value);
                    $where_clause[]=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                    $prevqualifier[]=$qual[0];
                }
                $qualifierlist=implode(" ",$where_clause);
            }
            else {
                $qual=explode(":",$qualifierSelected);
                $qualifierlist=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                $prevqualifier[]=$qual[0];
            }
        }

        $fueltype_table = 'fueltype';


      
        $send_fluid_pt=[];
        $send_normal_pt='';
        $save_normal_pt=[];
                
                
        for($i=0;$i<count($explode_part);$i++) {
                
            $send_normal_pt=$explode_part[$i];
            array_push($save_normal_pt,$send_normal_pt);
                
        }
        

        //We use left joins in case the VCDB isn't fully populated

        $vcdb_table_joins = [
            'basevehicle' => 'LEFT JOIN basevehicle ON vehicle.BaseVehicleID = basevehicle.BaseVehicleID',
            'region' => 'LEFT JOIN region ON vehicle.RegionID = region.RegionID',
            'submodel' => 'LEFT JOIN submodel ON vehicle.SubmodelID = submodel.SubModelID',
            'vehicletospringtypeconfig' => 'LEFT JOIN vehicletospringtypeconfig ON vehicle.VehicleID = vehicletospringtypeconfig.VehicleID',
            'springtype_front' => 'LEFT JOIN springtypeconfig ON vehicletospringtypeconfig.SpringTypeConfigID = springtypeconfig.SpringTypeConfigID',
            'springtype' => 'LEFT JOIN springtype springtype_front ON springtypeconfig.FrontSpringTypeID = springtype_front.SpringTypeID',
            'springtype_rear' => 'LEFT JOIN springtype springtype_rear ON springtypeconfig.RearSpringTypeID = springtype_rear.SpringTypeID',
            'vehicletosteeringconfig' => 'LEFT JOIN vehicletosteeringconfig ON vehicle.VehicleID = vehicletosteeringconfig.VehicleID',
            'steeringconfig' => 'LEFT JOIN steeringconfig ON vehicletosteeringconfig.SteeringConfigID = steeringconfig.SteeringConfigID',
            'steeringtype' => 'LEFT JOIN steeringtype ON steeringconfig.SteeringTypeID = steeringtype.SteeringTypeID',
            'steeringsystem' => 'LEFT JOIN steeringsystem ON steeringconfig.SteeringSystemID = steeringsystem.SteeringSystemID',
            'vehicletobedconfig' => 'LEFT JOIN vehicletobedconfig ON vehicle.VehicleID = vehicletobedconfig.VehicleID',
            'bedconfig' => 'LEFT JOIN bedconfig ON vehicletobedconfig.BedConfigID = bedconfig.BedConfigID',
            'bedtype' => 'LEFT JOIN bedtype ON bedconfig.BedTypeID = bedtype.BedTypeID',
            'bedlength' => 'LEFT JOIN bedlength ON bedconfig.BedLengthID = bedlength.BedLengthID',
            'vehicletobrakeconfig' => 'LEFT JOIN vehicletobrakeconfig ON vehicle.VehicleID = vehicletobrakeconfig.VehicleID',
            'braketype_front' => 'LEFT JOIN brakeconfig ON vehicletobrakeconfig.BrakeConfigID = brakeconfig.BrakeConfigID',
            'braketype' => 'LEFT JOIN braketype braketype_front ON brakeconfig.FrontBrakeTypeID = braketype_front.BrakeTypeID',
            'braketype_rear' => 'LEFT JOIN braketype braketype_rear ON brakeconfig.RearBrakeTypeID = braketype_rear.BrakeTypeID',
            'brakeabs' => 'LEFT JOIN brakeabs ON brakeconfig.BrakeABSID = brakeabs.BrakeABSID',
            'brakesystem' => 'LEFT JOIN brakesystem ON brakeconfig.BrakeSystemID = brakesystem.BrakeSystemID',
            'vehicletodrivetype' => 'LEFT JOIN vehicletodrivetype ON vehicle.VehicleID = vehicletodrivetype.VehicleID',
            'drivetype' => 'LEFT JOIN drivetype ON vehicletodrivetype.DriveTypeID = drivetype.DriveTypeID',
            'vehicletowheelbase' => 'LEFT JOIN vehicletowheelbase ON vehicle.VehicleID = vehicletowheelbase.VehicleID',
            'wheelbase' => 'LEFT JOIN wheelbase ON vehicletowheelbase.WheelbaseID = wheelbase.WheelBaseID',
            'vehicletobodystyleconfig' => 'LEFT JOIN vehicletobodystyleconfig ON vehicle.VehicleID = vehicletobodystyleconfig.VehicleID',
            'bodystyleconfig' => 'LEFT JOIN bodystyleconfig ON vehicletobodystyleconfig.BodyStyleConfigID = bodystyleconfig.BodyStyleConfigID',
            'bodynumdoors' => 'LEFT JOIN bodynumdoors ON bodystyleconfig.BodyNumDoorsID = bodynumdoors.BodyNumDoorsID',
            'bodytype' => 'LEFT JOIN bodytype ON bodystyleconfig.BodyTypeID = bodytype.BodyTypeID',
            'vehicletomfrbodycode' => 'LEFT JOIN vehicletomfrbodycode ON vehicle.VehicleID = vehicletomfrbodycode.VehicleID',
            'mfrbodycode' => 'LEFT JOIN mfrbodycode ON vehicletomfrbodycode.MfrBodyCodeID = mfrbodycode.MfrBodyCodeID',
            'vehicletotransmission' => 'LEFT JOIN vehicletotransmission ON vehicle.VehicleID = vehicletotransmission.VehicleID',
            'transmission' => 'LEFT JOIN transmission ON vehicletotransmission.TransmissionID = transmission.TransmissionID',
            'eleccontrolled' => 'LEFT JOIN eleccontrolled ON transmission.TransmissionElecControlledID = eleccontrolled.ElecControlledID',
            'transmissionbase' => 'LEFT JOIN transmissionbase ON transmission.TransmissionBaseID = transmissionbase.TransmissionBaseID',
            'transmissiontype' => 'LEFT JOIN transmissiontype ON transmissionbase.TransmissionTypeID = transmissiontype.TransmissionTypeID',
            'transmissioncontroltype' => 'LEFT JOIN transmissioncontroltype ON transmissionbase.TransmissionControlTypeID = transmissioncontroltype.TransmissionControlTypeID',
            'transmissionnumspeeds' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr_transmission' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr' => 'LEFT JOIN mfr ON transmission.TransmissionMfrID = mfr.MfrID',
            'transmissionmfrcode' => 'LEFT JOIN transmissionmfrcode ON transmission.TransmissionMfrCodeID = transmissionmfrcode.TransmissionMfrCodeID',
            'vehicletoengineconfig' => 'LEFT JOIN vehicletoengineconfig ON vehicle.VehicleID = vehicletoengineconfig.VehicleID',
            'engineconfig' => 'LEFT JOIN engineconfig ON vehicletoengineconfig.EngineConfigID = engineconfig.EngineConfigID',
            'ignitionsystemtype' => 'LEFT JOIN ignitionsystemtype ON engineconfig.IgnitionSystemTypeID = ignitionsystemtype.IgnitionSystemTypeID',
            'cylinderheadtype' => 'LEFT JOIN cylinderheadtype ON engineconfig.CylinderHeadTypeID = cylinderheadtype.CylinderHeadTypeID',
            'aspiration' => 'LEFT JOIN aspiration ON engineconfig.AspirationID = aspiration.AspirationID',
            'fueltype' => "LEFT JOIN $fueltype_table ON engineconfig.FuelTypeID = fueltype.FuelTypeID",
            'valves' => 'LEFT JOIN valves ON engineconfig.ValvesID = valves.ValvesID',
            'enginevin' => 'LEFT JOIN enginevin ON engineconfig.EngineVINID = enginevin.EngineVINID',
            'mfr_engine' => 'LEFT JOIN engineversion ON engineconfig.EngineVersionID = engineversion.EngineVersionID',
            'engine_mfr' => 'LEFT JOIN mfr  ON engineconfig.EngineMfrID = mfr.MfrID',
            'fueldeliveryconfig' => 'LEFT JOIN fueldeliveryconfig ON engineconfig.FuelDeliveryConfigID = fueldeliveryconfig.FuelDeliveryConfigID',
            'fueldeliverytype' => 'LEFT JOIN fueldeliverytype ON fueldeliveryconfig.FuelDeliveryTypeID = fueldeliverytype.FuelDeliveryTypeID',
            'fuelsystemcontroltype' => 'LEFT JOIN fuelsystemcontroltype ON fueldeliveryconfig.FuelSystemControlTypeID = fuelsystemcontroltype.FuelSystemControlTypeID',
            'fueldeliverysubtype' => 'LEFT JOIN fueldeliverysubtype ON fueldeliveryconfig.FuelDeliverySubTypeID = fueldeliverysubtype.FuelDeliverySubTypeID',
            'fuelsystemdesign' => 'LEFT JOIN fuelsystemdesign ON fueldeliveryconfig.FuelSystemDesignID = fuelsystemdesign.FuelSystemDesignID',
            'enginedesignation' => 'LEFT JOIN enginedesignation ON engineconfig.EngineDesignationID = enginedesignation.EngineDesignationID',
            'enginebase' => 'LEFT JOIN enginebase ON engineconfig.EngineBaseID = enginebase.EngineBaseID'
            
        ];


        $vcdb_tables_per_qualifier = [
            'AspirationID' => ['vehicletoengineconfig','engineconfig','aspiration'],
            'BedLengthID' => ['vehicletobedconfig','bedconfig','bedlength'],
            'BedTypeID' => ['vehicletobedconfig','bedconfig','bedtype'],
            'BodyNumDoorsID' => ['vehicletobodystyleconfig','bodystyleconfig','bodynumdoors'],
            'BodyTypeID' => ['vehicletobodystyleconfig','bodystyleconfig','bodytype'],
            'BrakeABSID' => ['vehicletobrakeconfig','braketype_front','brakeabs'],
            'BrakeSystemID' => ['vehicletobrakeconfig','braketype_front','brakesystem'],
            'CylinderHeadTypeID' => ['vehicletoengineconfig','engineconfig','cylinderheadtype'],
            'DriveTypeID' => ['vehicletodrivetype','drivetype'],
            'EngineDesignationID' => ['vehicletoengineconfig','engineconfig','enginedesignation'],
            'EngineMfrID' => ['vehicletoengineconfig','engineconfig','engine_mfr'],//actual table name is mfr only
            'EngineVersionID' => ['vehicletoengineconfig','engineconfig','mfr_engine'],
            'EngineVINID' => ['vehicletoengineconfig','engineconfig','enginevin'],
            'FrontBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype'],
            'FrontSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype'],
            'FuelDeliverySubTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverysubtype'],
            'FuelDeliveryTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverytype'],
            'FuelSystemControlTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemcontroltype'],
            'FuelSystemDesignID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemdesign'],
            'FuelTypeID' => ['vehicletoengineconfig','engineconfig','fueltype'],
            'IgnitionSystemTypeID' => ['vehicletoengineconfig','engineconfig','ignitionsystemtype'],
            'MfrBodyCodeID' => ['vehicletomfrbodycode','mfrbodycode'],
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => ['vehicletoengineconfig','engineconfig','poweroutput'],
            'RearBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype_rear'],
            'RearSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype_rear'],
            'RegionID' => ['region'],
            'SteeringSystemID' => ['vehicletosteeringconfig','steeringconfig','steeringsystem'],
            'SteeringTypeID' => ['vehicletosteeringconfig','steeringconfig','steeringtype'],
            'SubModelID' => ['submodel'],
            'TransElecControlledID' => ['vehicletotransmission','transmission','eleccontrolled'],
            'TransmissionControlTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissioncontroltype'],
            'TransmissionMfrCodeID' => ['vehicletotransmission','transmission','transmissionmfrcode'],
            'TransmissionMfrID' => ['vehicletotransmission','transmission','mfr'],
            'TransmissionNumSpeedsID' => ['vehicletotransmission','transmission','transmissionbase','transmissionnumspeeds'],
            'TransmissionTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissiontype'],
            'ValvesPerEngineID' => ['vehicletoengineconfig','engineconfig','valves'],
            'WheelBaseID' => ['vehicletowheelbase','wheelbase']
        ];

        //Example questions (get the real one's off of John):
        //Accompanied by an optional graphic (like the one I sent in a Word document previously):

        $question_graphics = [
            'EngineVINID' => '/path/to/a/graphic',
        ];

        //We don't use position as it's a part specific qualifier not vehicle specific
        //Strings contains both single and double quotes so we use heredoc syntax.
        $vcdb_selects_per_qualifier['AspirationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',aspiration.AspirationID,'":{"Value":"',TRIM(aspiration.AspirationName),'","IsQualifierInDM":',IF(aspiration.AspirationID [CONDITION],'true','false'),'}') ORDER BY aspiration.AspirationName SEPARATOR ','),'}') AS AspirationID
        EOT;
        $vcdb_selects_per_qualifier['BedLengthID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedlength.BedLengthID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(bedlength.BedLength),'in'),CONCAT(TRIM(bedlength.BedLengthMetric),'cm')),'","IsQualifierInDM":',IF(bedlength.BedLengthID [CONDITION],'true','false'),'}') ORDER BY bedlength.BedLength,BedLengthMetric SEPARATOR ','),'}') AS BedLengthID
        EOT;

        $vcdb_selects_per_qualifier['BedTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedtype.BedTypeID,'":{"Value":"',TRIM(bedtype.BedTypeName),'","IsQualifierInDM":',IF(bedtype.BedTypeID [CONDITION],'true','false'),'}') ORDER BY bedtype.BedTypeName SEPARATOR ','),'}') AS BedTypeID
        EOT;

        $vcdb_selects_per_qualifier['BodyNumDoorsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodynumdoors.BodyNumDoorsID,'":{"Value":"',TRIM(bodynumdoors.BodyNumDoors),'","IsQualifierInDM":',IF(bodynumdoors.BodyNumDoorsID [CONDITION],'true','false'),'}') ORDER BY bodynumdoors.BodyNumDoors SEPARATOR ','),'}') AS BodyNumDoorsID
        EOT;
        $vcdb_selects_per_qualifier['BodyTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodytype.BodyTypeID,'":{"Value":"',TRIM(bodytype.BodyTypeName),'","IsQualifierInDM":',IF(bodytype.BodyTypeID [CONDITION],'true','false'),'}') ORDER BY bodytype.BodyTypeName SEPARATOR ','),'}') AS BodyTypeID
        EOT;
        $vcdb_selects_per_qualifier['BrakeABSID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakeabs.BrakeABSID,'":{"Value":"',TRIM(brakeabs.BrakeABSName),'","IsQualifierInDM":',IF(brakeabs.BrakeABSID [CONDITION],'true','false'),'}') ORDER BY brakeabs.BrakeABSName SEPARATOR ','),'}') AS BrakeABSID
        EOT;
        $vcdb_selects_per_qualifier['BrakeSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakesystem.BrakeSystemID,'":{"Value":"',TRIM(brakesystem.BrakeSystemName),'","IsQualifierInDM":',IF(brakesystem.BrakeSystemID [CONDITION],'true','false'),'}') ORDER BY brakesystem.BrakeSystemName SEPARATOR ','),'}') AS BrakeSystemID
        EOT;
        $vcdb_selects_per_qualifier['CylinderHeadTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',cylinderheadtype.CylinderHeadTypeID,'":{"Value":"',TRIM(cylinderheadtype.CylinderHeadTypeName),'","IsQualifierInDM":',IF(cylinderheadtype.CylinderHeadTypeID [CONDITION],'true','false'),'}') ORDER BY cylinderheadtype.CylinderHeadTypeName SEPARATOR ','),'}') AS CylinderHeadTypeID
        EOT;
        $vcdb_selects_per_qualifier['DriveTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',drivetype.DriveTypeID,'":{"Value":"',TRIM(drivetype.DriveTypeName),'","IsQualifierInDM":',IF(drivetype.DriveTypeID [CONDITION],'true','false'),'}') ORDER BY drivetype.DriveTypeName SEPARATOR ','),'}') AS DriveTypeID
        EOT;
        $vcdb_selects_per_qualifier['EngineDesignationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginedesignation.EngineDesignationID,'":{"Value":"',TRIM(enginedesignation.EngineDesignationName),'","IsQualifierInDM":',IF(enginedesignation.EngineDesignationID [CONDITION],'true','false'),'}') ORDER BY enginedesignation.EngineDesignationName SEPARATOR ','),'}') AS EngineDesignationID
        EOT;
        $vcdb_selects_per_qualifier['EngineMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS EngineMfrID
        EOT;
        $vcdb_selects_per_qualifier['EngineVersionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',engineversion.EngineVersionID,'":{"Value":"',TRIM(engineversion.EngineVersion),'","IsQualifierInDM":',IF(engineversion.EngineVersionID [CONDITION],'true','false'),'}') ORDER BY engineversion.EngineVersion SEPARATOR ','),'}') AS EngineVersionID
        EOT;
        $vcdb_selects_per_qualifier['EngineVINID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginevin.EngineVINID,'":{"Value":"',TRIM(enginevin.EngineVINName),'","IsQualifierInDM":',IF(enginevin.EngineVINID [CONDITION],'true','false'),'}') ORDER BY enginevin.EngineVINName SEPARATOR ','),'}') AS EngineVINID
        EOT;
        $vcdb_selects_per_qualifier['FrontBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_front.BrakeTypeID,'":{"Value":"',TRIM(braketype_front.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_front.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_front.BrakeTypeName SEPARATOR ','),'}') AS FrontBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['FrontSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_front.SpringTypeID,'":{"Value":"',TRIM(springtype_front.SpringTypeName),'","IsQualifierInDM":',IF(springtype_front.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_front.SpringTypeName SEPARATOR ','),'}') AS FrontSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliverySubTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverysubtype.FuelDeliverySubTypeID,'":{"Value":"',TRIM(fueldeliverysubtype.FuelDeliverySubTypeName),'","IsQualifierInDM":',IF(fueldeliverysubtype.FuelDeliverySubTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverysubtype.FuelDeliverySubTypeName SEPARATOR ','),'}') AS FuelDeliverySubTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliveryTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverytype.FuelDeliveryTypeID,'":{"Value":"',TRIM(fueldeliverytype.FuelDeliveryTypeName),'","IsQualifierInDM":',IF(fueldeliverytype.FuelDeliveryTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverytype.FuelDeliveryTypeName SEPARATOR ','),'}') AS FuelDeliveryTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemcontroltype.FuelSystemControlTypeID,'":{"Value":"',TRIM(fuelsystemcontroltype.FuelSystemControlTypeName),'","IsQualifierInDM":',IF(fuelsystemcontroltype.FuelSystemControlTypeID [CONDITION],'true','false'),'}') ORDER BY fuelsystemcontroltype.FuelSystemControlTypeName SEPARATOR ','),'}') AS FuelSystemControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemDesignID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemdesign.FuelSystemDesignID,'":{"Value":"',TRIM(fuelsystemdesign.FuelSystemDesignName),'","IsQualifierInDM":',IF(fuelsystemdesign.FuelSystemDesignID [CONDITION],'true','false'),'}') ORDER BY fuelsystemdesign.FuelSystemDesignName SEPARATOR ','),'}') AS FuelSystemDesignID
        EOT;
        $vcdb_selects_per_qualifier['FuelTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueltype.FuelTypeID,'":{"Value":"',TRIM(fueltype.FuelTypeName),'","IsQualifierInDM":',IF(fueltype.FuelTypeID [CONDITION],'true','false'),'}') ORDER BY fueltype.FuelTypeName SEPARATOR ','),'}') AS FuelTypeID
        EOT;
        $vcdb_selects_per_qualifier['IgnitionSystemTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',ignitionsystemtype.IgnitionSystemTypeID,'":{"Value":"',TRIM(ignitionsystemtype.IgnitionSystemTypeName),'","IsQualifierInDM":',IF(ignitionsystemtype.IgnitionSystemTypeID [CONDITION],'true','false'),'}') ORDER BY ignitionsystemtype.IgnitionSystemTypeName SEPARATOR ','),'}') AS IgnitionSystemTypeID
        EOT;
        $vcdb_selects_per_qualifier['MfrBodyCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfrbodycode.MfrBodyCodeID,'":{"Value":"',TRIM(mfrbodycode.MfrBodyCodeName),'","IsQualifierInDM":',IF(mfrbodycode.MfrBodyCodeID [CONDITION],'true','false'),'}') ORDER BY mfrbodycode.MfrBodyCodeName SEPARATOR ','),'}') AS MfrBodyCodeID
        EOT;
        $vcdb_selects_per_qualifier['PositionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',positions.PositionID,'":{"Value":"',TRIM(positions.Position),'","IsQualifierInDM":',IF(positions.PositionID [CONDITION],'true','false'),'}') ORDER BY positions.Position SEPARATOR ','),'}') AS PositionID
        EOT;
        $vcdb_selects_per_qualifier['PowerOutputID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',poweroutput.PowerOutputID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(poweroutput.HorserPower),'HP'),CONCAT(TRIM(poweroutput.KilowattPower),'kW')),'","IsQualifierInDM":',IF(poweroutput.PowerOutputID [CONDITION],'true','false'),'}') ORDER BY poweroutput.HorsePower,KilowattPower SEPARATOR ','),'}') AS PowerOutputID
        EOT;
        $vcdb_selects_per_qualifier['RearBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_rear.BrakeTypeID,'":{"Value":"',TRIM(braketype_rear.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_rear.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_rear.BrakeTypeName SEPARATOR ','),'}') AS RearBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['RearSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_rear.SpringTypeID,'":{"Value":"',TRIM(springtype_rear.SpringTypeName),'","IsQualifierInDM":',IF(springtype_rear.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_rear.SpringTypeName SEPARATOR ','),'}') AS RearSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['RegionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',region.RegionID,'":{"Value":"',TRIM(region.RegionName),'","IsQualifierInDM":',IF(region.RegionID [CONDITION],'true','false'),'}') ORDER BY region.RegionAbbr,RegionName SEPARATOR ','),'}') AS RegionID
        EOT;
        $vcdb_selects_per_qualifier['SteeringSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringsystem.SteeringSystemID,'":{"Value":"',TRIM(steeringsystem.SteeringSystemName),'","IsQualifierInDM":',IF(steeringsystem.SteeringSystemID [CONDITION],'true','false'),'}') ORDER BY steeringsystem.SteeringSystemName SEPARATOR ','),'}') AS SteeringSystemID
        EOT;
        $vcdb_selects_per_qualifier['SteeringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringtype.SteeringTypeID,'":{"Value":"',TRIM(steeringtype.SteeringTypeName),'","IsQualifierInDM":',IF(steeringtype.SteeringTypeID [CONDITION],'true','false'),'}') ORDER BY steeringtype.SteeringTypeName SEPARATOR ','),'}') AS SteeringTypeID
        EOT;
        $vcdb_selects_per_qualifier['SubModelID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',submodel.SubModelID,'":{"Value":"',TRIM(submodel.SubModelName),'","IsQualifierInDM":',IF(submodel.SubModelID [CONDITION],'true','false'),'}') ORDER BY submodel.SubModelName SEPARATOR ','),'}') AS SubModelID
        EOT;
        $vcdb_selects_per_qualifier['TransElecControlledID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',eleccontrolled.ElecControlledID,'":{"Value":"',TRIM(eleccontrolled.ElecControlled),'","IsQualifierInDM":',IF(eleccontrolled.ElecControlledID [CONDITION],'true','false'),'}') ORDER BY eleccontrolled.ElecControlled SEPARATOR ','),'}') AS TransElecControlledID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissioncontroltype.TransmissionControlTypeID,'":{"Value":"',TRIM(transmissioncontroltype.TransmissionControlTypeName),'","IsQualifierInDM":',IF(transmissioncontroltype.TransmissionControlTypeID [CONDITION],'true','false'),'}') ORDER BY transmissioncontroltype.TransmissionControlTypeName SEPARATOR ','),'}') AS TransmissionControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionmfrcode.TransmissionMfrCodeID,'":{"Value":"',TRIM(transmissionmfrcode.TransmissionMfrCode),'","IsQualifierInDM":',IF(transmissionmfrcode.TransmissionMfrCodeID [CONDITION],'true','false'),'}') ORDER BY transmissionmfrcode.TransmissionMfrCode SEPARATOR ','),'}') AS TransmissionMfrCodeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS TransmissionMfrID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionNumSpeedsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionnumspeeds.TransmissionNumSpeedsID,'":{"Value":"',TRIM(transmissionnumspeeds.TransmissionNumSpeeds),'","IsQualifierInDM":',IF(transmissionnumspeeds.TransmissionNumSpeedsID [CONDITION],'true','false'),'}') ORDER BY transmissionnumspeeds.TransmissionNumSpeeds SEPARATOR ','),'}') AS TransmissionNumSpeedsID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissiontype.TransmissionTypeID,'":{"Value":"',TRIM(transmissiontype.TransmissionTypeName),'","IsQualifierInDM":',IF(transmissiontype.TransmissionTypeID [CONDITION],'true','false'),'}') ORDER BY transmissiontype.TransmissionTypeName SEPARATOR ','),'}') AS TransmissionTypeID
        EOT;
        $vcdb_selects_per_qualifier['ValvesPerEngineID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',valves.ValvesID,'":{"Value":"',TRIM(valves.ValvesPerEngine),'","IsQualifierInDM":',IF(valves.ValvesID [CONDITION],'true','false'),'}') ORDER BY valves.ValvesPerEngine SEPARATOR ','),'}') AS ValvesPerEngineID
        EOT;
        $vcdb_selects_per_qualifier['WheelBaseID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',wheelbase.WheelBaseID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(wheelbase.WheelBase),'in'),CONCAT(TRIM(wheelbase.WheelBaseMetric),'cm')),'","IsQualifierInDM":',IF(wheelbase.WheelBaseID [CONDITION],'true','false'),'}') ORDER BY wheelbase.WheelBase,WheelBaseMetric SEPARATOR ','),'}') AS WheelBaseID
        EOT;


        $vehicle_definition = [
            'VehicleID' => 0,
            'BaseVehicleID' => 0,
            'YearID' => 0,
            'MakeID' => 0,
            'ModelID' => 0,
            'EngineConfigID' => 0,
            'EngineBaseID' => 0,
            
            /*The above are vehicle identifiers - typically a VehicleID or BaseVehicleID will be given
              or year, make, model (for which the basevehicle table will be needed)
              and one of EngineConfigID or EngineBaseID is needed.
              If the API's XML only returns values then more tables, e.g. make, model, will be needed.
             */    
              'AspirationID' => "Aspiration",
              'BedLengthID' => "Bed Length",
              'BedTypeID' => "Bed Type",
              'BodyNumDoorsID' => "Number of Doors",
              'BodyTypeID' => "Body Type",
              'BrakeABSID' => "ABS Type",
              'BrakeSystemID' => "Brake System",
              'CylinderHeadTypeID' => "Cylinder Head",
              'DriveTypeID' => "Drive Type",
              'EngineDesignationID' => "Engine Design",
              'EngineMfrID' => "Engine Mfr",
              'EngineVersionID' => "Engine Version",
              'EngineVINID' => "VIN",
              'FrontBrakeTypeID' => "Front Brake",
              'FrontSpringTypeID' => "Front Spring",
              'FuelDeliverySubTypeID' => "Fuel Del. Sub Type",
              'FuelDeliveryTypeID' => "Fuel Del. Type",
              'FuelSystemControlTypeID' => "Fuel Sys. Control",
              'FuelSystemDesignID' => "Fuel Sys. Design",
              'FuelTypeID' => "Fuel",
              'IgnitionSystemTypeID' => "Ignition",
              'MfrBodyCodeID' => "Body Code",
              'PositionID' => 0,
              'PowerOutputID' => "Power Output",
              'RearBrakeTypeID' => "Rear Brake",
              'RearSpringTypeID' => "Rear Spring",
              'RegionID' => "Region",
              'SteeringSystemID' => "Steering System",
              'SteeringTypeID' => "Steering",
              'SubModelID' => "SubModel",
              'TransElecControlledID' => "Trans Elec Ctrl",
              'TransmissionControlTypeID' => "Trans Ctrl Type",
              'TransmissionMfrCodeID' => "Trans Mfr Code",
              'TransmissionMfrID' => "Transmission Mfr",
              'TransmissionNumSpeedsID' => "Trans Num Speeds",
              'TransmissionTypeID' => "Trans Type",
              'ValvesPerEngineID' => "Valves Per Engine",
              'WheelBaseID' => "Wheel Base",
        
        ];

        $save_normal_pt_string=implode(",",$save_normal_pt);

        
        $data_qualifiers_select = <<<EOT
        SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
        EOT;
     
        
        $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND a.MfrLabel LIKE '%pureflow%'");

        $a=$results[0];
        $get_data_json=$a->data_json;

        $f="";

        if($get_data_json==null) {
            $f="fail";
            return json_encode($f);
        }
        else {
            $vcdb_tables = [];
            $vcdb_selects = [];
            $replace_count = 1;
            $result=[];

            $get_data_json=json_decode($get_data_json,true);

            foreach ($get_data_json as $qualifier_type => $qualifier_ids)  {
                if($qualifier_type != 'PositionID') {

                    if($qualifier_ids!=NULL) {
                        $result = array_diff($qualifier_ids, [0]);
                    }

                    if(count($result) >= 1 || $qualifier_ids[0] != 0) {

                        if(!empty($vehicle_definition[$qualifier_type])) {

                            $vcdb_tables = array_merge($vcdb_tables,$vcdb_tables_per_qualifier[$qualifier_type]);
                            $original_select = $vcdb_selects_per_qualifier[$qualifier_type];

                            if($qualifier_ids[0] == 0) {
                                //Any criteria will do here so as IDs can't be negative this will always return true
                                $final_select = str_replace('[CONDITION]','!= -1000',$original_select,$replace_count);

                            }
                            else {
                                $final_select = str_replace('[CONDITION]',('IN(' . implode(',', $qualifier_ids) . ')'),$original_select,$replace_count);
                            }
                            $vcdb_selects[] = $final_select;

                        }
                    }

                }
            }//foreach

            if(count($vcdb_tables) == 0) {
                $f="fail";
                return json_encode($f);
            }
            else {
                $vcdb_tables[] = 'vehicletoengineconfig';
                $vcdb_tables[] = 'engineconfig';

                $vcdb_tables_unique = array_unique($vcdb_tables);
                $vcdb_qualifiers_select = 'SELECT ' . implode(', ',$vcdb_selects) . ' FROM vehicle';

                foreach($vcdb_tables_unique as $table) {
                    $vcdb_qualifiers_select = $vcdb_qualifiers_select . ' ' . $vcdb_table_joins[$table];
                }

                $vcdb_qualifiers_select= DB::connection(self::$aces_vcdb)->select($vcdb_qualifiers_select . ' WHERE vehicle.BaseVehicleID ='.$baseVehicleId.'  AND engineconfig.EngineBaseID IN('.$engineId.')'.$qualifierlist);
                  
            }
            

            //get total qualifier count
            // $get_all_count = count($vcdb_qualifiers_select);
           
            if(empty($vcdb_qualifiers_select)) {
                $f="fail";
                return json_encode($f);
            }
            else {

                $get_value_of_q["values"]=[];
                $get_value_of_q["name"]=[];
                $get_value_of_q["showdropdown"]=[];

             
                for($i=0;$i<1;$i++) {

                    foreach($vcdb_qualifiers_select[$i] as $qualifier_type => $qualifier_type_json) {

                        $matched_value=0;
                      
                        foreach($store_upcoming as $store_upcoming1) {
                            $ex=explode(":",$store_upcoming1);
                            if($ex[0]==$qualifier_type) {
                                $matched_value=1;
                            }
                        }

                        if($matched_value==0) {
                           
                            $qualifier_type_json_array = json_decode($qualifier_type_json,true);
                            $qualifier_name=$vehicle_definition[$qualifier_type];
                            array_push($get_value_of_q["name"],$qualifier_name);


                            $qualifierlist="";
                            $result = [];

                            $show = 'true';
                    
                            $qual_count = count($qualifier_type_json_array);

                            
                                //check count of partnumbers and its value for each qualifier value
                                foreach($qualifier_type_json_array as $qualifier_id => $qualifier_detail) {

                                    $header="";
                                    $header='{"id":"'.$qualifier_type.":".$qualifier_id.'","values":"'.$qualifier_detail['Value'].'"}';
                                    $header=json_decode($header);
                                    array_push($get_value_of_q["values"],$header);

                                    
                                    $qualifierlist = "";
                                    $qualifierlist = " and (a.".$qualifier_type."=".$qualifier_id." OR a.".$qualifier_type." IS NULL)";
                                    
                                    $partTypeArray = DB::connection(self::$main_db)
                                        ->select("SELECT p.partNumber 
                                        FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                        WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." AND a.MfrLabel LIKE '%pureflow%' 
                                        GROUP BY p.partNumber");

                                    
                                    $final_result = collect($partTypeArray)->pluck('partNumber')->toArray();
                                    array_push($result,$final_result);
                                    
                                    
                                    
                                
                                }

                           
                                if($qual_count>1) {
                                    // If partnumbers from first array are matching with other 2 arrays(all same) - Then do not show engine dropdown  
                                    if ($this->areArraysIdentical($result)) {
                                        $show = 'false';
                                    }
                                    else {
                                        $show = 'true';
                                    }
                                
                                }
                                else {
                                    $show = 'false';
                                }


                                if($show=='false') {
                                    break;
                                }
                        }
                        else {
                            continue;
                        }
                        
                    }

                   

                }
                array_push($get_value_of_q["showdropdown"],$show);

                return $get_value_of_q;

            }

        }

    }




    public function qualifiersforAllPartTypes(Request $request,$baseVehicleId,$engineId,$qualifierSelected,$parttype,$partsubtype) {

        $qualifier_main_table=[
            'AspirationID' => 'aspiration.AspirationID',
            'BedLengthID' => 'bedlength.BedLengthID',
            'BedTypeID' => 'bedtype.BedTypeID',
            'BodyNumDoorsID' => 'bodynumdoors.BodyNumDoorsID',
            'BodyTypeID' => 'bodytype.BodyTypeID',
            'BrakeABSID' => 'brakeabs.BrakeABSID',
            'BrakeSystemID' => 'brakesystem.BrakeSystemID',
            'CylinderHeadTypeID' => 'cylinderheadtype.CylinderHeadTypeID',
            'DriveTypeID' => 'drivetype.DriveTypeID',
            'EngineDesignationID' => 'enginedesignation.EngineDesignationID',
            'EngineMfrID' => 'mfr.MfrID',
            'EngineVersionID' => 'engineversion.EngineVersionID',
            'EngineVINID' => 'enginevin.EngineVINID',
            'FrontBrakeTypeID' => 'braketype_front.BrakeTypeID',
            'FrontSpringTypeID' => 'springtype_front.SpringTypeID',
            'FuelDeliverySubTypeID' => 'fueldeliverysubtype.FuelDeliverySubTypeID',
            'FuelDeliveryTypeID' => 'fueldeliverytype.FuelDeliveryTypeID',
            'FuelSystemControlTypeID' => 'fuelsystemcontroltype.FuelSystemControlTypeID',
            'FuelSystemDesignID' => 'fuelsystemdesign.FuelSystemDesignID',
            'FuelTypeID' => 'fueltype.FuelTypeID',
            'IgnitionSystemTypeID' => 'ignitionsystemtype.IgnitionSystemTypeID',
            'MfrBodyCodeID' => 'mfrbodycode.MfrBodyCodeID',
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => 'poweroutput.PowerOutputID',
            'RearBrakeTypeID' => 'braketype_rear.BrakeTypeID',
            'RearSpringTypeID' => 'springtype_rear.SpringTypeID',
            'RegionID' => 'region.RegionID',
            'SteeringSystemID' => 'steeringsystem.SteeringSystemID',
            'SteeringTypeID' => 'steeringtype.SteeringTypeID',
            'SubModelID' => 'submodel.SubModelID',
            'TransElecControlledID' => 'eleccontrolled.ElecControlledID',
            'TransmissionControlTypeID' => 'transmissioncontroltype.TransmissionControlTypeID',
            'TransmissionMfrCodeID' => 'transmissionmfrcode.TransmissionMfrCodeID',
            'TransmissionMfrID' => 'mfr.MfrID',
            'TransmissionNumSpeedsID' => 'transmissionnumspeeds.TransmissionNumSpeedsID',
            'TransmissionTypeID' => 'transmissiontype.TransmissionTypeID',
            'ValvesPerEngineID' => 'valves.ValvesID',
            'WheelBaseID' => 'wheelbase.WheelBaseID'
        ];

        
        $store_upcoming=[];
        
        $explode_part=explode(",",$parttype);
        $store_upcoming=explode(",",$qualifierSelected);
        
        $qualifierlist="";
        if($store_upcoming[0]!="noqualifier") {
            if(count($store_upcoming)>1) {
                foreach ($store_upcoming as $value) {
                    $qual=explode(":",$value);
                    $where_clause[]=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                    $prevqualifier[]=$qual[0];
                }
                $qualifierlist=implode(" ",$where_clause);
            }
            else {
                $qual=explode(":",$qualifierSelected);
                $qualifierlist=" AND ".$qualifier_main_table[$qual[0]]."=".$qual[1];
                $prevqualifier[]=$qual[0];
            }
        }

        $fueltype_table = 'fueltype';


      
        $send_fluid_pt=[];
        $send_normal_pt='';
        $save_normal_pt=[];
                
                
        for($i=0;$i<count($explode_part);$i++) {
                
            $send_normal_pt=$explode_part[$i];
            array_push($save_normal_pt,$send_normal_pt);
                
        }
        

        //We use left joins in case the VCDB isn't fully populated

        $vcdb_table_joins = [
            'basevehicle' => 'LEFT JOIN basevehicle ON vehicle.BaseVehicleID = basevehicle.BaseVehicleID',
            'region' => 'LEFT JOIN region ON vehicle.RegionID = region.RegionID',
            'submodel' => 'LEFT JOIN submodel ON vehicle.SubmodelID = submodel.SubModelID',
            'vehicletospringtypeconfig' => 'LEFT JOIN vehicletospringtypeconfig ON vehicle.VehicleID = vehicletospringtypeconfig.VehicleID',
            'springtype_front' => 'LEFT JOIN springtypeconfig ON vehicletospringtypeconfig.SpringTypeConfigID = springtypeconfig.SpringTypeConfigID',
            'springtype' => 'LEFT JOIN springtype springtype_front ON springtypeconfig.FrontSpringTypeID = springtype_front.SpringTypeID',
            'springtype_rear' => 'LEFT JOIN springtype springtype_rear ON springtypeconfig.RearSpringTypeID = springtype_rear.SpringTypeID',
            'vehicletosteeringconfig' => 'LEFT JOIN vehicletosteeringconfig ON vehicle.VehicleID = vehicletosteeringconfig.VehicleID',
            'steeringconfig' => 'LEFT JOIN steeringconfig ON vehicletosteeringconfig.SteeringConfigID = steeringconfig.SteeringConfigID',
            'steeringtype' => 'LEFT JOIN steeringtype ON steeringconfig.SteeringTypeID = steeringtype.SteeringTypeID',
            'steeringsystem' => 'LEFT JOIN steeringsystem ON steeringconfig.SteeringSystemID = steeringsystem.SteeringSystemID',
            'vehicletobedconfig' => 'LEFT JOIN vehicletobedconfig ON vehicle.VehicleID = vehicletobedconfig.VehicleID',
            'bedconfig' => 'LEFT JOIN bedconfig ON vehicletobedconfig.BedConfigID = bedconfig.BedConfigID',
            'bedtype' => 'LEFT JOIN bedtype ON bedconfig.BedTypeID = bedtype.BedTypeID',
            'bedlength' => 'LEFT JOIN bedlength ON bedconfig.BedLengthID = bedlength.BedLengthID',
            'vehicletobrakeconfig' => 'LEFT JOIN vehicletobrakeconfig ON vehicle.VehicleID = vehicletobrakeconfig.VehicleID',
            'braketype_front' => 'LEFT JOIN brakeconfig ON vehicletobrakeconfig.BrakeConfigID = brakeconfig.BrakeConfigID',
            'braketype' => 'LEFT JOIN braketype braketype_front ON brakeconfig.FrontBrakeTypeID = braketype_front.BrakeTypeID',
            'braketype_rear' => 'LEFT JOIN braketype braketype_rear ON brakeconfig.RearBrakeTypeID = braketype_rear.BrakeTypeID',
            'brakeabs' => 'LEFT JOIN brakeabs ON brakeconfig.BrakeABSID = brakeabs.BrakeABSID',
            'brakesystem' => 'LEFT JOIN brakesystem ON brakeconfig.BrakeSystemID = brakesystem.BrakeSystemID',
            'vehicletodrivetype' => 'LEFT JOIN vehicletodrivetype ON vehicle.VehicleID = vehicletodrivetype.VehicleID',
            'drivetype' => 'LEFT JOIN drivetype ON vehicletodrivetype.DriveTypeID = drivetype.DriveTypeID',
            'vehicletowheelbase' => 'LEFT JOIN vehicletowheelbase ON vehicle.VehicleID = vehicletowheelbase.VehicleID',
            'wheelbase' => 'LEFT JOIN wheelbase ON vehicletowheelbase.WheelbaseID = wheelbase.WheelBaseID',
            'vehicletobodystyleconfig' => 'LEFT JOIN vehicletobodystyleconfig ON vehicle.VehicleID = vehicletobodystyleconfig.VehicleID',
            'bodystyleconfig' => 'LEFT JOIN bodystyleconfig ON vehicletobodystyleconfig.BodyStyleConfigID = bodystyleconfig.BodyStyleConfigID',
            'bodynumdoors' => 'LEFT JOIN bodynumdoors ON bodystyleconfig.BodyNumDoorsID = bodynumdoors.BodyNumDoorsID',
            'bodytype' => 'LEFT JOIN bodytype ON bodystyleconfig.BodyTypeID = bodytype.BodyTypeID',
            'vehicletomfrbodycode' => 'LEFT JOIN vehicletomfrbodycode ON vehicle.VehicleID = vehicletomfrbodycode.VehicleID',
            'mfrbodycode' => 'LEFT JOIN mfrbodycode ON vehicletomfrbodycode.MfrBodyCodeID = mfrbodycode.MfrBodyCodeID',
            'vehicletotransmission' => 'LEFT JOIN vehicletotransmission ON vehicle.VehicleID = vehicletotransmission.VehicleID',
            'transmission' => 'LEFT JOIN transmission ON vehicletotransmission.TransmissionID = transmission.TransmissionID',
            'eleccontrolled' => 'LEFT JOIN eleccontrolled ON transmission.TransmissionElecControlledID = eleccontrolled.ElecControlledID',
            'transmissionbase' => 'LEFT JOIN transmissionbase ON transmission.TransmissionBaseID = transmissionbase.TransmissionBaseID',
            'transmissiontype' => 'LEFT JOIN transmissiontype ON transmissionbase.TransmissionTypeID = transmissiontype.TransmissionTypeID',
            'transmissioncontroltype' => 'LEFT JOIN transmissioncontroltype ON transmissionbase.TransmissionControlTypeID = transmissioncontroltype.TransmissionControlTypeID',
            'transmissionnumspeeds' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr_transmission' => 'LEFT JOIN transmissionnumspeeds ON transmissionbase.TransmissionNumSpeedsID = transmissionnumspeeds.TransmissionNumSpeedsID',
            'mfr' => 'LEFT JOIN mfr ON transmission.TransmissionMfrID = mfr.MfrID',
            'transmissionmfrcode' => 'LEFT JOIN transmissionmfrcode ON transmission.TransmissionMfrCodeID = transmissionmfrcode.TransmissionMfrCodeID',
            'vehicletoengineconfig' => 'LEFT JOIN vehicletoengineconfig ON vehicle.VehicleID = vehicletoengineconfig.VehicleID',
            'engineconfig' => 'LEFT JOIN engineconfig ON vehicletoengineconfig.EngineConfigID = engineconfig.EngineConfigID',
            'ignitionsystemtype' => 'LEFT JOIN ignitionsystemtype ON engineconfig.IgnitionSystemTypeID = ignitionsystemtype.IgnitionSystemTypeID',
            'cylinderheadtype' => 'LEFT JOIN cylinderheadtype ON engineconfig.CylinderHeadTypeID = cylinderheadtype.CylinderHeadTypeID',
            'aspiration' => 'LEFT JOIN aspiration ON engineconfig.AspirationID = aspiration.AspirationID',
            'fueltype' => "LEFT JOIN $fueltype_table ON engineconfig.FuelTypeID = fueltype.FuelTypeID",
            'valves' => 'LEFT JOIN valves ON engineconfig.ValvesID = valves.ValvesID',
            'enginevin' => 'LEFT JOIN enginevin ON engineconfig.EngineVINID = enginevin.EngineVINID',
            'mfr_engine' => 'LEFT JOIN engineversion ON engineconfig.EngineVersionID = engineversion.EngineVersionID',
            'engine_mfr' => 'LEFT JOIN mfr  ON engineconfig.EngineMfrID = mfr.MfrID',
            'fueldeliveryconfig' => 'LEFT JOIN fueldeliveryconfig ON engineconfig.FuelDeliveryConfigID = fueldeliveryconfig.FuelDeliveryConfigID',
            'fueldeliverytype' => 'LEFT JOIN fueldeliverytype ON fueldeliveryconfig.FuelDeliveryTypeID = fueldeliverytype.FuelDeliveryTypeID',
            'fuelsystemcontroltype' => 'LEFT JOIN fuelsystemcontroltype ON fueldeliveryconfig.FuelSystemControlTypeID = fuelsystemcontroltype.FuelSystemControlTypeID',
            'fueldeliverysubtype' => 'LEFT JOIN fueldeliverysubtype ON fueldeliveryconfig.FuelDeliverySubTypeID = fueldeliverysubtype.FuelDeliverySubTypeID',
            'fuelsystemdesign' => 'LEFT JOIN fuelsystemdesign ON fueldeliveryconfig.FuelSystemDesignID = fuelsystemdesign.FuelSystemDesignID',
            'enginedesignation' => 'LEFT JOIN enginedesignation ON engineconfig.EngineDesignationID = enginedesignation.EngineDesignationID',
            'enginebase' => 'LEFT JOIN enginebase ON engineconfig.EngineBaseID = enginebase.EngineBaseID'
            
        ];


        $vcdb_tables_per_qualifier = [
            'AspirationID' => ['vehicletoengineconfig','engineconfig','aspiration'],
            'BedLengthID' => ['vehicletobedconfig','bedconfig','bedlength'],
            'BedTypeID' => ['vehicletobedconfig','bedconfig','bedtype'],
            'BodyNumDoorsID' => ['vehicletobodystyleconfig','bodystyleconfig','bodynumdoors'],
            'BodyTypeID' => ['vehicletobodystyleconfig','bodystyleconfig','bodytype'],
            'BrakeABSID' => ['vehicletobrakeconfig','braketype_front','brakeabs'],
            'BrakeSystemID' => ['vehicletobrakeconfig','braketype_front','brakesystem'],
            'CylinderHeadTypeID' => ['vehicletoengineconfig','engineconfig','cylinderheadtype'],
            'DriveTypeID' => ['vehicletodrivetype','drivetype'],
            'EngineDesignationID' => ['vehicletoengineconfig','engineconfig','enginedesignation'],
            'EngineMfrID' => ['vehicletoengineconfig','engineconfig','engine_mfr'],//actual table name is mfr only
            'EngineVersionID' => ['vehicletoengineconfig','engineconfig','mfr_engine'],
            'EngineVINID' => ['vehicletoengineconfig','engineconfig','enginevin'],
            'FrontBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype'],
            'FrontSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype'],
            'FuelDeliverySubTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverysubtype'],
            'FuelDeliveryTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fueldeliverytype'],
            'FuelSystemControlTypeID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemcontroltype'],
            'FuelSystemDesignID' => ['vehicletoengineconfig','engineconfig','fueldeliveryconfig','fuelsystemdesign'],
            'FuelTypeID' => ['vehicletoengineconfig','engineconfig','fueltype'],
            'IgnitionSystemTypeID' => ['vehicletoengineconfig','engineconfig','ignitionsystemtype'],
            'MfrBodyCodeID' => ['vehicletomfrbodycode','mfrbodycode'],
            //We don't use position as it's a part specific qualifier not vehicle specific
            //'PositionID' => ['positions'],
            'PowerOutputID' => ['vehicletoengineconfig','engineconfig','poweroutput'],
            'RearBrakeTypeID' => ['vehicletobrakeconfig','braketype_front','braketype_rear'],
            'RearSpringTypeID' => ['vehicletospringtypeconfig','springtypeconfig','springtype_front','springtype_rear'],
            'RegionID' => ['region'],
            'SteeringSystemID' => ['vehicletosteeringconfig','steeringconfig','steeringsystem'],
            'SteeringTypeID' => ['vehicletosteeringconfig','steeringconfig','steeringtype'],
            'SubModelID' => ['submodel'],
            'TransElecControlledID' => ['vehicletotransmission','transmission','eleccontrolled'],
            'TransmissionControlTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissioncontroltype'],
            'TransmissionMfrCodeID' => ['vehicletotransmission','transmission','transmissionmfrcode'],
            'TransmissionMfrID' => ['vehicletotransmission','transmission','mfr'],
            'TransmissionNumSpeedsID' => ['vehicletotransmission','transmission','transmissionbase','transmissionnumspeeds'],
            'TransmissionTypeID' => ['vehicletotransmission','transmission','transmissionbase','transmissiontype'],
            'ValvesPerEngineID' => ['vehicletoengineconfig','engineconfig','valves'],
            'WheelBaseID' => ['vehicletowheelbase','wheelbase']
        ];

        //Example questions (get the real one's off of John):
        //Accompanied by an optional graphic (like the one I sent in a Word document previously):

        $question_graphics = [
            'EngineVINID' => '/path/to/a/graphic',
        ];

        //We don't use position as it's a part specific qualifier not vehicle specific
        //Strings contains both single and double quotes so we use heredoc syntax.
        $vcdb_selects_per_qualifier['AspirationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',aspiration.AspirationID,'":{"Value":"',TRIM(aspiration.AspirationName),'","IsQualifierInDM":',IF(aspiration.AspirationID [CONDITION],'true','false'),'}') ORDER BY aspiration.AspirationName SEPARATOR ','),'}') AS AspirationID
        EOT;
        $vcdb_selects_per_qualifier['BedLengthID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedlength.BedLengthID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(bedlength.BedLength),'in'),CONCAT(TRIM(bedlength.BedLengthMetric),'cm')),'","IsQualifierInDM":',IF(bedlength.BedLengthID [CONDITION],'true','false'),'}') ORDER BY bedlength.BedLength,BedLengthMetric SEPARATOR ','),'}') AS BedLengthID
        EOT;

        $vcdb_selects_per_qualifier['BedTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bedtype.BedTypeID,'":{"Value":"',TRIM(bedtype.BedTypeName),'","IsQualifierInDM":',IF(bedtype.BedTypeID [CONDITION],'true','false'),'}') ORDER BY bedtype.BedTypeName SEPARATOR ','),'}') AS BedTypeID
        EOT;

        $vcdb_selects_per_qualifier['BodyNumDoorsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodynumdoors.BodyNumDoorsID,'":{"Value":"',TRIM(bodynumdoors.BodyNumDoors),'","IsQualifierInDM":',IF(bodynumdoors.BodyNumDoorsID [CONDITION],'true','false'),'}') ORDER BY bodynumdoors.BodyNumDoors SEPARATOR ','),'}') AS BodyNumDoorsID
        EOT;
        $vcdb_selects_per_qualifier['BodyTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',bodytype.BodyTypeID,'":{"Value":"',TRIM(bodytype.BodyTypeName),'","IsQualifierInDM":',IF(bodytype.BodyTypeID [CONDITION],'true','false'),'}') ORDER BY bodytype.BodyTypeName SEPARATOR ','),'}') AS BodyTypeID
        EOT;
        $vcdb_selects_per_qualifier['BrakeABSID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakeabs.BrakeABSID,'":{"Value":"',TRIM(brakeabs.BrakeABSName),'","IsQualifierInDM":',IF(brakeabs.BrakeABSID [CONDITION],'true','false'),'}') ORDER BY brakeabs.BrakeABSName SEPARATOR ','),'}') AS BrakeABSID
        EOT;
        $vcdb_selects_per_qualifier['BrakeSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',brakesystem.BrakeSystemID,'":{"Value":"',TRIM(brakesystem.BrakeSystemName),'","IsQualifierInDM":',IF(brakesystem.BrakeSystemID [CONDITION],'true','false'),'}') ORDER BY brakesystem.BrakeSystemName SEPARATOR ','),'}') AS BrakeSystemID
        EOT;
        $vcdb_selects_per_qualifier['CylinderHeadTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',cylinderheadtype.CylinderHeadTypeID,'":{"Value":"',TRIM(cylinderheadtype.CylinderHeadTypeName),'","IsQualifierInDM":',IF(cylinderheadtype.CylinderHeadTypeID [CONDITION],'true','false'),'}') ORDER BY cylinderheadtype.CylinderHeadTypeName SEPARATOR ','),'}') AS CylinderHeadTypeID
        EOT;
        $vcdb_selects_per_qualifier['DriveTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',drivetype.DriveTypeID,'":{"Value":"',TRIM(drivetype.DriveTypeName),'","IsQualifierInDM":',IF(drivetype.DriveTypeID [CONDITION],'true','false'),'}') ORDER BY drivetype.DriveTypeName SEPARATOR ','),'}') AS DriveTypeID
        EOT;
        $vcdb_selects_per_qualifier['EngineDesignationID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginedesignation.EngineDesignationID,'":{"Value":"',TRIM(enginedesignation.EngineDesignationName),'","IsQualifierInDM":',IF(enginedesignation.EngineDesignationID [CONDITION],'true','false'),'}') ORDER BY enginedesignation.EngineDesignationName SEPARATOR ','),'}') AS EngineDesignationID
        EOT;
        $vcdb_selects_per_qualifier['EngineMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS EngineMfrID
        EOT;
        $vcdb_selects_per_qualifier['EngineVersionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',engineversion.EngineVersionID,'":{"Value":"',TRIM(engineversion.EngineVersion),'","IsQualifierInDM":',IF(engineversion.EngineVersionID [CONDITION],'true','false'),'}') ORDER BY engineversion.EngineVersion SEPARATOR ','),'}') AS EngineVersionID
        EOT;
        $vcdb_selects_per_qualifier['EngineVINID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',enginevin.EngineVINID,'":{"Value":"',TRIM(enginevin.EngineVINName),'","IsQualifierInDM":',IF(enginevin.EngineVINID [CONDITION],'true','false'),'}') ORDER BY enginevin.EngineVINName SEPARATOR ','),'}') AS EngineVINID
        EOT;
        $vcdb_selects_per_qualifier['FrontBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_front.BrakeTypeID,'":{"Value":"',TRIM(braketype_front.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_front.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_front.BrakeTypeName SEPARATOR ','),'}') AS FrontBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['FrontSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_front.SpringTypeID,'":{"Value":"',TRIM(springtype_front.SpringTypeName),'","IsQualifierInDM":',IF(springtype_front.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_front.SpringTypeName SEPARATOR ','),'}') AS FrontSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliverySubTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverysubtype.FuelDeliverySubTypeID,'":{"Value":"',TRIM(fueldeliverysubtype.FuelDeliverySubTypeName),'","IsQualifierInDM":',IF(fueldeliverysubtype.FuelDeliverySubTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverysubtype.FuelDeliverySubTypeName SEPARATOR ','),'}') AS FuelDeliverySubTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelDeliveryTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueldeliverytype.FuelDeliveryTypeID,'":{"Value":"',TRIM(fueldeliverytype.FuelDeliveryTypeName),'","IsQualifierInDM":',IF(fueldeliverytype.FuelDeliveryTypeID [CONDITION],'true','false'),'}') ORDER BY fueldeliverytype.FuelDeliveryTypeName SEPARATOR ','),'}') AS FuelDeliveryTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemcontroltype.FuelSystemControlTypeID,'":{"Value":"',TRIM(fuelsystemcontroltype.FuelSystemControlTypeName),'","IsQualifierInDM":',IF(fuelsystemcontroltype.FuelSystemControlTypeID [CONDITION],'true','false'),'}') ORDER BY fuelsystemcontroltype.FuelSystemControlTypeName SEPARATOR ','),'}') AS FuelSystemControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['FuelSystemDesignID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fuelsystemdesign.FuelSystemDesignID,'":{"Value":"',TRIM(fuelsystemdesign.FuelSystemDesignName),'","IsQualifierInDM":',IF(fuelsystemdesign.FuelSystemDesignID [CONDITION],'true','false'),'}') ORDER BY fuelsystemdesign.FuelSystemDesignName SEPARATOR ','),'}') AS FuelSystemDesignID
        EOT;
        $vcdb_selects_per_qualifier['FuelTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',fueltype.FuelTypeID,'":{"Value":"',TRIM(fueltype.FuelTypeName),'","IsQualifierInDM":',IF(fueltype.FuelTypeID [CONDITION],'true','false'),'}') ORDER BY fueltype.FuelTypeName SEPARATOR ','),'}') AS FuelTypeID
        EOT;
        $vcdb_selects_per_qualifier['IgnitionSystemTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',ignitionsystemtype.IgnitionSystemTypeID,'":{"Value":"',TRIM(ignitionsystemtype.IgnitionSystemTypeName),'","IsQualifierInDM":',IF(ignitionsystemtype.IgnitionSystemTypeID [CONDITION],'true','false'),'}') ORDER BY ignitionsystemtype.IgnitionSystemTypeName SEPARATOR ','),'}') AS IgnitionSystemTypeID
        EOT;
        $vcdb_selects_per_qualifier['MfrBodyCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfrbodycode.MfrBodyCodeID,'":{"Value":"',TRIM(mfrbodycode.MfrBodyCodeName),'","IsQualifierInDM":',IF(mfrbodycode.MfrBodyCodeID [CONDITION],'true','false'),'}') ORDER BY mfrbodycode.MfrBodyCodeName SEPARATOR ','),'}') AS MfrBodyCodeID
        EOT;
        $vcdb_selects_per_qualifier['PositionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',positions.PositionID,'":{"Value":"',TRIM(positions.Position),'","IsQualifierInDM":',IF(positions.PositionID [CONDITION],'true','false'),'}') ORDER BY positions.Position SEPARATOR ','),'}') AS PositionID
        EOT;
        $vcdb_selects_per_qualifier['PowerOutputID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',poweroutput.PowerOutputID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(poweroutput.HorserPower),'HP'),CONCAT(TRIM(poweroutput.KilowattPower),'kW')),'","IsQualifierInDM":',IF(poweroutput.PowerOutputID [CONDITION],'true','false'),'}') ORDER BY poweroutput.HorsePower,KilowattPower SEPARATOR ','),'}') AS PowerOutputID
        EOT;
        $vcdb_selects_per_qualifier['RearBrakeTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',braketype_rear.BrakeTypeID,'":{"Value":"',TRIM(braketype_rear.BrakeTypeName),'","IsQualifierInDM":',IF(braketype_rear.BrakeTypeID [CONDITION],'true','false'),'}') ORDER BY braketype_rear.BrakeTypeName SEPARATOR ','),'}') AS RearBrakeTypeID
        EOT;
        $vcdb_selects_per_qualifier['RearSpringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',springtype_rear.SpringTypeID,'":{"Value":"',TRIM(springtype_rear.SpringTypeName),'","IsQualifierInDM":',IF(springtype_rear.SpringTypeID [CONDITION],'true','false'),'}') ORDER BY springtype_rear.SpringTypeName SEPARATOR ','),'}') AS RearSpringTypeID
        EOT;
        $vcdb_selects_per_qualifier['RegionID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',region.RegionID,'":{"Value":"',TRIM(region.RegionName),'","IsQualifierInDM":',IF(region.RegionID [CONDITION],'true','false'),'}') ORDER BY region.RegionAbbr,RegionName SEPARATOR ','),'}') AS RegionID
        EOT;
        $vcdb_selects_per_qualifier['SteeringSystemID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringsystem.SteeringSystemID,'":{"Value":"',TRIM(steeringsystem.SteeringSystemName),'","IsQualifierInDM":',IF(steeringsystem.SteeringSystemID [CONDITION],'true','false'),'}') ORDER BY steeringsystem.SteeringSystemName SEPARATOR ','),'}') AS SteeringSystemID
        EOT;
        $vcdb_selects_per_qualifier['SteeringTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',steeringtype.SteeringTypeID,'":{"Value":"',TRIM(steeringtype.SteeringTypeName),'","IsQualifierInDM":',IF(steeringtype.SteeringTypeID [CONDITION],'true','false'),'}') ORDER BY steeringtype.SteeringTypeName SEPARATOR ','),'}') AS SteeringTypeID
        EOT;
        $vcdb_selects_per_qualifier['SubModelID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',submodel.SubModelID,'":{"Value":"',TRIM(submodel.SubModelName),'","IsQualifierInDM":',IF(submodel.SubModelID [CONDITION],'true','false'),'}') ORDER BY submodel.SubModelName SEPARATOR ','),'}') AS SubModelID
        EOT;
        $vcdb_selects_per_qualifier['TransElecControlledID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',eleccontrolled.ElecControlledID,'":{"Value":"',TRIM(eleccontrolled.ElecControlled),'","IsQualifierInDM":',IF(eleccontrolled.ElecControlledID [CONDITION],'true','false'),'}') ORDER BY eleccontrolled.ElecControlled SEPARATOR ','),'}') AS TransElecControlledID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionControlTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissioncontroltype.TransmissionControlTypeID,'":{"Value":"',TRIM(transmissioncontroltype.TransmissionControlTypeName),'","IsQualifierInDM":',IF(transmissioncontroltype.TransmissionControlTypeID [CONDITION],'true','false'),'}') ORDER BY transmissioncontroltype.TransmissionControlTypeName SEPARATOR ','),'}') AS TransmissionControlTypeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrCodeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionmfrcode.TransmissionMfrCodeID,'":{"Value":"',TRIM(transmissionmfrcode.TransmissionMfrCode),'","IsQualifierInDM":',IF(transmissionmfrcode.TransmissionMfrCodeID [CONDITION],'true','false'),'}') ORDER BY transmissionmfrcode.TransmissionMfrCode SEPARATOR ','),'}') AS TransmissionMfrCodeID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionMfrID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',mfr.MfrID,'":{"Value":"',TRIM(mfr.MfrName),'","IsQualifierInDM":',IF(mfr.MfrID [CONDITION],'true','false'),'}') ORDER BY mfr.MfrName SEPARATOR ','),'}') AS TransmissionMfrID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionNumSpeedsID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissionnumspeeds.TransmissionNumSpeedsID,'":{"Value":"',TRIM(transmissionnumspeeds.TransmissionNumSpeeds),'","IsQualifierInDM":',IF(transmissionnumspeeds.TransmissionNumSpeedsID [CONDITION],'true','false'),'}') ORDER BY transmissionnumspeeds.TransmissionNumSpeeds SEPARATOR ','),'}') AS TransmissionNumSpeedsID
        EOT;
        $vcdb_selects_per_qualifier['TransmissionTypeID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',transmissiontype.TransmissionTypeID,'":{"Value":"',TRIM(transmissiontype.TransmissionTypeName),'","IsQualifierInDM":',IF(transmissiontype.TransmissionTypeID [CONDITION],'true','false'),'}') ORDER BY transmissiontype.TransmissionTypeName SEPARATOR ','),'}') AS TransmissionTypeID
        EOT;
        $vcdb_selects_per_qualifier['ValvesPerEngineID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',valves.ValvesID,'":{"Value":"',TRIM(valves.ValvesPerEngine),'","IsQualifierInDM":',IF(valves.ValvesID [CONDITION],'true','false'),'}') ORDER BY valves.ValvesPerEngine SEPARATOR ','),'}') AS ValvesPerEngineID
        EOT;
        $vcdb_selects_per_qualifier['WheelBaseID']=<<<EOT
        CONCAT('{',GROUP_CONCAT(DISTINCT CONCAT('"',wheelbase.WheelBaseID,'":{"Value":"',CONCAT_WS(', ',CONCAT(TRIM(wheelbase.WheelBase),'in'),CONCAT(TRIM(wheelbase.WheelBaseMetric),'cm')),'","IsQualifierInDM":',IF(wheelbase.WheelBaseID [CONDITION],'true','false'),'}') ORDER BY wheelbase.WheelBase,WheelBaseMetric SEPARATOR ','),'}') AS WheelBaseID
        EOT;


        $vehicle_definition = [
            'VehicleID' => 0,
            'BaseVehicleID' => 0,
            'YearID' => 0,
            'MakeID' => 0,
            'ModelID' => 0,
            'EngineConfigID' => 0,
            'EngineBaseID' => 0,
            
            /*The above are vehicle identifiers - typically a VehicleID or BaseVehicleID will be given
              or year, make, model (for which the basevehicle table will be needed)
              and one of EngineConfigID or EngineBaseID is needed.
              If the API's XML only returns values then more tables, e.g. make, model, will be needed.
             */    
              'AspirationID' => "Aspiration",
              'BedLengthID' => "Bed Length",
              'BedTypeID' => "Bed Type",
              'BodyNumDoorsID' => "Number of Doors",
              'BodyTypeID' => "Body Type",
              'BrakeABSID' => "ABS Type",
              'BrakeSystemID' => "Brake System",
              'CylinderHeadTypeID' => "Cylinder Head",
              'DriveTypeID' => "Drive Type",
              'EngineDesignationID' => "Engine Design",
              'EngineMfrID' => "Engine Mfr",
              'EngineVersionID' => "Engine Version",
              'EngineVINID' => "VIN",
              'FrontBrakeTypeID' => "Front Brake",
              'FrontSpringTypeID' => "Front Spring",
              'FuelDeliverySubTypeID' => "Fuel Del. Sub Type",
              'FuelDeliveryTypeID' => "Fuel Del. Type",
              'FuelSystemControlTypeID' => "Fuel Sys. Control",
              'FuelSystemDesignID' => "Fuel Sys. Design",
              'FuelTypeID' => "Fuel",
              'IgnitionSystemTypeID' => "Ignition",
              'MfrBodyCodeID' => "Body Code",
              'PositionID' => 0,
              'PowerOutputID' => "Power Output",
              'RearBrakeTypeID' => "Rear Brake",
              'RearSpringTypeID' => "Rear Spring",
              'RegionID' => "Region",
              'SteeringSystemID' => "Steering System",
              'SteeringTypeID' => "Steering",
              'SubModelID' => "SubModel",
              'TransElecControlledID' => "Trans Elec Ctrl",
              'TransmissionControlTypeID' => "Trans Ctrl Type",
              'TransmissionMfrCodeID' => "Trans Mfr Code",
              'TransmissionMfrID' => "Transmission Mfr",
              'TransmissionNumSpeedsID' => "Trans Num Speeds",
              'TransmissionTypeID' => "Trans Type",
              'ValvesPerEngineID' => "Valves Per Engine",
              'WheelBaseID' => "Wheel Base",
        
        ];
        
        $data_qualifiers_select = <<<EOT
        SELECT CONCAT('{',NULLIF(CONCAT_WS(',',CONCAT('"FuelTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelTypeID,0) ORDER BY IFNULL(a.FuelTypeID,0) SEPARATOR ','),']'),CONCAT('"AspirationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.AspirationID,0) ORDER BY IFNULL(a.AspirationID,0) SEPARATOR ','),']'),CONCAT('"DriveTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.DriveTypeID,0) ORDER BY IFNULL(a.DriveTypeID,0) SEPARATOR ','),']'),CONCAT('"EngineVINID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVINID,0) ORDER BY IFNULL(a.EngineVINID,0) SEPARATOR ','),']'),CONCAT('"EngineDesignationID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineDesignationID,0) ORDER BY IFNULL(a.EngineDesignationID,0) SEPARATOR ','),']'),CONCAT('"CylinderHeadTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.CylinderHeadTypeID,0) ORDER BY IFNULL(a.CylinderHeadTypeID,0) SEPARATOR ','),']'),CONCAT('"SubModelID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SubModelID,0) ORDER BY IFNULL(a.SubModelID,0) SEPARATOR ','),']'),CONCAT('"TransmissionControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionControlTypeID,0) ORDER BY IFNULL(a.TransmissionControlTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionNumSpeedsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionNumSpeedsID,0) ORDER BY IFNULL(a.TransmissionNumSpeedsID,0) SEPARATOR ','),']'),CONCAT('"ValvesPerEngineID":[',GROUP_CONCAT(DISTINCT IFNULL(a.ValvesPerEngineID,0) ORDER BY IFNULL(a.ValvesPerEngineID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliveryTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliveryTypeID,0) ORDER BY IFNULL(a.FuelDeliveryTypeID,0) SEPARATOR ','),']'),CONCAT('"BedTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedTypeID,0) ORDER BY IFNULL(a.BedTypeID,0) SEPARATOR ','),']'),CONCAT('"BodyNumDoorsID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyNumDoorsID,0) ORDER BY IFNULL(a.BodyNumDoorsID,0) SEPARATOR ','),']'),CONCAT('"EngineMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineMfrID,0) ORDER BY IFNULL(a.EngineMfrID,0) SEPARATOR ','),']'),CONCAT('"FuelDeliverySubTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelDeliverySubTypeID,0) ORDER BY IFNULL(a.FuelDeliverySubTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemControlTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemControlTypeID,0) ORDER BY IFNULL(a.FuelSystemControlTypeID,0) SEPARATOR ','),']'),CONCAT('"FuelSystemDesignID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FuelSystemDesignID,0) ORDER BY IFNULL(a.FuelSystemDesignID,0) SEPARATOR ','),']'),CONCAT('"SteeringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringTypeID,0) ORDER BY IFNULL(a.SteeringTypeID,0) SEPARATOR ','),']'),CONCAT('"TransmissionTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionTypeID,0) ORDER BY IFNULL(a.TransmissionTypeID,0) SEPARATOR ','),']'),CONCAT('"BedLengthID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BedLengthID,0) ORDER BY IFNULL(a.BedLengthID,0) SEPARATOR ','),']'),CONCAT('"BodyTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BodyTypeID,0) ORDER BY IFNULL(a.BodyTypeID,0) SEPARATOR ','),']'),CONCAT('"BrakeABSID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeABSID,0) ORDER BY IFNULL(a.BrakeABSID,0) SEPARATOR ','),']'),CONCAT('"BrakeSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.BrakeSystemID,0) ORDER BY IFNULL(a.BrakeSystemID,0) SEPARATOR ','),']'),CONCAT('"EngineVersionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.EngineVersionID,0) ORDER BY IFNULL(a.EngineVersionID,0) SEPARATOR ','),']'),CONCAT('"FrontBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontBrakeTypeID,0) ORDER BY IFNULL(a.FrontBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"FrontSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.FrontSpringTypeID,0) ORDER BY IFNULL(a.FrontSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"IgnitionSystemTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.IgnitionSystemTypeID,0) ORDER BY IFNULL(a.IgnitionSystemTypeID,0) SEPARATOR ','),']'),CONCAT('"MfrBodyCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.MfrBodyCodeID,0) ORDER BY IFNULL(a.MfrBodyCodeID,0) SEPARATOR ','),']'),CONCAT('"PowerOutputID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PowerOutputID,0) ORDER BY IFNULL(a.PowerOutputID,0) SEPARATOR ','),']'),CONCAT('"RearBrakeTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearBrakeTypeID,0) ORDER BY IFNULL(a.RearBrakeTypeID,0) SEPARATOR ','),']'),CONCAT('"RearSpringTypeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RearSpringTypeID,0) ORDER BY IFNULL(a.RearSpringTypeID,0) SEPARATOR ','),']'),CONCAT('"RegionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.RegionID,0) ORDER BY IFNULL(a.RegionID,0) SEPARATOR ','),']'),CONCAT('"SteeringSystemID":[',GROUP_CONCAT(DISTINCT IFNULL(a.SteeringSystemID,0) ORDER BY IFNULL(a.SteeringSystemID,0) SEPARATOR ','),']'),CONCAT('"TransElecControlledID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransElecControlledID,0) ORDER BY IFNULL(a.TransElecControlledID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrID,0) ORDER BY IFNULL(a.TransmissionMfrID,0) SEPARATOR ','),']'),CONCAT('"TransmissionMfrCodeID":[',GROUP_CONCAT(DISTINCT IFNULL(a.TransmissionMfrCodeID,0) ORDER BY IFNULL(a.TransmissionMfrCodeID,0) SEPARATOR ','),']'),CONCAT('"WheelBaseID":[',GROUP_CONCAT(DISTINCT IFNULL(a.WheelBaseID,0) ORDER BY IFNULL(a.WheelBaseID,0) SEPARATOR ','),']'),CONCAT('"PositionID":[',GROUP_CONCAT(DISTINCT IFNULL(a.PositionID,0) ORDER BY IFNULL(a.PositionID,0) SEPARATOR ','),']')),''),'}') AS data_json
        EOT;
     
    
        //If partterminologyID is not equal to 0
        if($parttype!=0) {

            $send_parttype = "";
            

            $explode_part=explode(",",$parttype);

            if(count($explode_part)>1) {
                foreach ($explode_part as $ex) {
                    $or_clause[]= "pi.PartTerminologyID=".$ex;
                }
                $send_parttype=implode(" OR ",$or_clause);
            }
            else {
                $send_parttype = "pi.PartTerminologyID=".$explode_part[0];
            }

            $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId) AND ($send_parttype)");

        }
        else {
            $results= DB::connection(self::$main_db)->select($data_qualifiers_select ." FROM applications a JOIN partNumberInfo pi ON a.part_id=pi.part_id WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)");
        }

        $a=$results[0];
        $get_data_json=$a->data_json;

        $f="";

        if($get_data_json==null) {
            $f="fail";
            return json_encode($f);
        }
        else {
            $vcdb_tables = [];
            $vcdb_selects = [];
            $replace_count = 1;
            $result=[];

            $get_data_json=json_decode($get_data_json,true);

            foreach ($get_data_json as $qualifier_type => $qualifier_ids)  {
                if($qualifier_type != 'PositionID') {

                    if($qualifier_ids!=NULL) {
                        $result = array_diff($qualifier_ids, [0]);
                    }

                    if(count($result) >= 1 || $qualifier_ids[0] != 0) {

                        if(!empty($vehicle_definition[$qualifier_type])) {

                            $vcdb_tables = array_merge($vcdb_tables,$vcdb_tables_per_qualifier[$qualifier_type]);
                            $original_select = $vcdb_selects_per_qualifier[$qualifier_type];

                            if($qualifier_ids[0] == 0) {
                                //Any criteria will do here so as IDs can't be negative this will always return true
                                $final_select = str_replace('[CONDITION]','!= -1000',$original_select,$replace_count);

                            }
                            else {
                                $final_select = str_replace('[CONDITION]',('IN(' . implode(',', $qualifier_ids) . ')'),$original_select,$replace_count);
                            }
                            $vcdb_selects[] = $final_select;

                        }
                    }

                }
            }//foreach

            if(count($vcdb_tables) == 0) {
                $f="fail";
                return json_encode($f);
            }
            else {
                $vcdb_tables[] = 'vehicletoengineconfig';
                $vcdb_tables[] = 'engineconfig';

                $vcdb_tables_unique = array_unique($vcdb_tables);
                $vcdb_qualifiers_select = 'SELECT ' . implode(', ',$vcdb_selects) . ' FROM vehicle';

                foreach($vcdb_tables_unique as $table) {
                    $vcdb_qualifiers_select = $vcdb_qualifiers_select . ' ' . $vcdb_table_joins[$table];
                }

                $vcdb_qualifiers_select= DB::connection(self::$aces_vcdb)->select($vcdb_qualifiers_select . ' WHERE vehicle.BaseVehicleID ='.$baseVehicleId.'  AND engineconfig.EngineBaseID IN('.$engineId.')'.$qualifierlist);
                  
            }
            

            //get total qualifier count
            // $get_all_count = count($vcdb_qualifiers_select);
           
            if(empty($vcdb_qualifiers_select)) {
                $f="fail";
                return json_encode($f);
            }
            else {

                $get_value_of_q["values"]=[];
                $get_value_of_q["name"]=[];
                $get_value_of_q["showdropdown"]=[];

                for($i=0;$i<1;$i++) {

                    foreach($vcdb_qualifiers_select[$i] as $qualifier_type => $qualifier_type_json) {

                        $matched_value=0;
                        $stop_process=0;

                        foreach($store_upcoming as $store_upcoming1) {
                            $ex=explode(":",$store_upcoming1);
                            if($ex[0]==$qualifier_type) {
                                $matched_value=1;
                            }
                        }

                        if($matched_value==0) {
                           
                            $qualifier_type_json_array = json_decode($qualifier_type_json,true);
                            $qualifier_name=$vehicle_definition[$qualifier_type];
                            array_push($get_value_of_q["name"],$qualifier_name);


                            $qualifierlist="";
                            $result = [];

                            $show = 'true';
                    
                            $qual_count = count($qualifier_type_json_array);

                           
                            //check count of partnumbers and its value for each qualifier value
                            foreach($qualifier_type_json_array as $qualifier_id => $qualifier_detail) {

                                $header="";
                                $header='{"id":"'.$qualifier_type.":".$qualifier_id.'","values":"'.$qualifier_detail['Value'].'"}';
                                $header=json_decode($header);
                                array_push($get_value_of_q["values"],$header);

                                
                                $qualifierlist = "";
                                $qualifierlist = " and (a.".$qualifier_type."=".$qualifier_id." OR a.".$qualifier_type." IS NULL)";
                                
                                $partTypeArray = DB::connection(self::$main_db)
                                    ->select("SELECT p.partNumber 
                                    FROM partNumberInfo p LEFT JOIN applications a ON p.part_id=a.part_id 
                                    WHERE a.BaseVehicleID=$baseVehicleId AND a.EngineBaseID IN($engineId)".$qualifierlist." 
                                    GROUP BY p.partNumber");


                                if(count($partTypeArray) > 0) {
                                    $final_result = collect($partTypeArray)->pluck('partNumber')->toArray();
                                    array_push($result,$final_result);
                                }
                                
                                
                            
                            }

                            
                            if(count($result)>0) {
                         
                                //if qualifier value is greater than one then show else do not show dropdown
                                if($qual_count>1) {
                                    // If partnumbers from first array are matching with other 2 arrays(all same) - Then do not show engine dropdown  
                                    if ($this->areArraysIdentical($result)) {
                                        $show = 'false';
                                    }
                                }
                                else {
                                    $show = 'false';
                                }

                            }


                            array_push($get_value_of_q["showdropdown"],$show);

                        }
                        else {
                            continue;
                        }

                        if($stop_process==0) {
                            break;
                        }

                        
                    }


                    

                }

                return $get_value_of_q;


                //return $newObject;
            }

        }

    }

}
