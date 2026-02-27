<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;
use DB;

use Storage;
use Google\Auth\Credentials\ServiceAccountCredentials;

class StatisticsController extends Controller
{

    public static $aces_vcdb = 'mysql_aces';
    public static $main_db = 'mysql';

    public function index(Request $request) {

        $lookup_flag = !empty($request['lookup_flag'])  ? $request['lookup_flag'] : '';

        $curentYear=date('Y');
        $curentMonth=date('m');

        if($lookup_flag!='') {
            $col_name = '';
            if($lookup_flag=='application'){
                $col_name = 'application_lookups';
            }
            elseif($lookup_flag=='keyword') {
                $col_name = 'keyword_lookups';
            }
            else {
                $col_name = 'vin_lookups';
            }
            
            $cnt=DB::connection(self::$main_db)
                    ->select("SELECT id,".$col_name." FROM lookups_statistics_app where app_year='$curentYear' and app_month='$curentMonth'");

            if(empty($cnt)) {
                DB::connection(self::$main_db)
                    ->insert("INSERT INTO lookups_statistics_app (app_year,app_month,".$col_name.") VALUES (?, ?, ?)", [$curentYear,$curentMonth,1]);
            }
            else {
                $get_id = $cnt[0]->id;
                $count=$cnt[0]->$col_name;
                $count = $count+1;
                DB::connection(self::$main_db)
                    ->update("UPDATE lookups_statistics_app SET ".$col_name." = ? WHERE id = ?", [$count,$get_id]);
            }

        }


        $top25flag = !empty($request['top25flag'])  ? $request['top25flag'] : '';
        $top25label = !empty($request['label'])  ? $request['label'] : '';

        if($top25flag!='' && $top25label!='') {

            $col_name = '';
            if($top25flag=='application') {
                $col_name = 'applications_statistics_app';
            }
            else {
                $col_name = 'crossref_statistics_app';
            }


            $cnt=DB::connection(self::$main_db)
                    ->select("SELECT id FROM $col_name WHERE app_year='$curentYear' AND app_month='$curentMonth' AND label='$top25label'");

            Log::info($cnt);
            if(empty($cnt)) {
                DB::connection(self::$main_db)
                    ->insert("INSERT INTO $col_name (app_month,app_year,label,label_count) VALUES ('$curentMonth','$curentYear','$top25label','1')");
            }
            else {
                $get_id = $cnt[0]->id;
                DB::connection(self::$main_db)
                    ->update("UPDATE $col_name SET label_count=label_count+1 WHERE id = ?", [$get_id]);
            }

        }
    }

    public function insert_device_tokens(Request $request) {
        $fcmToken = !empty($request['fcmToken'])  ? $request['fcmToken'] : '';
        if(!empty($fcmToken)) {
            DB::insert("INSERT INTO app_device_tokens (device_token) VALUES (?) ON DUPLICATE KEY UPDATE device_token = device_token", [$fcmToken]);
        }
    }

    public function get_device_tokens() {
        $result = DB::connection(self::$main_db)
        ->select('select device_token from app_device_tokens');
        return $result;
    }

    public function get_image_dimensions() {
        $image_name = $_POST["image_name"];
        $brands = $_POST["brand_id"];
        $paths = array("1" => "/home/pgfilt5/public_html/wp-content/uploads1/assets/", "2" => "/home/pgfilt5/public_html/wp-content/uploads1/assets/Ecogard/");
        $image_name = explode(",", $image_name);
        $final_val = [];
        foreach($image_name as $image_name1){
        $image_path = $paths[$brands] . $image_name1;
        if (file_exists($image_path)) {
            $size = getimagesize($image_path);
            $final_val[] = array($image_name1=>$size[0]."X".$size[1]);
        }else{
            $final_val[] = array($image_name1=>"");
        }
        }
        return json_encode($final_val);
    }

    public function get_image_dimension() {
        $image_name = $_POST["image_name"];
        $image_path = "/home/pgfilt5/public_html/wp-content/uploads1/assets/" . $image_name;
        if (file_exists($image_path)) {
            $size = getimagesize($image_path);
            $final_val[] = array("dimension"=>$size[0]."X".$size[1]);
        }else{
            $final_val[] = array("dimension"=>"");
        }
        return json_encode($final_val);
    }


    public function get_bearer_token() {
        // Path to your service account JSON key file

        $credentialsPath = Storage::disk('public')->path('premium-guard-firebase-adminsdk-sooge-94f30f9d22.json');
      
        // Define the scopes you need
        $scopes = ['https://www.googleapis.com/auth/cloud-platform']; // Adjust the scopes as needed

        // Create a ServiceAccountCredentials object
        $credentials = new ServiceAccountCredentials(
            $scopes,
            $credentialsPath
        );

        // Get an bearer token
        $bearerToken = $credentials->fetchAuthToken();

        $bea_token = 0;
        // Print the bearer token
        if (isset($bearerToken['access_token'])) {
            $bea_token = $bearerToken['access_token'];
        }
        
        $data['bearer_token']=$bea_token;

        return json_encode($data);

        
    }


}
