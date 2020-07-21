<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{

    protected $table = 'locales';

    protected $fillable = [
        'name',
        'locale',
    
    ];
    
    
    protected $dates = [
    
    ];
    public $timestamps = false;
    
    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function policies()
    {
        return $this->belongsToMany('App\Models\Policy');
    }

    public function getResourceUrlAttribute()
    {
        return url('/admin/locales/'.$this->getKey());
    }
}
