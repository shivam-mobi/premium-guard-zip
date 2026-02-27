<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Config;

class ProductCompController extends Controller {

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

    public function index(Request $request) {
    	
    }

    public function all_years(Request $request) {

        $products["years"] = DB::connection(self::$main_db)
                                ->table('applications')
                                ->selectRaw('DISTINCT(YearID) as year')
                                ->orderBy('year','DESC')
                                ->get();

        // $products["parttypes"] = DB::connection(self::$main_db)
                                // ->select("SELECT CASE WHEN partTypeid = 1 THEN 'Oil Filter' WHEN partTypeid = 14 THEN 'Housing Cap' WHEN partTypeid = 10 THEN 'Drain Plug' ELSE partTypename END AS partTypename 
                                // FROM parttype_master WHERE partTypeid IN(1,2,3,4,5,8,10,14) ORDER BY FIELD(partTypeid,1,2,3,4,5,14,10,8)");
                                // ->select("SELECT CASE WHEN PartTerminologyID = 1 THEN 'Oil Filter' WHEN PartTerminologyID = 14 THEN 'Housing Cap' WHEN PartTerminologyID = 10 THEN 'Drain Plug' ELSE partTypename END AS partTypename 
                                // FROM parttype_master WHERE PartTerminologyID IN(1,2,3,4,5,8,10,14) ORDER BY FIELD(PartTerminologyID,1,2,3,4,5,14,10,8)");

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
        
        return json_encode($products);
    }

