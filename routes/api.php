<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{VoiceController,ChatBotApiController,StatisticsController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('convertfile', [VoiceController::class,'convertToFlac']);

Route::get('yearChatbotapi', [ChatBotApiController::class,'yearWhatsappChatbot']);
Route::get('makeChatbotapi/{year}', [ChatBotApiController::class,'makeWhatsappChatbot']);
Route::get('modelChatbotapi/{year}/{makeID}', [ChatBotApiController::class,'modelWhatsappChatbot']);
Route::get('engineChatbotapi/{bvid}', [ChatBotApiController::class,'engineWhatsappChatbot']);

Route::get('dynamicChatbotapi/{searchterm}/', [ChatBotApiController::class,'dynamicWhatsappChatbot']);
Route::get('productsChatbotapi/{bvid}/{enginebaseid}', [ChatBotApiController::class,'productsWhatsappChatbot']);

Route::post('insertDeviceTokens', [StatisticsController::class, 'insert_device_tokens']);
Route::post('getImageDimensionArray', [StatisticsController::class, 'get_image_dimensions'])->withoutMiddleware("throttle:api");





