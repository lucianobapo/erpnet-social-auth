<?php
return [
    'redirectTo' => '/erpnet-api',
    'userApiUrl' => env('ERPNET_SOCIAL_AUTH_USER_API_URL', 'http://localhost:8080/erpnet-api/user'),
    'userApiHeader' => 'application/x.erpnet.v1+json',
    //socialLogin configs
    'socialLogin' => [
        'services' => [
            'google' => [
                'client_id' => env('GOOGLE_CLIENT_ID', 'your-app-id'),
                'client_secret' => env('GOOGLE_CLIENT_SECRET', 'your-app-secret'),
                'redirect' => env('GOOGLE_REDIRECT', 'http://your-callback-url'),
            ],
            'facebook' => [
                'client_id' => env('FACEBOOK_CLIENT_ID', 'your-app-id'),
                'client_secret' => env('FACEBOOK_CLIENT_SECRET', 'your-app-secret'),
                'redirect' => env('FACEBOOK_REDIRECT', 'http://your-callback-url'),
            ],
        ],
        'availableProviders' => explode(',', env('SOCIAL_LOGIN_PROVIDERS', 'laravel')),

        'google' => [
            'scopes' => [
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/plus.me',
                'https://www.googleapis.com/auth/userinfo.profile',
            ],
            'fields' => [],
        ],
        'facebook' => [
            'scopes' => ['email','user_birthday','user_friends'],
            'fields' => [
                //public_profile
                'id',
                'name',
                'first_name',
                'last_name',
                'age_range',
                'link',
                'gender',
                'locale',
                'picture',
                'timezone',
                'updated_time',
                'verified',
                //email
                'email',
                'birthday',
                'friends',
            ],
        ],
    ],
];