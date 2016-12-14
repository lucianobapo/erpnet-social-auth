<?php

namespace ErpNET\SocialAuth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @param Request $request
     * @return Response
     */
    public function redirectToProvider($provider, Request $request)
    {
        $args = $request->all();

        if (isset($args['back']))
            $this->cache->put(md5($_SERVER['REMOTE_ADDR']), $args['back'], 5);
//            $request->cookie('back', $args['back']);

        return $this->callSocialiteDriver($provider)->redirect();
    }

    /**
     * @param $provider
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function callSocialiteDriver($provider)
    {
        $driver = Socialite::driver($provider);
        if (method_exists($driver, 'fields') && is_callable([$driver, 'fields']))
            $driver->fields(config('erpnetSocialAuth.socialLogin.'.$provider.'.fields'));

        if (method_exists($driver, 'scopes') && is_callable([$driver, 'scopes']))
            $driver->scopes(config('erpnetSocialAuth.socialLogin.'.$provider.'.scopes'));

        return $driver;
    }

    /**
     * Obtain the user information from Provider.
     *
     * @param $provider
     * @param Request $request
     * @return Response
     */
    public function handleProviderCallback($provider, Request $request)
    {
        if (array_search($provider, config('erpnetSocialAuth.socialLogin.availableProviders'))===false)
            dd($provider);

        $state = $request->get('state');
        $request->session()->put('state',$state);

        if(\Auth::check()==false){
            session()->regenerate();
        }

        $abstractProvider = $this->callSocialiteDriver($provider);
//        dd($abstractProvider);
        $user = $abstractProvider->user();
//        dd($user);
        return $this->processSocialUser($provider, $user, $request);
    }

    /**
     * @param string $provider
     * @param $socialUser
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function processSocialUser($provider, $socialUser, Request $request)
    {
        $userFromDatabase = $this->userService->findFirst([
            'provider_name' => $provider,
            'provider_id' => $socialUser->getId(),
        ]);

        if (is_null($userFromDatabase)) {
            $validatorSocialUser = $this->validatorSocialUser([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
            ]);

            if ($validatorSocialUser->fails()) {
                return redirect()->to('register')
                    ->withErrors($validatorSocialUser->getMessageBag()->get('email'));
            }

            $userFromDatabase = $this->userService->create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(config('services.' . $provider . '.client_id')),
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        Auth::guard($this->getGuard())->login($userFromDatabase);

        if ($this->cache->has(md5($_SERVER['REMOTE_ADDR'])))
            return redirect($this->cache->get(md5($_SERVER['REMOTE_ADDR'])));
        else
            return redirect($this->redirectPath());
    }
}