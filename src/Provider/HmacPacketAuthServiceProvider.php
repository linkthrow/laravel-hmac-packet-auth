<?php namespace LinkThrow\HmacPacketAuth\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * A Laravel 5's package template.
 *
 * @author: Rémi Collin 
 */
class HmacPacketAuthServiceProvider extends ServiceProvider {

    protected $packageName = 'hmacPackageAuth';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router, \Illuminate\Contracts\Http\Kernel $kernel)
    {
        // Register your migration's publisher
        $this->publishes([
            __DIR__.'/../database/migrations/' => base_path('/database/migrations')
        ], 'migrations');

        // Publish your config
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path($this->packageName.'.php'),
        ], 'config');

        //router middleware
        $router->middleware('auth.hmac', \LinkThrow\HmacPacketAuth\Middleware\HttpPacketTokenCheckMiddleware::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__.'/../config/config.php', $this->packageName);
    }

}
