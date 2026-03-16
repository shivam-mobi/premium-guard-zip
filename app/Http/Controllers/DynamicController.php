<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Log;
use Config;
use SoapClient;
use stdClass;
use Exception;
use GuzzleHttp\Client;

class DynamicController extends Controller
{

    public static $aces_vcdb = 'mysql_aces';
    public static $main_db = 'mysql';
    protected $pg_db;

    public function __construct() {
        $this->pg_db = Config::get('constants.pg_dbname');
    }

    public function indexBK(Request $request,$searchterm)
    {
        $products = [];
        $search_length = strlen($searchterm);
        $partNumberId  = 'partnumber';
        $premiumId     = 'partintnumber';
        $pattern       = '/^[a-zA-Z0-9\-_.*\/\s]+$/';
        // Filter input search term
        if (!preg_match($pattern, $searchterm) || $search_length > 30) {
            echo json_encode($products);
            exit();
        }
        
        //Partnumbers
    	// $partnumber = DB::connection(self::$main_db)
        //                 ->select("SELECT '$partNumberId' as id, p.partNumber as label, CONCAT(pm.partTypename, '===', p.partType) as value 
        //                 FROM partNumberInfo p JOIN parttype_master pm ON pm.partTypeid=p.partType 
        //                 WHERE p.partNumber LIKE '%$searchterm%' AND p.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR')");
        $partnumber = DB::connection(self::$main_db)
                        ->select("SELECT '$partNumberId' as id, p.partNumber as label, CONCAT(pm.partTypename, '===', p.PartTerminologyID) as value 
                        FROM partNumberInfo p JOIN parttype_master pm ON pm.PartTerminologyID=p.PartTerminologyID 
                        WHERE p.partNumber LIKE '%$searchterm%' AND p.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') GROUP BY p.partNumber");

    	if (!empty($partnumber) && count($partnumber) > 0) {
            // Create object for part numbers title
            $partNumberTitle        = new stdClass();
            $partNumberTitle->id    = 'label';
            $partNumberTitle->label = 'Part Numbers';

            array_push($products, $partNumberTitle);
            $products = array_merge($products, $partnumber);
            unset($partNumberTitle);

            //if search term length is less than equal to 3, return only this result
            if ($search_length <= 3) {
                // return json_encode($products);
            }
           
        }
        
        //Applications
        
        $searchname_appl = str_replace('-','',$searchterm);
        $appl_string = str_replace(" ", "%' and REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%", $searchname_appl);

        // $pro=DB::connection(self::$aces_vcdb)
        //         ->select("SELECT STRAIGHT_JOIN CONCAT(bv.YearID, '!', bv.MakeID, '!', bv.ModelID, '!', GROUP_CONCAT(DISTINCT eb.EngineBaseID)) as id, CONCAT(CAST(bv.YearID AS char), ' ', TRIM(ma.MakeName), ' ', TRIM(mo.ModelName), ' ', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as label, CONCAT(ids.BaseVehicleID, '===', CAST(bv.YearID AS char), '!', TRIM(ma.MakeName), '!', TRIM(mo.ModelName), '!', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as value 
        //         FROM pgi_web_dynamic_search_flat dsf JOIN pgi_web_dynamic_search_flat_ids ids ON dsf.YMME_ID=ids.YMME_Target_ID 
        //         JOIN basevehicle bv ON ids.BaseVehicleID=bv.BaseVehicleID 
        //         JOIN make ma ON bv.MakeID = ma.MakeID JOIN model mo ON bv.ModelID = mo.ModelID 
        //         JOIN engineconfig ec ON ids.EngineConfigID = ec.EngineConfigID 
        //         JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
        //         LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
        //         WHERE REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%$appl_string%' 
        //         GROUP BY CAST(bv.YearID AS char), TRIM(ma.MakeName), TRIM(mo.ModelName),CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))
        //         ORDER BY CAST(bv.YearID AS char) DESC");

        $pro=DB::connection(self::$aces_vcdb)
                ->select("SELECT STRAIGHT_JOIN CONCAT(bv.YearID, '!', bv.MakeID, '!', bv.ModelID, '!', GROUP_CONCAT(DISTINCT eb.EngineBaseID)) as id, CONCAT(CAST(bv.YearID AS char), ' ', TRIM(ma.MakeName), ' ', TRIM(mo.ModelName), ' ', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as label, CONCAT(dsf.BaseVehicleID, '===', CAST(bv.YearID AS char), '!', TRIM(ma.MakeName), '!', TRIM(mo.ModelName), '!', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as value 
                FROM $this->pg_db.pgi_web_dynamic_search_flat dsf 
                JOIN basevehicle bv ON dsf.BaseVehicleID=bv.BaseVehicleID 
                JOIN make ma ON bv.MakeID = ma.MakeID JOIN model mo ON bv.ModelID = mo.ModelID 
                JOIN engineconfig ec ON dsf.EngineConfigID = ec.EngineConfigID 
                JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
                LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID 
                WHERE REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%$appl_string%' 
                GROUP BY CAST(bv.YearID AS char), TRIM(ma.MakeName), TRIM(mo.ModelName),CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))
                ORDER BY CAST(bv.YearID AS char) DESC");

        if (!empty($pro) && count($pro) > 0) {
            // Create object for applications title
            $appTitle        = new stdClass();
            $appTitle->id    = 'label';
            $appTitle->label = 'Applications';

            array_push($products, $appTitle);
            $products = array_merge($products, $pro);
            unset($appTitle);

            //if search term length is less than equal to 3 , return only this result
            if ($search_length <= 3) {
                return json_encode($products);
            }

        }
        unset($pro);

        //Partinterchanges
        $searchterm = str_replace(['-', ' ', '/'], '', $searchterm);
        $premium=DB::connection(self::$main_db)
                            // ->select("SELECT pm.partIntPartNumber,i.interchange_name,i.id,pm.partNumber 
                            // FROM partInterchange_mapper pm JOIN Interchangename_brandcode_mapper i ON pm.InterchangeNotes=i.id 
                            // WHERE pm.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') AND 
                            // REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pm.partIntPartNumber,'-',''),'*',''),' ',''),'_',''),'/','')  LIKE '%$searchterm%' 
                            // GROUP BY pm.partIntPartNumber,i.interchange_name");

                            // ->select("SELECT '$premiumId' as id, CONCAT(pm.partIntPartNumber, ' ', i.BrandName) as label, CONCAT(pm.partIntPartNumber, '===', i.id) as value
                            // FROM partInterchange_mapper pm
                            // JOIN Interchangename_brandcode_mapper i ON pm.partIntBrandID=i.BrandID AND pm.partIntSubBrandID <=> i.SubBrandID
                            // JOIN partNumberInfo pni ON pni.part_id = pm.part_id
                            // WHERE pni.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') AND 
                            // REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pm.partIntPartNumber,'-',''),'*',''),' ',''),'_',''),'/','')  LIKE '%$searchterm%' 
                            // GROUP BY pm.partIntPartNumber,i.BrandName");
                            // Adding join with parttype_master table to check PartTerminologyID exists in it
                            ->select("SELECT '$premiumId' as id, CONCAT(pm.partIntPartNumber, ' ', i.BrandName) as label, CONCAT(pm.partIntPartNumber, '===', i.id) as value
                            FROM partInterchange_mapper pm
                            JOIN Interchangename_brandcode_mapper i ON pm.partIntBrandID=i.BrandID AND pm.partIntSubBrandID <=> i.SubBrandID
                            JOIN partNumberInfo pni ON pni.part_id = pm.part_id
                            JOIN parttype_master ptm ON ptm.PartTerminologyID=pni.PartTerminologyID
                            WHERE pni.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') AND 
                            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pm.partIntPartNumber,'-',''),'*',''),' ',''),'_',''),'/','')  LIKE '%$searchterm%' 
                            GROUP BY pm.partIntPartNumber,i.BrandName");

        if(!empty($premium) && count($premium) > 0) {
            // Create object for competitors title
            $premiumTitle        = new stdClass();
            $premiumTitle->id    = 'label';
            $premiumTitle->label = 'Competitors Interchanges';

            array_push($products, $premiumTitle);
            $products = array_merge($products, $premium);
            unset($premiumTitle);
        }
        unset($premiumId);
        unset($premium);

        return json_encode($products);
    }

    public function index(Request $request, $searchterm)
    {
        $products = [];
        $searchterm = trim($searchterm ?? '');
        $search_length = strlen($searchterm);
        $pattern = '/^[a-zA-Z0-9\-_.*\/\s]+$/';

        if (!preg_match($pattern, $searchterm) || $search_length > 30) {
            return response()->json($products);
        }

        $partnumber = DB::connection(self::$main_db)->select(
            "SELECT 'partnumber' as id, p.partNumber as label, CONCAT(pm.partTypename, '===', p.PartTerminologyID) as value
            FROM partNumberInfo p
            JOIN parttype_master pm ON pm.PartTerminologyID = p.PartTerminologyID
            WHERE p.partNumber LIKE ? AND p.partNumber NOT IN ('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR')
            GROUP BY p.partNumber",
            ['%' . $searchterm . '%']
        );

        if (!empty($partnumber)) {
            $products[] = (object) ['id' => 'label', 'label' => 'Part Numbers'];
            $products = array_merge($products, $partnumber);
        }

        $searchname_appl = str_replace('-', '', $searchterm);
        $words = array_filter(explode(' ', $searchname_appl));
        $pro = [];

        if (!empty($words)) {
            $appl_conditions = implode(' AND ', array_fill(0, count($words), "REPLACE(dsf.YMME_Concatenated,'-','') LIKE ?"));
            $appl_bindings = array_map(fn($w) => '%' . $w . '%', $words);

            $pro = DB::connection(self::$aces_vcdb)->select(
            "SELECT STRAIGHT_JOIN CONCAT(bv.YearID, '!', bv.MakeID, '!', bv.ModelID, '!', GROUP_CONCAT(DISTINCT eb.EngineBaseID)) as id,
                CONCAT(CAST(bv.YearID AS char), ' ', TRIM(ma.MakeName), ' ', TRIM(mo.ModelName), ' ', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(eb.Liter),'-'), 'L'))) as label,
                CONCAT(dsf.BaseVehicleID, '===', CAST(bv.YearID AS char), '!', TRIM(ma.MakeName), '!', TRIM(mo.ModelName), '!', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(eb.Liter),'-'), 'L'))) as value
            FROM {$this->pg_db}.pgi_web_dynamic_search_flat dsf
            JOIN basevehicle bv ON dsf.BaseVehicleID = bv.BaseVehicleID
            JOIN make ma ON bv.MakeID = ma.MakeID
            JOIN model mo ON bv.ModelID = mo.ModelID
            JOIN engineconfig ec ON dsf.EngineConfigID = ec.EngineConfigID
            JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID
            LEFT JOIN {$this->pg_db}.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID
            WHERE {$appl_conditions}
            GROUP BY CAST(bv.YearID AS char), TRIM(ma.MakeName), TRIM(mo.ModelName), CONCAT_WS(' ', CONCAT(NULLIF(TRIM(eb.Liter),'-'), 'L'))
            ORDER BY CAST(bv.YearID AS char) DESC",
                $appl_bindings
            );
        }

        if (!empty($pro)) {
            $products[] = (object) ['id' => 'label', 'label' => 'Applications'];
            $products = array_merge($products, $pro);
            if ($search_length <= 3) {
                return response()->json($products);
            }
        }

        $searchterm_normalized = str_replace(['-', ' ', '/', '*', '_'], '', $searchterm);
        $premium = DB::connection(self::$main_db)->select(
            "SELECT 'partintnumber' as id, CONCAT(pm.partIntPartNumber, ' ', i.BrandName) as label, CONCAT(pm.partIntPartNumber, '===', i.id) as value
            FROM partInterchange_mapper pm
            JOIN Interchangename_brandcode_mapper i ON pm.partIntBrandID = i.BrandID AND pm.partIntSubBrandID <=> i.SubBrandID
            JOIN partNumberInfo pni ON pni.part_id = pm.part_id
            JOIN parttype_master ptm ON ptm.PartTerminologyID = pni.PartTerminologyID
            WHERE pni.partNumber NOT IN ('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR')
            AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(pm.partIntPartNumber,'-',''),'*',''),' ',''),'_',''),'/','') LIKE ?
            GROUP BY pm.partIntPartNumber, i.BrandName",
            ['%' . $searchterm_normalized . '%']
        );

        if (!empty($premium)) {
            $products[] = (object) ['id' => 'label', 'label' => 'Competitors Interchanges'];
            $products = array_merge($products, $premium);
        }

        return response()->json($products);
    }


    public function parttype(Request $request,$searchterm)
    {
        $products = [];
        $search_length = strlen($searchterm);
        $pattern       = '/^[a-zA-Z0-9\-_.*\/\s]+$/';
        // Filter input search term
        if (!preg_match($pattern, $searchterm) || $search_length > 30) {
            echo json_encode($products);
            exit();
        }

        
        //split the string with space and search if keyword is present in parttypemaster
        $searchname1 = $searchterm;
        $split_term_array = explode(" ",$searchname1);

        $split_term = str_replace(" ","%' OR partTypename LIKE '%", $searchname1);
        $parttypename_result = DB::connection(self::$main_db)->select("SELECT partTypename FROM parttype_master WHERE partTypename LIKE '%$split_term%'");

        if(!empty($parttypename_result) && count($split_term_array) > 1) {

            $array_match = [];
            $final_ptid = [];
            $ptid ='';
            $concat_pt_string = '';

            $final_string = $searchname1;
            
            foreach ($parttypename_result as $key => $value) {
                $pt = $value->partTypename;

                //for each keyword,check if keyword is present in each parttypename string
                $foo = array_filter($split_term_array, function($obj) use ($pt,&$array_match) {
                    
                    if(stripos($pt,$obj) !== false) {
                        if(strlen($obj) > 1) {
                            array_push($array_match,$obj);
                            return TRUE;
                        }
                    }
                    
                });
            }

            //remove duplicate words
            $arr_match = array_unique($array_match);
            //if keyword matches the parttypename string
            if(count($arr_match)>0) {

                //replace the keywords which are related to parttypename with empty string(to search in pgi_web_dynamic_search_flat_ids table)
                $searchname1 = str_replace($arr_match,'',$searchname1);
                $final_string = trim($searchname1);

                if($final_string!='') {

                    //get the ids of parttypename which has all the keywords related to partttypename
                    $join_pt_term = implode(" ",$arr_match);
                    $filtered_term = str_replace(" ","%' AND partTypename LIKE '%", $join_pt_term);
                    // $filtered_result = DB::connection(self::$main_db)->select("SELECT partTypeid FROM parttype_master WHERE partTypename LIKE '%$filtered_term%'");
                    $filtered_result = DB::connection(self::$main_db)->select("SELECT PartTerminologyID as partTypeid FROM parttype_master WHERE partTypename LIKE '%$filtered_term%'");

                    foreach($filtered_result as $fr) {
                        array_push($final_ptid,$fr->partTypeid);
                    }

                    if(count($final_ptid)>0) {

                        $ptid = implode(",",$final_ptid); //parttypeids

                        $replace="\"'\"";

                        $final_string = str_replace('-','',$final_string);
                   
                        $appl_string = str_replace(" ", "%' and REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%", $final_string);

                        // $pro = DB::connection(self::$aces_vcdb)->select("SELECT STRAIGHT_JOIN bv.YearID,bv.MakeID,bv.ModelID,ids.BaseVehicleID,GROUP_CONCAT(DISTINCT eb.EngineBaseID) as EngineBaseID,CAST(bv.YearID AS char) as year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) as model,CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L')) AS engine 
                        //     FROM pgi_web_dynamic_search_flat dsf JOIN pgi_web_dynamic_search_flat_ids ids ON dsf.YMME_ID=ids.YMME_Target_ID 
                        //     JOIN basevehicle bv ON ids.BaseVehicleID=bv.BaseVehicleID 
                        //     JOIN make ma ON bv.MakeID = ma.MakeID 
                        //     JOIN model mo ON bv.ModelID = mo.ModelID 
                        //     JOIN engineconfig ec ON ids.EngineConfigID = ec.EngineConfigID 
                        //     JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
                        //     LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID JOIN $this->pg_db.applications a ON (ids.BaseVehicleID=a.BaseVehicleID AND eb.EngineBaseID=a.EngineBaseID)
                        //     JOIN  $this->pg_db.partNumberInfo pn ON a.partnumber=pn.partNumber
                        //     WHERE REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%$appl_string%' AND pn.partType IN($ptid) 
                        //     GROUP BY a.BaseVehicleID,eb.Liter 
                        //     ORDER BY year DESC,make,model,engine");
                        // $pro = DB::connection(self::$aces_vcdb)->select("SELECT STRAIGHT_JOIN CONCAT(bv.YearID, '!', bv.MakeID, '!', bv.ModelID, '!', GROUP_CONCAT(DISTINCT eb.EngineBaseID)) as id, CONCAT(CAST(bv.YearID AS char), ' ', TRIM(ma.MakeName), ' ', TRIM(mo.ModelName), ' ', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as label, CONCAT(ids.BaseVehicleID, '===', CAST(bv.YearID AS char), '!', TRIM(ma.MakeName), '!', TRIM(mo.ModelName), '!', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as value  
                        //     FROM pgi_web_dynamic_search_flat dsf JOIN pgi_web_dynamic_search_flat_ids ids ON dsf.YMME_ID=ids.YMME_Target_ID 
                        //     JOIN basevehicle bv ON ids.BaseVehicleID=bv.BaseVehicleID 
                        //     JOIN make ma ON bv.MakeID = ma.MakeID 
                        //     JOIN model mo ON bv.ModelID = mo.ModelID 
                        //     JOIN engineconfig ec ON ids.EngineConfigID = ec.EngineConfigID 
                        //     JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
                        //     LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID JOIN $this->pg_db.applications a ON (ids.BaseVehicleID=a.BaseVehicleID AND eb.EngineBaseID=a.EngineBaseID)
                        //     JOIN  $this->pg_db.partNumberInfo pn ON a.part_id=pn.part_id
                        //     WHERE REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%$appl_string%' AND pn.partType IN($ptid) 
                        //     GROUP BY a.BaseVehicleID,eb.Liter 
                        //     ORDER BY CAST(bv.YearID AS char) DESC, TRIM(ma.MakeName), TRIM(mo.ModelName), CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))");
                        $pro = DB::connection(self::$aces_vcdb)->select("SELECT STRAIGHT_JOIN CONCAT(bv.YearID, '!', bv.MakeID, '!', bv.ModelID, '!', GROUP_CONCAT(DISTINCT eb.EngineBaseID)) as id, CONCAT(CAST(bv.YearID AS char), ' ', TRIM(ma.MakeName), ' ', TRIM(mo.ModelName), ' ', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as label, CONCAT(dsf.BaseVehicleID, '===', CAST(bv.YearID AS char), '!', TRIM(ma.MakeName), '!', TRIM(mo.ModelName), '!', CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))) as value  
                            FROM $this->pg_db.pgi_web_dynamic_search_flat dsf
                            JOIN basevehicle bv ON dsf.BaseVehicleID=bv.BaseVehicleID 
                            JOIN make ma ON bv.MakeID = ma.MakeID 
                            JOIN model mo ON bv.ModelID = mo.ModelID 
                            JOIN engineconfig ec ON dsf.EngineConfigID = ec.EngineConfigID 
                            JOIN enginebase eb ON ec.EngineBaseID = eb.EngineBaseID 
                            LEFT JOIN $this->pg_db.fueltype_web_display_mappings ft ON ec.FuelTypeID = ft.FuelTypeID JOIN $this->pg_db.applications a ON (dsf.BaseVehicleID=a.BaseVehicleID AND eb.EngineBaseID=a.EngineBaseID)
                            JOIN  $this->pg_db.partNumberInfo pn ON a.part_id=pn.part_id
                            WHERE REPLACE(dsf.YMME_Concatenated,'-','') LIKE '%$appl_string%' AND pn.PartTerminologyID IN($ptid) 
                            GROUP BY a.BaseVehicleID,eb.Liter 
                            ORDER BY CAST(bv.YearID AS char) DESC, TRIM(ma.MakeName), TRIM(mo.ModelName), CONCAT_WS(' ', CONCAT(NULLIF(TRIM(`eb`.`Liter`),'-'), 'L'))");

                     
                            if (!empty($pro) && count($pro) > 0) {
                                // Create object for applications title
                                $appTitle        = new stdClass();
                                $appTitle->id    = 'label';
                                $appTitle->label = 'Applications';
                    
                                array_push($products, $appTitle);
                                $products = array_merge($products, $pro);
                                unset($appTitle);

                            }
                            unset($pro);

                    }
                }
                else {
                    $products[0]["parttype"]='No';
                }
            }
        }
        else {
            if(!empty($parttypename_result)) {
                $products[0]["parttype"]='No';
            }
        }
    
        return json_encode($products);
    
    }

    public function interchangeresult(Request $request,$int_name,$int_no)
    {  
        $interchnage_pt['res']=DB::connection(self::$main_db)
                                // ->select("SELECT pm.partNumber,pm.partIntPartNumber,i.interchange_name 
                                // FROM partInterchange_mapper pm JOIN Interchangename_brandcode_mapper i ON pm.InterchangeNotes=i.id 
                                // WHERE pm.partIntPartNumber='".$int_name."' and InterchangeNotes=".$int_no."");
                                ->select("SELECT pni.partNumber,pm.partIntPartNumber,i.BrandName as interchange_name 
                                FROM partInterchange_mapper pm
                                JOIN Interchangename_brandcode_mapper i ON pm.partIntBrandID=i.BrandID AND pm.partIntSubBrandID <=> i.SubBrandID
                                JOIN  partNumberInfo pni ON pm.part_id=pni.part_id
                                WHERE pm.partIntPartNumber='".$int_name."' and i.id=".$int_no." GROUP BY pm.partIntPartNumber,i.BrandName,pni.partNumber");
                                
        return json_encode($interchnage_pt);
    }


    // public function intersupersededBK(Request $request,$partNumber)
    // {
    //     // $superseded=DB::connection(self::$main_db)->select("SELECT p.EI_PTS,p.partType,pm.partTypename 
    //     //         FROM partNumberInfo p JOIN parttype_master pm ON pm.partTypeid=p.partType 
    //     //         WHERE partNumber='$partNumber'");
    //     $superseded=DB::connection(self::$main_db)->select("SELECT p.PartTerminologyID as partType,pm.partTypename 
    //             FROM partNumberInfo p JOIN parttype_master pm ON pm.PartTerminologyID=p.PartTerminologyID 
    //             WHERE partNumber='$partNumber'");
                
    //     return json_encode($superseded);
    // }

    public function intersuperseded(Request $request, $partNumber)
    {
        $superseded = DB::connection(self::$main_db)->select(
            "SELECT p.PartTerminologyID as partType, pm.partTypename
            FROM partNumberInfo p
            JOIN parttype_master pm ON pm.PartTerminologyID = p.PartTerminologyID
            WHERE p.partNumber = ?",
            [trim($partNumber ?? '')]
        );

        return response()->json($superseded);
    }

    public function interchangeParttype(Request $request,$partNumber)
    {
        // $superseded = DB::connection(self::$main_db)
        //                 ->select("SELECT p.EI_PTS,p.partType,pm.partTypename FROM partNumberInfo p 
        //                 JOIN parttype_master pm ON pm.partTypeid=p.partType WHERE partnumber='$partNumber'");
        $superseded = DB::connection(self::$main_db)
                        ->select("SELECT p.PartTerminologyID as partType,pm.partTypename FROM partNumberInfo p 
                        JOIN parttype_master pm ON pm.PartTerminologyID=p.PartTerminologyID WHERE partNumber='$partNumber'");

        return json_encode($superseded);
    }

    public function vinsearchBK(Request $request,$vinsearch)
    {
        
        $response_data='{"res":"nodata"}';

        if($vinsearch!='') 
        {
            $vin = $vinsearch;

            try {

                // Initialize Guzzle client
                $client = new Client();
                $apiUrl = 'https://vintelligence.spglobal.com/okta';
                $response = $client->request('POST', $apiUrl, [
                    'headers' => [
                        'username' => 'PGIAPISHARE@PREMIUMGUARD.COM',
                        'password' => 'P4u9Ap@m83!',
                    ]
                ]);
        
        
        
                // Get the body of the response
                $body = $response->getBody();
                $data = json_decode($body, true);
        
                if(count($data)>0) {
        
                    $get_access_token = $data['access_token'];
        
                    if($get_access_token!='') {
                        $decodeVinUrl = 'https://vintelligence.spglobal.com/vintelligence/decodeVin';
        
                        $vindatabody = [
                            'requiredFieldsCSVs'=>'',
                            'vinRequests' => [
                                [
                                    'make' => '',
                                    "trim" => '',
                                    'vin' => $vin,
                                    "year" => '',
                                ]
                                
                            ],
                        ];
        
                        $finalbody = json_encode($vindatabody,JSON_PRETTY_PRINT);
        
                        $responseVin = $client->request('POST', $decodeVinUrl, [
                            'json' => $vindatabody,
                            'headers' => [
                                'accesstoken' => $get_access_token,
                            ]
                        ]);
        
        
                        // Get the body of the response
                        $vinBody = $responseVin->getBody();
                        $vindata = json_decode($vinBody);
        
                        $finalresult = $vindata[0];
        
                        if($finalresult->returnCode=='0') {


                            $get_data=[];

                            for($i=0;$i<count($finalresult->fields);$i++){
                                if (strpos($finalresult->fields[$i]->name, 'ACES_BASE_VEHICLE') !== false) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                    
                                }
                                if (strpos($finalresult->fields[$i]->name, 'ACES_ENGINE_BASE_ID') !== false) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                   
                                }
                                if(preg_match('(ACES_YEAR_ID|ACES_MAKE_NAME|ACES_MODEL_NAME|ACES_LITERS)', $finalresult->fields[$i]->name) === 1) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                }
                    
                            }


                            if($get_data!=NULL)
                            {
                                $split_response=implode(',',$get_data);
                                $response_data='{"res":"'.$split_response.'"}';
                            }
                        }
                    }
                }
        
            }  catch (Exception $e) {
                echo $e->getMessage();
            }
        
        }

        return $response_data;
    }

    public function vinsearch(Request $request, $vinsearch)
    {
        $vin = trim($vinsearch ?? '');
        $response = ['res' => 'nodata'];

        if ($vin === '') {
            return response()->json($response);
        }

        $vinConfig = config('services.vin_api');
        if (empty($vinConfig['username']) || empty($vinConfig['password'])) {
            Log::error('VIN API credentials not configured');
            return response()->json($response);
        }

        try {
            $client = new Client();
            $baseUrl = rtrim($vinConfig['url'], '/');

            $authResponse = $client->request('POST', $baseUrl . '/okta', [
                'headers' => [
                    'username' => $vinConfig['username'],
                    'password' => $vinConfig['password'],
                ],
            ]);

            $data = json_decode($authResponse->getBody()->getContents(), true);
            $accessToken = $data['access_token'] ?? '';

            if ($accessToken === '') {
                return response()->json($response);
            }

            $decodeResponse = $client->request('POST', $baseUrl . '/vintelligence/decodeVin', [
                'json' => [
                    'requiredFieldsCSVs' => '',
                    'vinRequests' => [
                        ['make' => '', 'trim' => '', 'vin' => $vin, 'year' => ''],
                    ],
                ],
                'headers' => ['accesstoken' => $accessToken],
            ]);

            $vindata = json_decode($decodeResponse->getBody()->getContents());
            $finalresult = $vindata[0] ?? null;

            if ($finalresult && ($finalresult->returnCode ?? '') === '0' && !empty($finalresult->fields)) {
                $get_data = [];

                foreach ($finalresult->fields as $field) {
                    $name = $field->name ?? '';
                    if (str_contains($name, 'ACES_BASE_VEHICLE') || str_contains($name, 'ACES_ENGINE_BASE_ID')) {
                        $get_data[] = $field->value ?? '';
                    } elseif (preg_match('/ACES_YEAR_ID|ACES_MAKE_NAME|ACES_MODEL_NAME|ACES_LITERS/', $name)) {
                        $get_data[] = $field->value ?? '';
                    }
                }

                if (!empty($get_data)) {
                    $response['res'] = implode(',', $get_data);
                }
            }
        } catch (Exception $e) {
            Log::error('VIN decode error: ' . $e->getMessage());
        }

        return response()->json($response);
    }

    public function vinsearchMobikasa(Request $request,$vinsearch)
    {
        $response_data='{"res":"nodata"}';

        if($vinsearch!='') 
        {
            $vin = $vinsearch;

            try {

                // Initialize Guzzle client
                $client = new Client();
                $apiUrl = 'https://vintelligence.spglobal.com/okta';
                $response = $client->request('POST', $apiUrl, [
                    'headers' => [
                        'username' => 'PGIAPISHARE@PREMIUMGUARD.COM',
                        'password' => 'P4u9Ap@m83!',
                    ]
                ]);
        
        
        
                // Get the body of the response
                $body = $response->getBody();
                $data = json_decode($body, true);
        
                if(count($data)>0) {
        
                    $get_access_token = $data['access_token'];
        
                    if($get_access_token!='') {
                        $decodeVinUrl = 'https://vintelligence.spglobal.com/vintelligence/decodeVin';
        
                        $vindatabody = [
                            'requiredFieldsCSVs'=>'',
                            'vinRequests' => [
                                [
                                    'make' => '',
                                    "trim" => '',
                                    'vin' => $vin,
                                    "year" => '',
                                ]
                                
                            ],
                        ];
        
                        $finalbody = json_encode($vindatabody,JSON_PRETTY_PRINT);
        
                        $responseVin = $client->request('POST', $decodeVinUrl, [
                            'json' => $vindatabody,
                            'headers' => [
                                'accesstoken' => $get_access_token,
                            ]
                        ]);
        
        
                        // Get the body of the response
                        $vinBody = $responseVin->getBody();
                        $vindata = json_decode($vinBody);
        
                        $finalresult = $vindata[0];
        
                        if($finalresult->returnCode=='0') {


                            $get_data=[];

                            for($i=0;$i<count($finalresult->fields);$i++){
                                if (strpos($finalresult->fields[$i]->name, 'ACES_BASE_VEHICLE') !== false) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                    
                                }
                                if (strpos($finalresult->fields[$i]->name, 'ACES_ENGINE_BASE_ID') !== false) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                   
                                }
                                if(preg_match('(ACES_YEAR_ID|ACES_MAKE_NAME|ACES_MODEL_NAME|ACES_LITERS)', $finalresult->fields[$i]->name) === 1) {
                                    array_push($get_data,$finalresult->fields[$i]->value);
                                }
                    
                            }


                            if($get_data!=NULL)
                            {
                                $split_response=implode(',',$get_data);
                                $response_data='{"res":"'.$split_response.'"}';
                            }
                        }
                    }
                }
        
            }  catch (Exception $e) {
                echo $e->getMessage();
            }
        
        }

        return $response_data;

    }

    public function partsearchcount(Request $request,$partnumber){

        // $curentMonth=date('m');
        // $curentYear=date('Y');
        // $cnt=DB::connection(self::$main_db)->select("SELECT id FROM partnumber_statistics where part_month='$curentMonth' and part_year='$curentYear' and partnumber='$partnumber'");

        // if(empty($cnt))
        // {

        //     $wpdb=DB::connection(self::$main_db)->select("INSERT INTO partnumber_statistics (part_month,part_year,partnumber,count) VALUES ('$curentMonth','$curentYear','$partnumber','1')");
        // }
        // else
        // {


        //     $partId=$cnt[0]->id;
        //     $wpdb=DB::connection(self::$main_db)->select("UPDATE partnumber_statistics SET count=count+1 WHERE id='$partId'");

        // }

        // return json_encode($wpdb);



    }

    

    //not found application from dynamic search (edit before deploying)
    public function noresultcount(Request $request){

        // $data = $request->all();
        // $searchterm = $data['searchterm'];
        // $curentMonth=date('m');
        // $curentYear=date('Y');

        // $cnt = DB::select(DB::raw("SELECT n_id FROM not_found_applications where missing_search='$searchterm'");

        // if(empty($cnt)) {
        //     $wpdb=DB::select(DB::raw("INSERT INTO not_found_applications (n_id,missing_search,app_year,app_month,count_of_app) VALUES ('','$searchterm','$curentYear','$curentMonth','1')");
        // }
        // else {
        //     $appId = $cnt[0]->n_id;
        //     $wpdb=DB::select(DB::raw("UPDATE not_found_applications SET count_of_app = count_of_app+1 WHERE n_id='$appId'");
        // }

        // return json_encode($wpdb);
    }
}
