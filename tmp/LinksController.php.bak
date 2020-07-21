<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Application\BulkDestroyApplication;
use App\Http\Requests\Admin\Application\DestroyApplication;
use App\Http\Requests\Admin\Application\IndexApplication;
use App\Http\Requests\Admin\Application\StoreApplication;
use App\Http\Requests\Admin\Application\UpdateApplication;
use App\Models\Application;
use Brackets\AdminListing\Facades\AdminListing;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class LinksController extends Controller
{
    public function meta(Request $request)
    {
        $link_meta = [
            'title'=>null,
            'description'=>null,
            'icon'=>null,
            'extra_icons'=>[]
        ];
        
        if(!$request->has('url')){
            return response()->json(['error'=>1,'message'=>''], 422); 
        }
        try{
            $parse_url = parse_url($request->url);
            if(!isset($parse_url['scheme']) || !isset($parse_url['host'])){
                return response()->json(['error'=>2,'message'=>''], 422); 
            }

            $url = $parse_url['scheme'] . '://' . $parse_url['host'] ;
            if(isset($parse_url['port'])){
                $url = $url . ':' . $parse_url['port'];
            }

            $contents = file_get_contents($url);
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument();
            $doc->strictErrorChecking = FALSE;
            $doc->loadHTML(mb_convert_encoding($contents, 'HTML-ENTITIES', 'UTF-8'));
            //$doc->loadHTML($contents);
            libxml_use_internal_errors(false);
            $xml = simplexml_import_dom($doc);


            if(isset($xml->head->title)){
                $link_meta['title'] = trim(explode('-', $xml->head->title)[0]);
            }

            if(isset($xml->head->meta)){
                $metas = $xml->head->meta;
                foreach($metas as $meta){
                    $attributes = $meta->attributes();
                    if( isset($attributes['name']) && isset($attributes['content']) && $attributes['name'] == 'description'){
                        $link_meta['description'] =  (string)  $attributes['content'];
                    }
                }
            }

            if(isset($xml->head->link)){
                $links = $xml->head->link;
                foreach($links as $link){
                    $attributes = $link->attributes();
                    //Array ( [href] => //cdn.cnn.com/cnn/.e/img/3.0/global/misc/apple-touch-icon.png [rel] => apple-touch-icon [type] => image/png ) ) 
                    //print "rel = " . $attributes['rel'] . " href = " . $attributes['href'] . "<br>\n";
                    if( isset($attributes['rel']) && isset($attributes['href']) && $attributes['rel'] == 'apple-touch-icon'){
                        $link_meta['extra_icons'][] = (string)  $attributes['href'][0];
                        //Array ( [rel] => icon [href] => https://s.ytimg.com/yts/img/favicon_96-vflW9Ec0w.png [sizes] => 96x96 ) )
                    }elseif( isset($attributes['rel']) && isset($attributes['href']) && $attributes['rel'] == 'icon' && substr($attributes['href'],-4)=='.png'){
                        $link_meta['extra_icons'][] = (string)  $attributes['href'][0];
                    //}elseif( isset($attributes['rel']) && isset($attributes['href']) && $attributes['rel'] == 'shortcut icon' && substr($attributes['href'],-4)=='.png'){
                    //    $meta['icon'] = $attributes['href'];
                    }
                }
                if($link_meta['extra_icons']){
                    foreach($link_meta['extra_icons'] as $index => $value){
                        if(substr($value,0,2)=='//'){
                            //$meta['icon'] = $meta['icon'];
                        }elseif(substr($value,0,1)=='/'){
                            $link_meta['extra_icons'][$index] = $url . $value;
                        }
                    }
                    $link_meta['icon'] = $link_meta['extra_icons'][0];
                }
            }
            return response()->json($link_meta, 200);
        } catch (Exception $e) {
            return response()->json(['error'=>2,'message'=> $e->getMessage()], 422);
        }

    }

}
