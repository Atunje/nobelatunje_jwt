<?php

return [

    /**
     * User Identification field to attach jwt token to user
     */
    "user_id_field" => "id",

    /**
     * Private key for signing jwt token
     */
    "private_key" => strval( env('JWT_PRIVATE_KEY') ),

    /**
     * Public key for signing jwt token
     */
    "public_key" => strval( env('JWT_PRIVATE_KEY') ),

    /**
     * Life span of the jwt in seconds
     */
    "token_life" => intval( env("JWT_TOKEN_LIFE", 3600) ),

    /**
     * Token Issuer
     */
    "issuer" => env("APP_URL"),

    /**
     * Policies
     */
    "policies" => [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ]
];
