<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;
use DB;
use Config;
use Log;


use App\Models\Post;



class TechnicalResources extends Controller
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

    public function index(Request $request)
    {

    }


    public function bulletins(Request $request)
    {

        $bulletins= DB::connection(self::$main_db)
            ->select("SELECT GROUP_CONCAT(DISTINCT pdf.title ORDER BY pdf.title SEPARATOR '!!') as title,GROUP_CONCAT(DISTINCT pdf.file_name ORDER BY pdf.title SEPARATOR '!!') as filename,cat.id as catid,cat.title as cattitle 
                FROM wp_bsk_pdf_manager_relationships rel JOIN wp_bsk_pdf_manager_pdfs pdf ON rel.pdf_id=pdf.id 
                JOIN wp_bsk_pdf_manager_cats cat ON rel.cat_id=cat.id 
                WHERE rel.type='CAT' AND rel.cat_id IN(12,13,14) AND pdf.trash='0' 
                GROUP BY cat.id 
                ORDER BY cat.last_date ASC");
        
        $final_bulletins = [];
        

        if(!empty($bulletins)) {
            foreach ($bulletins as $bl) {
                $send_bulletins['cat_title'] = $bl->cattitle;

                $explode_title = explode("!!",$bl->title);
                $explode_file_name = explode("!!",$bl->filename);
                $save_path_title = [];
                $title_path = [];

                foreach($explode_title as $key=>$value) {

                    $title_path['pdf_title'] = $value;
                    $title_path['pdf_path'] = $explode_file_name[$key];
                    array_push($save_path_title,$title_path);

                }
            
                $send_bulletins['cat_pdf_title'] = $save_path_title;
                array_push($final_bulletins,$send_bulletins);
            }
        }

        return json_encode($final_bulletins);
    }


    public function bulletinssearch(Request $request,$searchtext)
    {

        $postRequest = array(
            'search_term' => $searchtext,
        );

        $data = http_build_query($postRequest);

        $api_url = 'https://www.pgfilters.com/wp-json/techbulletins/search';
      
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL,$api_url);
        curl_setopt($cURL, CURLOPT_POSTFIELDS, $data);
        curl_setopt($cURL, CURLOPT_HEADER,false);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($cURL, CURLOPT_POST, true);
        curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($cURL);
        curl_close($cURL);

        Log::info($response);

        return json_decode($response);

    }


    public function gettechtips()
    {
        $userData = Post::customMethod();
        return json_encode($userData);
    }

    public function techtipsearch(Request $request,$searchtext)
    {

        $findtechtips = Post::techTipsSearch();
        return $findtechtips;
        // $findres = file_get_contents('https://www.pgfilters.com/wp-json/wp/v2/posts?categories=512&per_page=100');
        // return $findres;
    }

    public function geteachpostdetails(Request $request,$post_id) {
        $postData = Post::eachPost($post_id);
        return json_encode($postData);
    }

    public function blogsearch(Request $request,$searchtext)
    {

        $findblog = Post::blogSearch();
        return $findblog;
        // $findblog = file_get_contents('https://www.pgfilters.com/wp-json/wp/v2/posts?categories=2619&per_page=100');
        // return $findblog;
    }

    public function gettheblogs()
    {
        $userBlogData = Post::customMethodforBlogs();
        return $userBlogData;
    }

    public function geteachpostimage(Request $request,$post_id) {
        $postImage = Post::postImage($post_id);
        return json_encode($postImage);
    }

    public function getpostsimages(Request $request,$post_id) {
        $Image = Post::allImages($post_id);
        return json_encode($Image);
    }

    public function getthevideos(){
        $missingpmg1 = DB::connection(self::$main_db)->select("SELECT * FROM pgfilter_videos where is_active=1 order by created_date desc limit 1");
        return json_encode($missingpmg1);
    }

    public function getothervideos($video_id1){
        $othervideos = DB::connection(self::$main_db)->select("SELECT * FROM pgfilter_videos where video_id!='$video_id1' and is_active=1 ORDER BY created_date DESC");
        return json_encode($othervideos);
    }

    public function getothervideosontop($other_video_id){
        $othervideosnames = DB::connection(self::$main_db)->select("SELECT * FROM pgfilter_videos where video_id='$other_video_id' and is_active=1 limit 1");
        return json_encode($othervideosnames);
    }

    public function getsearchvideo($get_videossearchterm) {
        $searchedvideosnames = DB::connection(self::$main_db)->select("SELECT * FROM pgfilter_videos where video_name LIKE '%$get_videossearchterm%' and is_active=1 ORDER BY created_date DESC");
        return json_encode($searchedvideosnames);
    }


}
