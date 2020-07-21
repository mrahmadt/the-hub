<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Tab;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class PagesController extends Controller
{
    public function view($page_id)
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


        $page = Page::select(['id','title','body'])
        ->where('id',$page_id)
        ->where('is_published',1)
        ->first();

        if(!isset($page->body)) exit;


        $settings = Setting::getSettings();

        return view('page.view',['page'=>$page,'settings'=>$settings,'user'=>$user,'tabs'=>$tabs]);
    }
}
