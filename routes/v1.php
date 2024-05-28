<?php

use App\Http\Controllers\v1\Asset\SoundControllerV1;
use App\Http\Controllers\v1\AuthControllerV1;
use App\Http\Controllers\v1\Channel\ChannelInvitationControllerV1;
use App\Http\Controllers\v1\Channel\PrivateChannelControllerV1;
use App\Http\Controllers\v1\Channel\PublicChannelControllerV1;
use App\Http\Controllers\v1\Chat\ChatControllerV1;
use App\Http\Controllers\v1\CommonControllerV1;
use App\Http\Controllers\v1\FavouriteChannelControllerV1;
use App\Http\Controllers\v1\Friends\FriendControllerV1;
use App\Http\Controllers\v1\Friends\FriendRequestControllerV1;
use App\Http\Controllers\v1\Notifications\NotificationControllerV1;
use App\Http\Controllers\v1\Profile\ProfileController;
use App\Http\Controllers\v1\Report\ReportControllerV1;
use Illuminate\Support\Facades\Route;

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
Route::get('generateHeaderToken', [AuthControllerV1::class, 'generateHeaderToken']);

// Route::get('/upload_sound', [SoundControllerV1::class, 'uploadSounds']);

Route::group(['middleware' => ['isHeaderValid', 'apiLog']], function () {
    Route::post('auth', [AuthControllerV1::class, 'authenticate']);
    Route::post('checkUsername', [ProfileController::class, 'checkUsername']);
    Route::post('checkAccounts', [AuthControllerV1::class, 'checkAccounts']);
    Route::get('offensiveWords', [CommonControllerV1::class, 'offensiveWords']);
    Route::get('settings', [CommonControllerV1::class, 'appSettings']);
    Route::group(['middleware' => ['auth:api']], function () {
        Route::put('basicInfo', [ProfileController::class, 'updateBasicInfo']);
        Route::post('changeUsername', [ProfileController::class, 'updateUserName']);
        Route::post('logout', [AuthControllerV1::class, 'logout'])->middleware('isNotGuestUser');
        Route::delete('deleteAccount', [ProfileController::class, 'deleteAccount']);

        Route::post('sendOtp', [ProfileController::class, 'sendOtp']);
        Route::post('verifyOtp', [ProfileController::class, 'verifyOtp']);
        Route::get('searchUsersByPhoneNo', [ProfileController::class, 'fetchUserByPhoneNo']);

        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'index']);
            Route::post('/outfits', [ProfileController::class, 'updateUserOutfit']);
        });
        /** Friend Request APIs */
        Route::prefix('friendRequests')->group(function () {
            Route::post('/', [FriendRequestControllerV1::class, 'sentRequest']);
            Route::get('/', [FriendRequestControllerV1::class, 'getRequests']);
            Route::delete('/{requestId}', [FriendRequestControllerV1::class, 'deleteRequest']);
            Route::patch('/{requestId}/respond', [FriendRequestControllerV1::class, 'respondRequest']);
            Route::get('/suggestions', [FriendRequestControllerV1::class, 'getFriendSuggestions']);
        });
        /** Friends APIs */
        Route::prefix('friends')->group(function () {
            Route::get('/', [FriendControllerV1::class, 'friendList']);
            Route::delete('/{userId}', [FriendControllerV1::class, 'unfriend']);
        });
        /** User Suggestions */

        /** User Search API */
        Route::get('/searchUser', [ProfileController::class, 'searchUser']);
        /** Report User APIs */
        Route::get('reportTypes', [ReportControllerV1::class, 'index']);
        Route::post('reportUser', [ReportControllerV1::class, 'reportUser']);

        Route::prefix('privateChannels')->group(function () {
            Route::get('/', [PrivateChannelControllerV1::class, 'index']);
            Route::get('/{channelId}/members', [PrivateChannelControllerV1::class, 'getPrivateChannelMembers']);
            Route::post('/', [PrivateChannelControllerV1::class, 'store']);
            Route::patch('/{channelId}', [PrivateChannelControllerV1::class, 'edit']);
            Route::delete('/{channelId}', [PrivateChannelControllerV1::class, 'destroy']);
            Route::get('/checkFrequency', [PrivateChannelControllerV1::class, 'checkFrequency']);
            Route::prefix('invites')->group(function () {
                Route::post('/', [ChannelInvitationControllerV1::class, 'sendInvitation']);
                Route::patch('/{invitationId}/respond', [ChannelInvitationControllerV1::class, 'respondInvitation']);
            });
            Route::get('/ownedAndJoined', [PrivateChannelControllerV1::class, 'ownedAndJoinedChannel']);
            Route::patch('/members/leave', [PrivateChannelControllerV1::class, 'leaveChannel']);
            Route::patch('/members/kick', [PrivateChannelControllerV1::class, 'kickUser']);
        });

        Route::get('sounds', [SoundControllerV1::class, 'index']);
        Route::get('shuffleChannel', [PublicChannelControllerV1::class, 'shuffleChannel']);
        Route::get('channelUser/{userId}', [FriendControllerV1::class, 'checkUserRelationship']);

        Route::prefix('favouriteChannel')->group(function () {
            Route::post('/', [FavouriteChannelControllerV1::class, 'store']);
            Route::get('/', [FavouriteChannelControllerV1::class, 'index']);
            Route::delete('/', [FavouriteChannelControllerV1::class, 'destroy']);
        });

        Route::get('notifications', [NotificationControllerV1::class, 'index']);
        Route::prefix('pushNotificationStatus')->group(function () {
            Route::get('/', [NotificationControllerV1::class, 'getPushNotificationStatus']);
            Route::put('/', [NotificationControllerV1::class, 'updatePushNotificationStatus']);
        });

        Route::prefix('chat')->group(function () {
            Route::get('/threads', [ChatControllerV1::class, 'threads']);
        });
    });

});
