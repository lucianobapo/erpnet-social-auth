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
        $this->redirectTo = config('erpnetSocialAuth.redirectTo');
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
        if (session()->has("_previous")){
            session()->put('back', session("_previous")['url']);
        }

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
     * @throws \Exception
     */
    protected function processSocialUser($provider, $socialUser, Request $request)
    {
        $responseSearch = $this->userApiSearch($socialUser);
        $userFound = json_decode($responseSearch->getBody()->getContents());

        if (isset($userFound->data) && count($userFound->data)==1)
            return $this->loginRedirect($request, $userFound->data[0]->id);
        elseif (isset($userFound->data) && count($userFound->data)==0)  {
            $responseCreate = $this->userApiCreate($provider, $socialUser);

            $userCreated = json_decode($responseCreate->getBody()->getContents());

            if ($userCreated->data->provider_id == $socialUser->id)
                return $this->loginRedirect($request, $userCreated->data->id);
            else
                throw new \Exception('Erro na criação do usuário');
        } else
            throw new \Exception('Erro na busca do usuário');

//        if ($this->cache->has(md5($_SERVER['REMOTE_ADDR'])))
//            return redirect($this->cache->get(md5($_SERVER['REMOTE_ADDR'])));
//        else
//            return redirect($this->redirectPath());
    }

    /**
     * @param $socialUser
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function userApiSearch($socialUser)
    {
        $response = $this->guzzle->request('GET', config('erpnetSocialAuth.userApiUrl'), [
            'debug' => false,
            'query' => ['search' => $socialUser->id],
            'headers' => [
                'Accept' => config('erpnetSocialAuth.userApiHeader'),
            ]
        ]);

        if ($response->getStatusCode() != 200) throw new \Exception('Resposta não é 200');

        return $response;
    }

    /**
     * @param $provider
     * @param $socialUser
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function userApiCreate($provider, $socialUser)
    {
        $response = $this->guzzle->request('POST', config('erpnetSocialAuth.userApiUrl'), [
            'debug' => false,
            'form_params' => [
                'mandante' => config('erpnetSocialAuth.mandante'),
                'name' => $socialUser->name,
                'avatar' => $socialUser->avatar,
                'password' => $socialUser->id,
                'username' => $socialUser->nickname,
                'email' => $socialUser->email,
                'provider' => $provider,
                'provider_id' => $socialUser->id,
            ],
            'headers' => [
                'Accept' => config('erpnetSocialAuth.userApiHeader'),
            ]
        ]);

        if ($response->getStatusCode() != 200) throw new \Exception('Resposta não é 200');

        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function loginRedirect(Request $request, $id)
    {
        \Auth::loginUsingId($id);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended(session()->has("back")?session("back"):$this->redirectPath());
    }
}