<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

    public function users()
    {
        return $this->belongsToMany('App\User');
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class); //'App\Models\Policy');
    }
}
