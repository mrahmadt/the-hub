<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tab extends Model
{
    
    public function category()
    {
        return $this->belongsTo(Category::class); //'App\Models\Policy');
    }
    public function tabtype()
    {
        return $this->belongsTo(Tabtype::class); //'App\Models\Policy');
    }
}