    public function all_products(Request $request)  {
       
        $data = $request->all();

        $hidden_makes = '';
        $hidden_models = '';
        $hidden_partnumbers = '';
        $product_type = $data['parttype'];
        $fromyear = $data['fromyear'];
        $toyear = $data['toyear'];
        $get_hidden_makes = isset($data['make_selected'])?$data['make_selected']:'';
        $get_hidden_models = isset($data['model_selected'])?$data['model_selected']:'';
        $get_hidden_partnumbers = isset($data['partnumbers_selected'])?$data['partnumbers_selected']:'';


        if($fromyear != '' && $toyear !='') {
            $years_between = 'and (a.YearID BETWEEN '.$fromyear.' AND '.$toyear.')';
        }
        else {
            $years_between = '';
        }

        if($get_hidden_makes!='') {
            $hidden_makes = 'and a.MakeID IN('.$get_hidden_makes.')';
        }
    
        if($get_hidden_models!='') {
            $hidden_models = 'and a.ModelID IN('.$get_hidden_models.')';
        }
    
        if($get_hidden_partnumbers!='') {
            $store_pt = explode(",",$get_hidden_partnumbers);
    
            //prefix and suffix each partnumber with single quote
            if(count($store_pt)>1) {
                $data_pt = "'" . implode("','", $store_pt) . "'";
            }
            else {
                $data_pt="'".$store_pt[0]."'";
            }
            // $hidden_partnumbers = 'and p.partnumber IN('.$data_pt.')';
            $hidden_partnumbers = 'and p.partNumber IN('.$data_pt.')';
        }

        // $partTypeCodeArray = array("Oil Filter"=>[1,7,12],"Air Filter"=>[2,13],"Cabin Air Filter"=>[3,16],"Fuel Filter"=>[4],"Transmission Filter"=>[5],"Housing Cap"=>[14],"Drain Plug"=>[10,18],"Wiper Blade"=>[8,9,11,15,17]);
        $partTypeCodeArray = array("Oil Filter"=>[5340],"Air Filter"=>[6192],"Cabin Air Filter"=>[6832],"Fuel Filter"=>[6200,6216],"Transmission Filter"=>[8600,10964],"Housing Cap"=>[54048],"Drain Plug"=>[5560,5428],"Wiper Blade"=>[8852,11713,47330]);
        $partTypesIds = array("Oil Filter"=>1,"Air Filter"=>2,"Cabin Air Filter"=>3,"Fuel Filter"=>4,"Transmission Filter"=>5,"Housing Cap"=>6,"Drain Plug"=>7,"Wiper Blade"=>8);
        $PAID = array(14, 19, 24, 25, 31, 522, 531, 534, 568, 569, 570, 576, 577, 578, 579, 1908, 1172, 9035, 9036);
        $attributes_array=array();
        $flag_height=0;

        $products_result = [];

        if($product_type!='') {
            //product details
            $products_result = DB::connection(self::$main_db)
                    // ->select("SELECT p.partnumber,a.YearID AS year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) AS model,p.partType FROM applications a 
                    // JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                    // JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                    // JOIN partNumberInfo p ON a.partnumber=p.partNumber 
                    // WHERE p.partType IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes $hidden_models $hidden_partnumbers 
                    // and p.partnumber not like 'MG%' and p.partnumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') 
                    // GROUP BY a.partnumber 
                    // ORDER BY (SELECT count(DISTINCT ms.PAName) as c FROM part_attributes_mapper mp join part_attributes_master ms on mp.PAID=ms.PAID WHERE mp.partNumber=p.partnumber  GROUP BY mp.partNumber) DESC,
                    // CAST(digits(a.partnumber) as UNSIGNED) DESC");

                    ->select("SELECT p.partNumber as partnumber,a.YearID AS year,TRIM(ma.MakeName) AS make,TRIM(mo.ModelName) AS model,p.PartTerminologyID as partType FROM applications a 
                    JOIN $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                    JOIN $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                    JOIN partNumberInfo p ON a.part_id=p.part_id 
                    WHERE p.PartTerminologyID IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes $hidden_models $hidden_partnumbers 
                    and p.partNumber not like 'MG%' and p.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') 
                    GROUP BY p.partNumber 
                    ORDER BY (SELECT count(DISTINCT ms.PAName) as c FROM part_attributes_mapper mp join part_attributes_master ms on mp.PAID=ms.PAID WHERE mp.part_id=p.part_id  GROUP BY p.partNumber) DESC,
                    CAST(digits(p.partNumber) as UNSIGNED) DESC");
            
            foreach ($products_result as $products_result1) {

                    $ids_part = $partTypesIds[$product_type];

                    $part=$products_result1->partnumber;
                    $ptypefordis=substr($part,0,2);
                    $partT=strtoupper($ptypefordis);
                    if($partT=="DF"){
                        $products_result1->partTypeName ="Diesel-Fuel-Filter";
                    }else{
                        $partTypeName=$product_type;
                        $products_result1->partTypeName=str_replace(' ', '-',$partTypeName);
                    }

                   
                    //image filename query
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


                    if(count($productImg) > 0) {
                        
                        $products_result1->imagePath = $this->image_path.$productImg[0]->fileName;
                        
                    }
                    else {
                        
                        if($ids_part=="6") {
                            $products_result1->imagePath = $this->default_path.'PgFilterLookupOILCAP.png';
                        }
                        else if($ids_part=="7") {
                            $products_result1->imagePath = $this->default_path.'PgFilterLookupDRAINPLUG.png';
                        }
                        else if($ids_part=="8") {
                            $products_result1->imagePath = $this->default_path.'PgFilterLookupREARWIPER.png';
                        }
                        else {
                            $products_result1->imagePath = $this->default_path.'filterType'.$ids_part.'.png';
                        }
                    }

                    //product specification query
                    $product_specs = DB::connection(self::$main_db)
                                    // ->select("SELECT ms.PAName,mp.value,ms.PAID FROM part_attributes_mapper mp 
                                    // JOIN part_attributes_master ms ON mp.PAID=ms.PAID WHERE partNumber='$part'  group by ms.PAName order by FIELD(ms.PAName,'Shape','Gasket Material','Anti-Drain Back Valve','Thread Size','Bypass Relief Valve','Burst Pressure','Micron Rating','Gasket Thickness','Gasket Outside Diameter','Gasket Inside Diameter','Outside Diameter','Height','Width','Length','Side C Length','Side B Length','Side A Length')DESC");
                                    ->select("SELECT ms.PAName,mp.value,ms.PAID FROM part_attributes_mapper mp 
                                    JOIN part_attributes_master ms ON mp.PAID=ms.PAID
                                    JOIN partNumberInfo p ON mp.part_id=p.part_id
                                    WHERE p.partNumber='$part'  group by ms.PAName order by FIELD(ms.PAName,'Shape','Gasket Material','Anti-Drain Back Valve','Thread Size','Bypass Relief Valve','Burst Pressure','Micron Rating','Gasket Thickness','Gasket Outside Diameter','Gasket Inside Diameter','Outside Diameter','Height','Width','Length','Side C Length','Side B Length','Side A Length') DESC");

                    if(!empty($product_specs)) {

                        foreach ($product_specs as $product_specs1) {

                            if($product_specs1->PAID == 19 && $flag_height==1){
                                continue;
                            }
                                                                                                         
                            $spec_value1 = '';
                            $spec_value1 = strtoupper($product_specs1->value);

                            //e.g 6.31 IN , 6.31IN
                            if (preg_match('/^(\d+\.?\d*)IN$/', $spec_value1) == true || preg_match('/^(\d+\.?\d*) IN$/', $spec_value1) == true) {
                                $spec_value = str_replace('IN', '',$spec_value1);
                                
                            }
                            else {
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
                                            if(strpos($spec_value1, '±')!== false) {
                                                $spec_value = $components[0];
                                                if (is_numeric($spec_value)) {
                                                    $spec_value = $spec_value / 25.4;
                                                    $spec_value = round($spec_value, 2);
                                                }
                                                else {
                                                    $spec_value = round($components[0], 2);
                                                }
                                            }
                                            else {
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
                                }
                                else {
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
                            
                           
                            if($product_specs1->PAID == 31){
                                    $flag_height=1;
                            }
                            $product_specs1->product_specs_name =  $product_specs1->PAName;                                                        
                            

                            //value
                            if (in_array($product_specs1->PAID, $PAID) && strpos($spec_value1,'/min')===false) {

                                //if '±' not present in the string and no two '.' present in string
                                $reg_exp = preg_match('/[a-zA-Z\"\'\/^±£$%&*()}{@#~?><>,|=_+¬-]/', $spec_value);
                                if (!$reg_exp && substr_count($spec_value, '.') < 2 ) {
                                    $product_specs1->product_specs_value =  round($spec_value,2);  
                                }
                                else {
                                    $product_specs1->product_specs_value = ucwords(trim($spec_value));
                                }                                            
                            } else {
                                $product_specs1->product_specs_value = ucwords(trim($spec_value));
                            }
                        }
                    }

                    //custom key used
                    $products_result1->spec = $product_specs;
                }

            return json_encode($products_result);
        }
    }


    public function all_makemodel(Request $request) {

        $data = $request->all();

        $hidden_makes = '';
        $hidden_models= '';
        $years_between = '';
        $products = [];
        $product_type = $data['parttype'];
        $fromyear = $data['fromyear'];
        $toyear = $data['toyear'];

        $get_hidden_makes = isset($data['make_selected'])?$data['make_selected']:'';
        $get_hidden_models = isset($data['model_selected'])?$data['model_selected']:'';
        // $partTypeCodeArray = array("Oil Filter"=>[1,7,12],"Air Filter"=>[2,13],"Cabin Air Filter"=>[3,16],"Fuel Filter"=>[4],"Transmission Filter"=>[5],"Housing Cap"=>[14],"Drain Plug"=>[10,18],"Wiper Blade"=>[8,9,11,15,17]);
        $partTypeCodeArray = array("Oil Filter"=>[5340],"Air Filter"=>[6192],"Cabin Air Filter"=>[6832],"Fuel Filter"=>[6200,6216],"Transmission Filter"=>[8600,10964],"Housing Cap"=>[54048],"Drain Plug"=>[5560,5428],"Wiper Blade"=>[8852,11713,47330]);

        if($product_type!='') {

            if($get_hidden_makes!='') {
                $hidden_makes = 'and a.MakeID IN('.$get_hidden_makes.')';
            }

            if($get_hidden_models!='') {
                $hidden_models = 'and a.ModelID IN('.$get_hidden_models.')';
            }

            if($fromyear != '' && $toyear !='') {
                $years_between = 'and (a.YearID BETWEEN '.$fromyear.' AND '.$toyear.')';
            }


            $products = [];
            $products['make'] = DB::connection(self::$main_db)
                                // ->select("SELECT DISTINCT ma.MakeID,ma.MakeName 
                                // FROM applications a JOIN partNumberInfo p  ON a.partnumber = p.partNumber  
                                // JOIN $this->pg_secdb.make ma ON a.MakeID=ma.MakeID WHERE p.partType IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between  
                                // ORDER BY ma.MakeName asc");
                                ->select("SELECT DISTINCT ma.MakeID,ma.MakeName 
                                FROM applications a JOIN partNumberInfo p  ON a.part_id = p.part_id  
                                JOIN $this->pg_secdb.make ma ON a.MakeID=ma.MakeID WHERE p.PartTerminologyID IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between  
                                ORDER BY ma.MakeName asc");

            $products["model"] = DB::connection(self::$main_db)
                                // ->select("SELECT DISTINCT m.ModelID,m.ModelName 
                                // FROM applications a JOIN partNumberInfo p  ON a.partnumber = p.partNumber  
                                // JOIN $this->pg_secdb.model m ON a.ModelID=m.ModelID  WHERE  p.partType IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes  
                                // ORDER BY m.ModelName asc");
                                ->select("SELECT DISTINCT m.ModelID,m.ModelName 
                                FROM applications a JOIN partNumberInfo p  ON a.part_id = p.part_id  
                                JOIN $this->pg_secdb.model m ON a.ModelID=m.ModelID  WHERE  p.PartTerminologyID IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes  
                                ORDER BY m.ModelName asc");

            $products["partnumber"] = DB::connection(self::$main_db)
                                // ->select("SELECT p.partnumber FROM applications a 
                                // JOIN  $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                                // JOIN  $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                                // JOIN partNumberInfo p ON a.partnumber=p.partNumber 
                                // WHERE p.partType IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes $hidden_models  
                                // AND p.partnumber not like 'MG%' and p.partnumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') 
                                // GROUP BY a.partnumber 
                                // ORDER by CAST(digits(a.partnumber) as UNSIGNED) DESC");
                                ->select("SELECT p.partNumber as partnumber FROM applications a 
                                JOIN  $this->pg_secdb.make ma ON a.MakeID = ma.MakeID 
                                JOIN  $this->pg_secdb.model mo ON a.ModelID = mo.ModelID 
                                JOIN partNumberInfo p ON a.part_id=p.part_id 
                                WHERE p.PartTerminologyID IN (".implode(",",$partTypeCodeArray[$product_type]).") $years_between $hidden_makes $hidden_models  
                                AND p.partNumber not like 'MG%' and p.partNumber NOT IN('NRC','NSC','NRA','NSA','NRO','NSO','NRF','NSF','NRT','NST','TBA','NR') 
                                GROUP BY p.partNumber 
                                ORDER by CAST(digits(p.partNumber) as UNSIGNED) DESC");
            return $products;
            
        }

    }

    
}
