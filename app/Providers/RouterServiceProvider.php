<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Services\Router\RouteServices;
class RouterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Scraper\Services\LinkScraper', function ($app) {
            return new LinkScraper();
        });
    }
}