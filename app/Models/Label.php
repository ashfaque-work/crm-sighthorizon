<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = [
        'name',
        'color',
        'category',
        'pipeline_id',
        'created_by',
    ];

    public static $colors = [
        'primary',
        'secondary',
        'danger',
        'warning',
        'info',
    ];
    
    public static $category = [
        'Lead Source',
        'Call Status',
        'Kind of Lead',
    ];
}
