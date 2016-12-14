<?php

/**
 * Created by PhpStorm.
 * User: luciano
 * Date: 24/08/16
 * Time: 02:23
 */
namespace ErpNET\SocialAuth\Providers;

use Illuminate\Support\ServiceProvider;
use Collective\Html\FormFacade as Form;

class ErpnetSocialAuthServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $projectRootDir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
        $routesDir = $projectRootDir."routes".DIRECTORY_SEPARATOR;

        //Routing
        include $routesDir."web.php";

        $this->loadViewsFrom($projectRootDir.'resources/views', 'erpnetSocialAuth');

        $this->publishes([
            $projectRootDir.'config/erpnetSocialAuth.php' => config_path('erpnetSocialAuth.php'),
            $projectRootDir.'resources/views' => base_path('resources/views/vendor/erpnetSocialAuth'),
//            __DIR__.'/Migrations' => base_path('database/migrations'),
        ]);

        $this->app->config->set('services', array_merge($this->app->config->get('services'), $this->app->config->get('erpnetSocialAuth.socialLogin.services')));

        Form::component('customText', 'components.form.text',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
        Form::component('customCheckbox', 'components.form.checkbox',
            ['name', 'label' => null, 'value' => null, 'attributes' => [], 'checked' => false]);
        Form::component('customFile', 'components.form.file',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
    }
}