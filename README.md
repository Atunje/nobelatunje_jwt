# Nobelatunje/Jwt

A simple JSON Web Token Authentication Library built on top of **[lcobucci/jwt](https://github.com/lcobucci/jwt)** for Laravel and Lumen. 

It uses Asymmetric Algorithm using a **private key** for signature creation and a **public key** for verification. This means that it's fine to distribute your **public key**. However, the **private key** should **remain secret**.

## Laravel Installation

Via composer

    composer require nobelatunje/jwt

Install the package

    php artisan jwt:install

Generate private and public keys

    php artisan jwt:generate

Modify the jwtconfig.php in your config file as necessary and add your app's Policies if necessary.

Change the route driver in your auth.php config file to jwt.

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

## Auth Guard Usage

### Login

    // Generate a token for the user if the credentials are valid
    $token = Auth::attempt($credentials);

### User
    
    // Get the currently authenticated user
    $user = Auth::user();


