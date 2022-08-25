<?php

namespace Nobelatunje\Jwt;

use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * The currently authenticated user.
     *
     * @var Authenticatable|null
     */
    protected $user;

    /**
     * Request
     *
     * @var Request
     */
    private Request $request;

    /**
     * Jwt implementation class of the selected jwt library
     *
     * @var TokenFactory
     */
    private TokenFactory $tokenFactory;

    /**
     * Constructor
     *
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;

        $this->request = $request;

        $this->tokenFactory = new TokenFactory();
    }

    /**
     * Get the user by validating the token from the request
     *
     * @return Authenticatable|null
     */
    public function user(): Authenticatable|null
    {
        if ($this->user === null) {
            $user = null;

            $token = $this->request->bearerToken();

            if (! empty($token)) {
                $user = $this->getTokenUser($token);
            }

            return $this->user = $user;
        }

        return $this->user;
    }

    /**
     * Get the user of the token
     *
     * @param string $token
     * @return Authenticatable|null
     */
    public function getTokenUser(string $token): ?Authenticatable
    {
        //get the jwt token
        $user_id = $this->tokenFactory->validate($token);

        if ($user_id !== null) {
            return $this->provider->retrieveById($user_id);
        }

        return null;
    }

    /**
     * Validate supplied credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        return $user !== null;
    }

    /**
     * Validates user's credentials and returns access token
     *
     * @param array $credentials
     * @return string|null
     */
    public function attempt(array $credentials = []): ?string
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user !== null && $this->provider->validateCredentials($user, $credentials)) {
            //generate a new access token
            $id_field = config('nobelatunje_jwt.user_id_field');
            return $this->tokenFactory->issueToken($user->$id_field);
        }

        return null;
    }
}
