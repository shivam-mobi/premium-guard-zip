<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{ClassicSearchController,DynamicController,ProductListController,QualifierController};
use App\Http\Controllers\{ProductCompController,LateModelController,VoiceController,TechnicalResources,StatisticsController};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/', function () {
//     return view('mainFile');
// });





Route::get('yearCollection/{vehicletypeid}/', [ClassicSearchController::class,'index']);
Route::get('qualifierCollection/{baseVehicleID}/{engineID}/{qualifierSelected}/{parttype}/{partsubtype}/',[QualifierController::class,'index']);
Route::get('dynamicCollection/{searchterm}/',[DynamicController::class, 'index']);
Route::get('dyanamicCollection_new/{searchterm}/',[DynamicController::class, 'index']);
Route::get('dyanamicCollection/{searchterm}/',[DynamicController::class, 'index']);
Route::get('makeCollection/{vehicletypeid}/{yearVal}/{projectname?}', [ClassicSearchController::class,'getmakes']);
Route::get('modelCollection/{vehicletypeid}/{yearVal}/{makeVal}/{projectname?}',[ClassicSearchController::class,'getmodels']);
Route::get('engineCollection/{baseVehicleID}/', [ClassicSearchController::class,'getengines']);
Route::get('engineCheckCollection/{baseVehicleID}/', [ClassicSearchController::class,'checkforengine']);


Route::get('qualifiersforAllPartTypes/{baseVehicleID}/{engineID}/{qualifierSelected}/{parttype}/{partsubtype}/',[QualifierController::class,'qualifiersforAllPartTypes']);

Route::get('qualifierCollectionPureFlow/{baseVehicleID}/{engineID}/{qualifierSelected}/{parttype}/{partsubtype}/',[QualifierController::class,'qualifiercheck']);
Route::get('vinCollection/{searchterm_vin}',[DynamicController::class,'vinsearch']);

//for Mobikasa 
Route::get('engineCheckAllPartTypes/{baseVehicleID}/', [ClassicSearchController::class,'checkforengineallpart']);
Route::get('engineCheckPureFlow/{baseVehicleID}/', [ClassicSearchController::class,'enginecheckpureflow']);
Route::get('vinCollectionMobikasa/{searchterm_vin}',[DynamicController::class,'vinsearchMobikasa']);
Route::get('yearCollectionDrain/{vehicletypeid}/', [ClassicSearchController::class,'yearDrainPlugs']);


Route::get('productList/{baseVehicleID}/{engineID}/{qualifier_id}/{qualifier_name}/', [ProductListController::class,'productlist']);

Route::get('productList1/{baseVehicleID}/{engineID}/{qualifier_id}/{qualifier_name}/', [ProductListController::class,'productlist1']);

Route::get('productPage/{partNumber}/',[ClassicSearchController::class,'productpage']);
Route::get('prodApplication/{partNumber}/',[ClassicSearchController::class,'prodapplication']);
Route::get('prodApplication_new/{partNumber}/',[ClassicSearchController::class,'prodapplication_new']);
Route::get('prodApplication1/{partNumber}/{makeID}/{modelID}/{engineConfigID}/{min}/{max}',[ClassicSearchController::class,'prodapplication1']);
Route::get('prodapplicationCount/{partNumber}/', [ClassicSearchController::class,'prodapplication_count']);
Route::get('partInterchanges/{partNumber}/',[ClassicSearchController::class,'partinterchanges']);
Route::get('beaheadCollection', [ClassicSearchController::class,'beahead']);
Route::post('postContactForm',[ClassicSearchController::class,'postContactForm']);
// Route::get('premiumCollection/{partNumber}',[ClassicSearchController::class,'premiumcollection']);
Route::get('beaheadymm/{partNumber}', [ClassicSearchController::class,'beaheadymm']);
Route::get('productListCount/{yearVal}/{makeVal}/{modelVal}/{engineVal}/',[ClassicSearchController::class,'searchcount']);
Route::get('notFoundCount/{searchterm}/',[DynamicController::class,'noresultcount']);
Route::get('partNumberCount/{partnumber}/',[DynamicController::class,'partsearchcount']);
Route::get('vehicletypeCollection', [ClassicSearchController::class,'vehicletypeCollection']);

Route::get('getblogs', [TechnicalResources::class,'gettheblogs']);
Route::get('blogSearch/{searchtext}',[TechnicalResources::class,'blogsearch']);

Route::get('technicalTips', [TechnicalResources::class,'gettechtips']);
Route::get('techTipsSearch/{searchtext}',[TechnicalResources::class,'techtipsearch']);

Route::get('geteachpostdetails/{postid}',[TechnicalResources::class,'geteachpostdetails']);
Route::get('geteachpostimage/{postid}', [TechnicalResources::class,'geteachpostimage']);
Route::get('getpostsimages/{postid}', [TechnicalResources::class,'getpostsimages']);


Route::get('technicalBulletinsSearch/{searchtext}',[TechnicalResources::class,'bulletinssearch']);
Route::get('technicalBulletins', [TechnicalResources::class,'bulletins']);

Route::get('getvideos', [TechnicalResources::class,'getthevideos']);
Route::get('videosone/{video_id1}',[TechnicalResources::class,'getothervideos']);
Route::get('videos/{other_video_id}',[TechnicalResources::class,'getothervideosontop']);
Route::get('videoSearch/{get_videossearchterm}',[TechnicalResources::class,'getsearchvideo']);
Route::get('partInterchangeResponse/{int_name}/{int_no}/',[DynamicController::class,'interchangeresult']);
Route::get('partInterchangeSuperseded/{partNumber}/', [DynamicController::class,'intersuperseded']);

//for product comparison
Route::get('allyears', [ProductCompController::class,'all_years']);
Route::post('allproducts',[ProductCompController::class,'all_products']);
Route::post('allmakemodel',[ProductCompController::class,'all_makemodel']);

//for late model coverage
Route::get('lateyears', [LateModelController::class,'index']);
Route::get('lateallresult/{year}/{id?}/{parttypename?}', [LateModelController::class,'allresult']);

// //insert/update count for keyword/dynamic search
Route::get('keywordSearchCount', [DynamicController::class, 'keywordSearchCount']);

Route::post('StatisticsAppCrossRef', [StatisticsController::class, 'index']);

Route::get('keywordSearchCount', [DynamicController::class, 'keywordSearchCount']);

Route::post('voicesearch',[VoiceController::class,'voicesearch']);

Route::post('convertfile',[VoiceController::class,'convertToFlac']);

Route::get('dynamicCollection/pt/{searchterm}',[DynamicController::class,'parttype']);

//for old apps
Route::get('productList/{baseVehicleID}/{engineID}/{qualifier_id}/{qualifier_name}/', [ProductListController::class,'productlist']);


Route::get('/send_notifications', function () {
    return view('send_notifications');
});


Route::get('getDeviceTokens', [StatisticsController::class, 'get_device_tokens']);


Route::get('getBearerToken', [StatisticsController::class, 'get_bearer_token']);
Route::post('insertDeviceTokens', [StatisticsController::class, 'insert_device_tokens']);

// Adding versioning to routes - version v1
Route::prefix('v1')->group(function () {
    Route::get('makeCollection/{vehicletypeid}/{yearVal}/{projectname?}', [ClassicSearchController::class,'getmakes']);
    Route::get('modelCollection/{vehicletypeid}/{yearVal}/{makeVal}/{projectname?}',[ClassicSearchController::class,'getmodels']);
});