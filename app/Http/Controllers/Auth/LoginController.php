<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

//TODO: do we need to check Azure AD for each action?
//TODO: How to setup a time out after inactivity

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected $providers = [
        'azure'
    ];

    public function show()
    {
        if(count($this->providers)>1){
            return view('auth.login');
        }else{
            return redirect('/login/'.$this->providers[0]);
        }
    }

    public function logout() {
        Auth::logout();
        return view('user.logout');
        //return redirect()->route('login');
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

        // if user already found
        if( $user ) {
            // update the avatar and provider that might have changed
            $user->update([
                'name' => $providerUser->getName(),
                'avatar' => $providerUser->avatar,
                //'provider' => $driver,
                //'provider_id' => $providerUser->id,
                'access_token' => $providerUser->token
            ]);
        } else {
            // create a new user
            $user = User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
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

    
    public function teamsToken(Request $request){

        $output = [
            'status' => 'error',
            'error' => 'Unknown Error',
        ];

        if($request->has('tid')){
            $tid = $request->input('tid');
        }else{
            return response()->json($output,500);
        }
        if($request->has('token')){
            $token = $request->input('token');
        }else{
            return response()->json($output,500);
        }

        $scopes = ["https://graph.microsoft.com/User.Read"];

        $url = "https://login.microsoftonline.com/" . $tid . "/oauth2/v2.0/token";


        $params = [
            'client_id' => config("app.azure_ad_key"),
            'client_secret' => config("app.azure_ad_secret"),
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch); 

        return response()->json(['code'=>$httpCode,'status'=>'ok','ok'=>true,'error'=>false],200);
        print_r($httpCode);
        dd($apiResponse);
        //$jsonArrayResponse - json_decode($apiResponse);

/*
        var tid = req.body.tid;
        var token = req.body.token;
        var scopes = ["https://graph.microsoft.com/User.Read"];

        var oboPromise = new Promise((resolve, reject) => {
            const url = "https://login.microsoftonline.com/" + tid + "/oauth2/v2.0/token";
            const params = {
                client_id: config.get("tab.appId"),
                client_secret: config.get("tab.appPassword"),
                grant_type: "urn:ietf:params:oauth:grant-type:jwt-bearer",
                assertion: token,
                requested_token_use: "on_behalf_of",
                scope: scopes.join(" ")
            };
        
            fetch(url, {
              method: "POST",
              body: querystring.stringify(params),
              headers: {
                Accept: "application/json",
                "Content-Type": "application/x-www-form-urlencoded"
              }
            }).then(result => {
              if (result.status !== 200) {
                result.json().then(json => {
                  // TODO: Check explicitly for invalid_grant or interaction_required
                  reject({"error":json.error});
                });
              } else {
                result.json().then(json => {
                  resolve(json.access_token);
                });
              }
            });
        });

        oboPromise.then(function(result) {
            res.json(result);
        }, function(err) {
            console.log(err); // Error: "It broke"
            res.json(err);
        });

*/
    }
}
