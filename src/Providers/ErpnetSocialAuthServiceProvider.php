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
        // TODO: Implement register() method.
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'../../resources/views', 'erpnetSocialAuth');

        $this->publishes([
            __DIR__.'../../config/erpnetSocialAuth.php' => config_path('erpnetSocialAuth.php'),
            __DIR__.'../../resources/views' => base_path('resources/views/vendor/erpnetSocialAuth'),
//            __DIR__.'/Migrations' => base_path('database/migrations'),
        ]);

//        $this->app->config->set('auth.model', $this->app->config->get('easyAuthenticator.model'));

//        include __DIR__.'/routes.php';

        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);

        Form::component('customText', 'components.form.text',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
        Form::component('customCheckbox', 'components.form.checkbox',
            ['name', 'label' => null, 'value' => null, 'attributes' => [], 'checked' => false]);
        Form::component('customFile', 'components.form.file',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
    }
}