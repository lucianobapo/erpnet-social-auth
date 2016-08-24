<?php
return [
    //socialLogin configs
    'socialLogin' => [
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