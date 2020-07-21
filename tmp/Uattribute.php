<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uattribute extends Model
{
    protected $fillable = [
        'name',
        'attributes',
    
    ];
    
    
    protected $dates = [
    
    ];
    public $timestamps = false;
    
    protected $appends = ['resource_url'];

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/uattributes/'.$this->getKey());
    }
}
