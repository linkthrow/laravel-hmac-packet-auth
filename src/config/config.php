<?php return [
	
	'turnedOffForLocal'	=>	env('HMAC_AUTH_LOCAL', true),
    'rateLimit' => [
        'turnedOn' => env('HMAC_AUTH_RATE_ON', true),
        'timePeriod' => env('HMAC_AUTH_RATE_TIME', 60),
        'limitNumber' => env('HMAC_AUTH_RATE_LIMIT_NUMBER', 60),
    ],

];
