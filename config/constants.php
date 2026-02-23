<?php
$publicUrl='';
if(env('APP_ENV','forge')=='production'){
    $publicUrl="/public/";
}
return [

    /*
    |--------------------------------------------------------------------------
    | User Defined Variables
    |--------------------------------------------------------------------------
    |
    | This is a set of variables that are made specific to this application
    | that are better placed here rather than in .env file.
    | Use config('your_key') to get the values.
    |
    */

    'public_url' => $publicUrl,
    'pg_dbname' => env("DB_DATABASE"),
    'pg_secdbname' => env("DB_DATABASE_ACES"),
    // 'pg_dbname' => 'pg_main_server',
    // 'pg_secdbname' => 'pg_sec_server',
    'imagepath' => 'https://images.pgfilters.com/assets/',
    'defaultpath' => 'https://images.pgfilters.com/images/',

];

?>