<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Tab;
use App\Models\Application;
use DB;
use App\Models\Setting;

use Exception;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class CategoriesController extends Controller
{
    public function index($category_id=null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        try {
            $user = Auth::user();
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
        
        $applications = Application::select(['id','name','url','icon','isNewPage','isNewPageForIframe','category_id'])
        ->where('activated',1)
        ->orderBy('isFeatured', 'desc')
        ->orderBy('updated_at', 'desc');

        if($category_id){
            $applications = $applications->where('category_id',$category_id);
        }

        $applications = $applications->get();

        $applications_user = DB::table('application_user')
            ->where('user_id',$user->id)
            ->get();

        $pinned_applications = [];
        foreach($applications_user as $application_user){
            $pinned_applications[$application_user->application_id] = 1;
        }
        $settings = Setting::getSettings();

        return view('category.index',['categories'=>$categories,'tabs'=>$tabs,'applications'=>$applications,'pinned_applications'=>$pinned_applications,'settings'=>$settings,'user'=>$user]);
    }
}
