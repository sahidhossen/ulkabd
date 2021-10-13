<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->elementComposer();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /*
     * Elements Routes
     */
    public function elementComposer(){
        view()->composer('elements.navbar','App\Http\Composers\NavigationComposer@navBarCompose');
        view()->composer('elements.sidebar','App\Http\Composers\NavigationComposer@sideBarCompose');
        view()->composer('elements.footer','App\Http\Composers\NavigationComposer@footerCompose');
    }
}
