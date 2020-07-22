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

    
    public function teamsAuthStart(Request $request){
        return view('auth.teams-start',['clientId'=>config("app.azure_ad_key")]);
    }
    
    public function teamsAuthEnd(Request $request){
        return view('auth.teams-end',['clientId'=>config("app.azure_ad_key")]);
    }

    public function teamsToken(Request $request){

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
        $apiResponse_array = \json_decode($apiResponse);
        if(isset($apiResponse_array->access_token)){
            return response()->json($apiResponse_array->access_token,200);
        }elseif(isset($apiResponse_array->error)){
            return response()->json(['error'=>$apiResponse_array->error],200);
        }else{
            return response()->json(['error'=>'unknown'],200);
        }
        /*

        {\"error\":\"invalid_grant\"
            ,\"error_description\":\"AADSTS65001: The user or administrator has not consented to use the application with ID '53cda29e-7798-494b-8cf0-b0b4b50ca52d' named 
            'The Hub'. Send an interactive authorization request for this user and resource.\\r\\nTrace ID: 87800519-8aec-4ae9-8c4d-50464ea11f00\\r\\nCorrelation ID: ce79db0
            0-c21a-4037-bde1-d46eb64d595e\\r\\nTimestamp: 2020-07-22 09:49:26Z\",\
            "error_codes\":[65001],\"timestamp\":\"2020-07-22 09:49:26Z\",\"trace_id\":\"87800519-8aec-4ae9-8c4d-50464ea11f00\",
            \"correlation_id\":\"ce79db00-c21a-4037-bde1-d46eb64d595e\",\"suberror\":\"consent_required\"}

""{\"token_type\":\"Bearer\",\"scope\":\"email openid profile https:\/\/graph.microsoft.com\/User.Read\"
    ,\"expires_in\":6117,\"ext_expires_in\":6117,\"access_token\":\"eyJ0eXAiOiJKV1QiLCJub25jZSI6IktzUDZkQ2Q1Q2hwTVJiRGVNbEdxc3NRMkZwYj
    Z3MFY0WnVfMWh3NzZaZm8iLCJhbGciOiJSUzI1NiIsIng1dCI6Imh1Tjk1SXZQZmVocTM0R3pCRFoxR1hHaXJuTSIsImtpZCI6Imh1Tjk1SXZQZmVocTM0R3pCRFoxR1hHaXJuTSJ9.eyJhdWQiOiJodHRwczovL2dyYX
    BoLm1pY3Jvc29mdC5jb20iLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC8yMmIxNTY3Ny1hOWFlLTRiNjYtOTA2NC1jYmRlNWYxM2IwNmYvIiwiaWF0IjoxNTk1NDMwODg0LCJuYmYiOjE1OTU0MzA4ODQsImV
    4cCI6MTU5NTQzNzMwMiwiYWNjdCI6MCwiYWNyIjoiMSIsImFpbyI6IkFTUUEyLzhRQUFBQXg4WU9vbHlzaS9UMUVsMFFzUXJ2R05jUzlKb29iMmxWbEpEWTdjbUQrTFk9IiwiYW1yIjpbInB3ZCJdLCJhcHBfZGlzcGxheW5hb
    WUiOiJUaGUgSHViIiwiYXBwaWQiOiI1M2NkYTI5ZS03Nzk4LTQ5NGItOGNmMC1iMGI0YjUwY2E1MmQiLCJhcHBpZGFjciI6IjEiLCJmYW1pbHlfbmFtZSI6IkFsVHdhaWppcnkiLCJnaXZlbl9uYW1lIjoiQWhtYWQiLCJpcGFk
    ZHIiOiI3Ny4zMC4xMDcuMTYxIiwibmFtZSI6IkFobWFkIEFsVHdhaWppcnkiLCJvaWQiOiJmMzgwZjVkYi1iMmJhLTQyZjktODI3YS00MTQ4ODU1MzRhYmMiLCJwbGF0ZiI6IjMiLCJwdWlkIjoiMTAwMzIwMDBEMjFFREY1NCIsIn
    NjcCI6ImVtYWlsIG9wZW5pZCBwcm9maWxlIFVzZXIuUmVhZCIsInN1YiI6IjZ5VmU1ZUVEeUVyMlRMVEYxSVVCZDRCa2dNN2dwYTl3WVhmR2hpWFFwNE0iLCJ0ZW5hbnRfcmVnaW9uX3Njb3BlIjoiRVUiLCJ0aW
    QiOiIyMmIxNTY3Ny1hOWFlLTRiNjYtOTA2NC1jYmRlNWYxM2IwNmYiLCJ1bmlxdWVfbmFtZSI6ImFobWFkdEBhaG1hZHQwMDAub25taWNyb3NvZnQuY29tIiwidXBuIjoiYWhtYWR0QGFobWFkdDAwMC5vbm1pY3Jvc29md
    C5jb20iLCJ1dGkiOiJ6TzRURXA1QmQwcVl3S3RfTVpReEFBIiwidmVyIjoiMS4wIiwid2lkcyI6WyI2MmU5MDM5NC02OWY1LTQyMzctOTE5MC0wMTIxNzcxNDVlMTAiXSwieG1zX3N0Ijp7InN1YiI6IkZWVHdPUnJrakp
    ZRWZSdF9ZdUlvWFhSbUhYeGVKaGdadWkzRFpPSkhsSjAifSwieG1zX3RjZHQiOjE1OTUzOTEyMDh9.NdVFk4rWspg7btMhJ4q81Zah7ZB3dv1BcwysW0ELG2ABe-0Nc85pIGYMWAeD1HtzFUGNP_w
    8aborYigONnuiHuOk90sQu4O7ncGiZ1fHmGrCRgL-5fbOfkZd8N3fL3on2s8E6ElGRCnLFhZITy5S0W44IdzHTJdPeSmVe-8dqXysz2lKqn9cAUaUBztTucZvyhbLhCQ6hY1VQD4rI8IlKXtl9i17UAfleBd
    OZVM2IoLJ3PJGsGecSU_vqz_jyKCHeqA5sFqyn4wIaXD8yLrgKURRSfI0J7Z6DV4vGkCGGxDGCZq9AHCg4hA2UFFx7NWWuc2JHbA2iITFW0Ea8dDz0g\"}""
        */

        //print "sdasda";
        dd($apiResponse_json);
        //return response()->json(['code'=>$httpCode,'status'=>'ok','ok'=>true,'error'=>false],200);
        //print_r($httpCode);
        //dd($apiResponse);
        //$jsonArrayResponse - json_decode($apiResponse);
        curl_close($ch); 
        

/*

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
