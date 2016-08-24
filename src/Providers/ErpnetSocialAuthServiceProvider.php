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
        Form::component('customText', 'components.form.text',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
        Form::component('customCheckbox', 'components.form.checkbox',
            ['name', 'label' => null, 'value' => null, 'attributes' => [], 'checked' => false]);
        Form::component('customFile', 'components.form.file',
            ['name', 'label' => null, 'value' => null, 'attributes' => []]);
    }
}