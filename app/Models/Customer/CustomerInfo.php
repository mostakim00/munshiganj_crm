<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInfo extends Model
{
    use HasFactory;
    protected $guarded=[];


    public function customerLandRelation()
    {
        return $this->hasOne(CustomerInfo::class,'customer_id','id');
    }
}
