<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Tab;
use App\Models\Application;
use App\User;
use App\Models\Setting;


use Exception;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class ApplicationsController extends Controller
{

    public function appinfo(Request $request,$application_id){
        $application = Application::select(['id','name','icon','description'])
                ->where('activated',1)
                ->where('id',$application_id)
                ->first();

        return response()->json(['status' => '1', 'application' => $application]);
    }

    public function pin($application_id){ //create or ignore 
        if (!Auth::check()) {
            return response()->json(['status' => '0']);
        }
        try {
            $user = Auth::user();
            /*if(env('APP_DEBUG') == false){
                $userSocialite = Socialite::driver($user->provider)->userFromToken($user->access_token);
            }else{
                 //$user = User::find(1);
                $userSocialite = [];
            }*/
            $user->applications()->attach($application_id);
        } catch (Exception $e) {
            return response()->json(['status' => '0']);
        }
        return response()->json(['status' => '1']);
    }

    public function unpin($application_id){
        if (!Auth::check()) {
            return response()->json(['status' => '0']);
        }
        try {
            $user = Auth::user();
            /*if(env('APP_DEBUG') == false){
                $userSocialite = Socialite::driver($user->provider)->userFromToken($user->access_token);
            }else{
                 //$user = User::find(1);
                $userSocialite = [];
            }*/
            $user->applications()->detach($application_id);
        } catch (Exception $e) {
            return response()->json(['status' => '0']);
        }
        return response()->json(['status' => '1']);
    }

    public function teamsUI(Request $request){

        print_r($_GET);
        print "<hr>";
        print_r($_POST);
        dd($request);
    }
    public function myapps()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        try {
            $user = Auth::user();
            if(env('APP_DEBUG') == false){
                $userSocialite = Socialite::driver($user->provider)->userFromToken($user->access_token);
            }else{
                 //$user = User::find(1);
                $userSocialite = [];
            }
        } catch (Exception $e) {
            return redirect()->route('logout');
        }
        $categories = Category::select(['id','name'])->where('activated',1)->orderBy('itemorder', 'asc')->get();


        $tabs = Tab::select(['id','name','tabtype_id','category_id','url'])
                ->where('activated',1)
                ->orderBy('itemorder', 'asc')
                ->with(['tabtype'=>function($query){
                    $query->select('id','action','url_iframe');
                }])
                ->get();
        
       

        $applications = $user->applications;
        
        if(!count($applications)){
            $applications = Application::select(['id','name','url','icon','isNewPage','isNewPageForIframe','category_id'])
            ->where('activated',1)
            ->orderBy('isFeatured', 'desc')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        }
        $settings = Setting::getSettings();

        return view('application.myapps',['categories'=>$categories,'tabs'=>$tabs,'applications'=>$applications,'settings'=>$settings,'user'=>$user]);
    }
}
