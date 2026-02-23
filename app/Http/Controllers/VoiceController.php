<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;
use Illuminate\Support\Facades\Log;


use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\SpeechClient;

class VoiceController extends Controller
{
    public static $aces_vcdb = 'mysql_aces'; 
    public static $main_db = 'mysql';

    public function __construct() {
       
    }

    /**
     * Display a listing of Year.
     *
     * @return \Illuminate\Http\Response
     */

    public function voicesearch() {
       
        if(isset($_FILES['file']) and !$_FILES['file']['error']) {

            $path = storage_path('app').'/recordings/';

            $browsername=$_POST['browsername'];
            $cutime=time();

            if($browsername=="Safari" || $browsername=="Firefox") {
                $fname = "audio" .$cutime. ".mp4";
            }
            else{
                $fname = "audio" .$cutime. ".webm";
            }

            Log::info($browsername);

            if (move_uploaded_file($_FILES['file']['tmp_name'], $path. $fname)) {

                $languageCode = 'en-US';
                if(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') == TRUE) {
                    $audioChannelCount = 2;
                }
                else {
                    $audioChannelCount = 1;
                }
               
                if($browsername=="Safari" || $browsername=='Firefox') {

                    $safari_path_ori = $path.$fname;
                    $safari_path_con = $path."audio".$cutime.".flac";

                    Log::info('before exec');
                    //convert .mp4 file into .flac file
                    exec("ffmpeg -i ".$safari_path_ori." ".$safari_path_con);
                    Log::info('after exec');
             
                    $encoding=AudioEncoding::FLAC;

                    $isMobile=preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);

                    if($browsername=='Firefox') {

                        if($isMobile == 1) {
                            $audioChannelCount = 1;
                        }

                        $config = (new RecognitionConfig())
                            ->setEncoding($encoding)
                            ->setUseEnhanced(true) // added by manali
                            ->setAudioChannelCount($audioChannelCount)
                            ->setLanguageCode($languageCode);
                    }
                    else {
                        $config = (new RecognitionConfig())
                        ->setEncoding($encoding)
                        ->setUseEnhanced(true) // added by manali
                        ->setLanguageCode($languageCode);
                    }

                    
                       
                    $audioFile = $path."audio".$cutime.".flac";
        
                } else {

                    $config = (new RecognitionConfig())
                        ->setAudioChannelCount($audioChannelCount)
                        ->setLanguageCode($languageCode);
        
                    $audioFile = $path.$fname;
                }

                $content = file_get_contents($audioFile);
                $audio = (new RecognitionAudio())->setContent($content);
                $speechClient = new SpeechClient();
        
                try {

                    $response = $speechClient->recognize($config, $audio);
                    foreach ($response->getResults() as $result) {
                        Log::info('in foreach');
                        $alternatives = $result->getAlternatives();
                        $mostLikely = $alternatives[0];
                        $transcript = $mostLikely->getTranscript();
                        echo $transcript;
                        
                    }

                    if($browsername=="Safari") {
                        unlink($audioFile);
                        unlink($path."audio".$cutime.".mp4");
                    }else{    
                        unlink($audioFile);
                    }

                } finally {
                    $speechClient->close();
                }
                
            } else {
                Log::info('File not found');
            }
        }

    }

    public function convertToFlac(Request $request) {

        $data = $request->all();

        if(!empty($data)) {

            $path = storage_path('app').'/ecogard_recordings/';
            $cutime=time();
            $fname_mp4 = "audio" .$cutime. ".mp4";
            $file_temp = urldecode($data['file']);

            file_put_contents($path.$fname_mp4,$file_temp);

            if(file_exists($path.$fname_mp4)) {

                $encoding=AudioEncoding::FLAC;
                $languageCode = 'en-US';


                $safari_path_ori = $path.$fname_mp4;
                $safari_path_con = $path."audio".$cutime.".flac";

                Log::info('from ecogard_production');
                //convert .mp4 file into .flac file
                exec("ffmpeg -i ".$safari_path_ori." ".$safari_path_con);
                Log::info('after exec');
               
                $config = (new RecognitionConfig())
                    ->setEncoding($encoding)
                    ->setUseEnhanced(true) // added by manali
                    ->setLanguageCode($languageCode);

                $audioFile_flac = $path."audio".$cutime.".flac";

                $content = file_get_contents($audioFile_flac);
                $audio = (new RecognitionAudio())->setContent($content);
                $speechClient = new SpeechClient();

                try {

                    $response = $speechClient->recognize($config, $audio);
                    foreach ($response->getResults() as $result) {
                        Log::info('in foreach');
                        $alternatives = $result->getAlternatives();
                        $mostLikely = $alternatives[0];
                        $transcript = $mostLikely->getTranscript();

                        unlink($audioFile_flac);
                        unlink($path.$fname_mp4);
                        return $transcript;
                        
                    }
                   
                }
                 finally {
                    $speechClient->close();
                }
            }
            else {
                Log::info('no file found');
                return false;
            }
            
        }
        else {
            Log::info('no post data from ecogard php file');
            return false;
        }
    
    }

}
