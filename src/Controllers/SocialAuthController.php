<?php

namespace ErpNET\SocialAuth\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client as GuzzleClient;

class SocialAuthController extends Controller
{
    use AuthenticatesUsers;

    /*
     *
     * @var GuzzleClient
     */
    protected $guzzle;

    public function __construct()
    {
        $this->guzzle = new GuzzleClient([
            // Base URI is used with relative requests
//            'base_uri' => 'http://localhost:8080/',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
    }

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
        $socialUser = $abstractProvider->user();
//        dd($user);
        return $this->processSocialUser($provider, $socialUser, $request);
    }

    /**
     * @param string $provider
     * @param $socialUser
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function processSocialUser($provider, $socialUser, Request $request)
    {
        $response = $this->guzzle->request('GET', config('erpnetSocialAuth.userApiUrl'), [
            'debug' => false,
            'query' => ['search' => $socialUser->id],
            'headers' => [
                'Accept'     => config('erpnetSocialAuth.userApiHeader'),
            ]
        ]);

        if ($response->getStatusCode() != 200) throw new \Exception('Resposta não é 200');

        $responseUser = json_decode($response->getBody()->getContents());

        if (isset($responseUser->data) && count($responseUser->data)==1) {
            \Auth::loginUsingId($responseUser->data[0]->id);

            return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
        } elseif (isset($responseUser->data) && count($responseUser->data)==0)  {
            $response2 = $this->guzzle->request('POST', config('erpnetSocialAuth.userApiUrl'), [
                'debug' => false,
                'form_params' => [
                    'mandante' => 'ilhanet',
                    'name' => $socialUser->name,
                    'avatar' => $socialUser->avatar,
                    'password' => bcrypt($socialUser->id),
                    'username' => $socialUser->nickname,
                    'email' => $socialUser->email,
                    'provider' => $provider,
                    'provider_id' => $socialUser->id,
                ],
                'headers' => [
                    'Accept'     => config('erpnetSocialAuth.userApiHeader'),
                ]
            ]);

            if ($response2->getStatusCode() != 200) throw new \Exception('Resposta não é 200');

            $responseUser2 = json_decode($response->getBody()->getContents());

            dd($responseUser2);

            \Auth::loginUsingId($responseUser2->data[0]->id);

            return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());

        };

//        if ($this->cache->has(md5($_SERVER['REMOTE_ADDR'])))
//            return redirect($this->cache->get(md5($_SERVER['REMOTE_ADDR'])));
//        else
//            return redirect($this->redirectPath());
    }
}