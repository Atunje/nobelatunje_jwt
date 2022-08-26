<?php

namespace Nobelatunje\Jwt;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nobelatunje\Jwt\Commands\GenerateKeys;
use Nobelatunje\Jwt\Commands\InstallPackage;
use Nobelatunje\Jwt\Exceptions\InvalidUserProviderException;

class JWTServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [];

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishFiles();

        $this->registerPolicies();

        $this->setJWTGuard();
    }

    protected function publishFiles()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/' . Config::CONFIG_FILE . '.php' => config_path(Config::CONFIG_FILE . '.php'),
            ], 'config');

            $this->commands([
                InstallPackage::class,
                GenerateKeys::class,
            ]);
        }
    }

    /**
     * Get the policies defined on the provider.
     *
     * @return array<class-string, class-string>
     */
    protected function policies(): array
    {
        return $this->policies = config(Config::CONFIG_FILE . '.policies') ?? [];
    }

    /**
     * Register the application's policies.
     *
     * @return void
     */
    protected function registerPolicies(): void
    {
        foreach ($this->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    /**
     * Set up the JWT guard with the driver
     *
     * @return void
     */
    protected function setJWTGuard(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            //use the user provider specified in the app config file
            $provider = Auth::createUserProvider($config['provider']);

            if($provider !== null) {
                return new JWTGuard($provider, $app->make('request'));
            }

            throw new InvalidUserProviderException("UserProvider cannot be null");
        });
    }
}
