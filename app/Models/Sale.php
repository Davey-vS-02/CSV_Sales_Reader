<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    //Connected to sales table in db.

    protected $guarded = [];

    protected $casts = [
        "date" => "date",
        "delivery_date" => "date"
    ];
}
