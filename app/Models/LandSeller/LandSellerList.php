<?php

namespace App\Models\LandSeller;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandSellerList extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $hidden = [
        'laravel_through_key'
    ];
}
