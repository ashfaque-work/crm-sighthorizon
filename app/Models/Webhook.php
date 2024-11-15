<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'module',
        'url',
        'method',
        'created_by',
    ];

    public static function module()
    {
        $webmodule = [
            'Lead create' => 'Lead create',
            'deal create' => 'deal create',
            'Estimate create' => 'Estimate create',
            'Convert lead to deal' => 'Convert lead to deal',
            'Contract create' => 'Contract create',
            'Payment create' => 'Payment create',
            'Invoice create' => 'Invoice create',
            'Invoice status updated' => 'Invoice status updated',
        ];
        return $webmodule;
    }

    public static function method()
    {
        $method = [
            'POST' => 'POST',
            'GET'  => 'GET',
        ];
        return $method;
    }
}
