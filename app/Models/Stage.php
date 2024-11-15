<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = [
        'name','pipeline_id','created_by','order'
    ];

    public function deal()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
