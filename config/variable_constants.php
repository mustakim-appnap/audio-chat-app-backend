<?php

return [
    'activation' => [
        'active' => 1,
        'inactive' => 0,
    ],
    'check' => [
        'yes' => 1,
        'no' => 0,
    ],
    'channel_types' => [
        'public' => 0,
        'private' => 1,
    ],
    'friend_request_status' => [
        'pending' => 0,
        'accept' => 1,
        'decline' => 2,
    ],
    'otp_expiration_time' => 60 * 60, //3 Minutes (180 sec)
    'age_restriction' => 13,

    'pagination' => [
        'friend_requests' => 20,
        'friends' => 20,
        'search_users' => 20,
        'channel_members' => 50,
        'notifications' => 20,
        'friend_suggestions' => 20,
    ],
    'default' => [
        'username' => 'GuestUser',
        'user_avatar' => 'user_avatar.png',
        'public_channel' => '00.00',
        'username_change_interval_in_days' => 30,
        'user_password' => 'welcome',
    ],

    'public_channel' => [
        'max_user' => 5,
    ],
    'private_channel' => [
        'max_user' => 50,
    ],
    'suggestions' => [
        'users_per_channel' => 5,
    ],

    'notification_types' => [
        'friend_request' => 1,
        'channel_invitation' => 2,
        'message' => 3,
        'promotional' => 4,
    ],
    'app_env' => env('APP_ENV'),
    'azure' => [
        'container' => env('AZURE_STORAGE_CONTAINER'),
        'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
    ],
];
