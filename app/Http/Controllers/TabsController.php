<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Tab;
use App\Models\Application;
use App\Models\Setting;

use Exception;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class TabsController extends Controller
{
    public function page($tab_id=null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        try {
            $user = Auth::user();
        } catch (Exception $e) {
            return redirect()->route('logout');
        }

        $tabs = Tab::select(['id','name','tabtype_id','category_id','url'])
        ->where('activated',1)
        ->orderBy('itemorder', 'asc')
        ->with(['tabtype'=>function($query){
            $query->select('id','action','url_iframe');
        }])
        ->get();

        $tab = Tab::select(['id','body','name'])
                ->where('activated',1)
                ->where('id',$tab_id)
                ->first();

        if( !isset($tab->body) || $tab->body =='') exit;


        $settings = Setting::getSettings();

        return view('tab.page',['body'=>$tab->body,'name'=>$tab->name,'settings'=>$settings,'user'=>$user,'tabs'=>$tabs]);
    }

    public function iframe($tab_id=null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        try {
            $user = Auth::user();
        } catch (Exception $e) {
            return redirect()->route('logout');
        }

        $tabs = Tab::select(['id','name','tabtype_id','category_id','url'])
        ->where('activated',1)
        ->orderBy('itemorder', 'asc')
        ->with(['tabtype'=>function($query){
            $query->select('id','action','url_iframe');
        }])
        ->get();

        $tab = Tab::select(['id','url','name'])
                ->where('activated',1)
                ->where('id',$tab_id)
                ->first();

        if( !isset($tab->url) || $tab->url =='') exit;


        $settings = Setting::getSettings();

        return view('tab.iframe',['url'=>$tab->url,'name'=>$tab->name,'settings'=>$settings,'user'=>$user,'tabs'=>$tabs]);
    }
}
