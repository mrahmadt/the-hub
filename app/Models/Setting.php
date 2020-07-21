<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'name', 'value',
    ];

    protected $guarded = ['*'];

    static function getSettings(){
        $settingsDB = Setting::select(['key','value','updated_at'])->get();
        $settings = [];
        foreach($settingsDB as $setting){
            $settings[$setting->key] = [
                'value'=>$setting->value,
                'updated_at'=>$setting->updated_at,
                'uid'=> md5($setting->updated_at),
            ];
            
            /*if (in_array($setting->key, ['users.announcement','ui.banner.top'])) {
                $settings[$setting->key]['value'] = str_replace($settings[$setting->key]['value'],'<img src=','<img class="img-fluid" src=');
            }*/
        }
        return $settings;
    }
}
