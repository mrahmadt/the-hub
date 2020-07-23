<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

//TODO: do we need to check Azure AD for each action?
//TODO: How to setup a time out after inactivity

class LoginController extends Controller
{
    public function __construct()
    {

        //$this->middleware('guest', ['except' => 'logout']);
    }

    protected $providers = [
        'graph'
    ];

    public function show(Request $request)
    {
        print "SHOW";
        $data = $request->session()->all();
        dd($data);
        if(count($this->providers)>1){
            return view('auth.login');
        }else{
            return redirect('/login/'.$this->providers[0]);
        }
    }

    public function logout() {
        Auth::logout();
        return view('user.logout');
    }

    /**
     * Redirect the user to the  authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($driver)
    {
        if( ! $this->isProviderAllowed($driver) ) {
            return $this->sendFailedResponse("{$driver} is not currently supported");
        }

        try {
            return Socialite::driver($driver)->redirect();
        } catch (Exception $e) {
            // You should show something simple fail message
            return $this->sendFailedResponse($e->getMessage());
        }
    }

    /**
     * Obtain the user information from provider.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($driver)
    {
        try {
            $user = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            return redirect('/login/'.$driver);
        }

        // check for email in returned user
        return empty( $user->email )
        ? $this->sendFailedResponse("No email id returned from {$driver} provider.")
        : $this->loginOrCreateAccount($user, $driver);
    }

    protected function sendSuccessResponse()
    {
        return redirect()->intended('home');
    }

    protected function sendFailedResponse($msg = null)
    {
        return redirect()->route('login')
            ->withErrors(['msg' => $msg ?: 'Unable to login, try with another provider to login.']);
    }

    protected function loginOrCreateAccount($providerUser, $driver)
    {
        $provider_id = $providerUser->getId();
        // check for already has account
        $user = User::where(['provider'=> $driver, 'provider_id'=>$provider_id])->first();

        //TODO: if we have the email in database the creation will fail

        $avatar = null;
        try{
            $avatar = $providerUser->avatar;
        } catch (\Exception $e) {
        }

        // if user already found
        if( $user ) {
            // update the avatar and provider that might have changed
            $user->update([
                'name' => $providerUser->getName(),
                'avatar' => $avatar,
                //'provider' => $driver,
                //'provider_id' => $providerUser->id,
                'access_token' => $providerUser->token
            ]);
        } else {
            // create a new user
            $user = User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $avatar,
                'provider' => $driver,
                'provider_id' => $provider_id,
                'access_token' => $providerUser->token
            ]);
        }
        // login the user
        Auth::login($user, true);
        return $this->sendSuccessResponse();
    }

    private function isProviderAllowed($driver)
    {
        return in_array($driver, $this->providers) && config()->has("services.{$driver}");
    }

    
    public function teamsAuthStart(Request $request){
        return view('auth.teams-start',['clientId'=>config("app.graph_client_id")]);
    }
    
    public function teamsAuthEnd(Request $request){
        return view('auth.teams-end',['clientId'=>config("app.graph_client_secret")]);
    }

    public function teamsToken(Request $request){

        $driver = 'graph';
        $json = [
            'status' => 'error',
            'error' => 'Unknown Error',
        ];

        $post_content = $request->getContent();
        if($post_content=='') {
            throw new HttpResponseException(response()->json($json, 422)); 
        }

        $post_content = \json_decode($post_content);
        if(json_last_error()!=JSON_ERROR_NONE) exit;
        if($post_content->tid==''){
            throw new HttpResponseException(response()->json($json, 422)); 
        }
        
        if(isset($post_content->tid)){
            $tid = $post_content->tid;
        }else{
            return response()->json([
                'status' => 'error 1',
                'error' => 'Unknown Error',
            ],500);
        }
        if(isset($post_content->token)){
            $token = $post_content->token;
        }else{
            return response()->json([
                'status' => 'error 2',
                'error' => 'Unknown Error',
            ],500);
        }

        $scopes = ["https://graph.microsoft.com/User.Read"];

        $url = "https://login.microsoftonline.com/" . $tid . "/oauth2/v2.0/token";
        //$url = "https://login.windows.net/common/oauth2/token/" . $tid . "/oauth2/v2.0/token";
        
        $params = [
            'client_id' => config("app.graph_client_id"),
            'client_secret' => config("app.graph_client_secret"),
            'grant_type' => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            'assertion' => $token,
            'requested_token_use' => "on_behalf_of",
            'scope' => implode(' ',$scopes),
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER, [
                'Accept: "application/json"',
                '"Content-Type": "application/x-www-form-urlencoded"',
            ]
        ]);

        $apiResponse = curl_exec($ch);
        curl_close($ch); 

        $request->session()->put('session', 'ok');

        $apiResponse_array = \json_decode($apiResponse);
        if(isset($apiResponse_array->access_token)){
            try {
                $user = Socialite::driver( $driver )->userFromToken($apiResponse_array->access_token);
            } catch (\Exception $e) {
            }
            if((!isset($user->email)) || empty( $user->email )){
                return response()->json(['error'=>"No email id returned from {$driver} provider."],200);
            }else{
                $this->loginOrCreateAccount($user, $driver);
                return response()->json([url('/myapps')],200);
            }
        }elseif(isset($apiResponse_array->error)){
            return response()->json(['error'=>$apiResponse_array->error],200);
        }else{
            return response()->json(['error'=>'unknown'],200);
        }
    }
}
