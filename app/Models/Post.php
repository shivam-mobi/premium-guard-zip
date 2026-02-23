<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Log;

class Post extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    


    public static function getCurlResponse($api_url) {

        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL,$api_url);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($cURL);
        curl_close($cURL);

      
        if($response!='') {
            $dataBlogs=json_decode($response,true);
            return $dataBlogs;
        }
        else {
            return [];
        }

    }

    public static function blogSearch() {
        $blogs_search_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts?categories=2619&per_page=100';
        $res = self::getCurlResponse($techtips_url);
        return $res;
    }

    public static function techTipsSearch() {
        $techtips_search_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts?categories=512&per_page=100';
        $res = self::getCurlResponse($techtips_search_url);
        return $res;
    }

    public static function customMethod() {
        $techtips_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts?categories=512&per_page=100';
        $res = self::getCurlResponse($techtips_url);
        return $res;
    }

    public static function customMethodforBlogs() {
        $blogs_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts?categories=2619&per_page=100';
        $res = self::getCurlResponse($blogs_url);
        return $res;
    }

    public static function eachPost($post_id) {
        $each_post_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts/'.$post_id;
        $res = self::getCurlResponse($each_post_url);
        return $res;
    }

    public static function postImage($post_id) {
        $image_url = 'https://www.pgfilters.com/wp-json/wp/v2/posts/'.$post_id.'?_embed';
        $res = self::getCurlResponse($image_url);
        return $res;
    }

    public static function allImages($post_id) {
        $all_image_url = 'https://www.pgfilters.com/wp-json/wp/v2/media?parent='.$post_id;
        $res = self::getCurlResponse($all_image_url);
        return $res;
    }

}
