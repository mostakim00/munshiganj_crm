<?php

namespace App\Models\LandBroker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandBrokerPaymentInformation extends Model
{
    use HasFactory;
    protected $guarded=[];

    public static $BROKER_MONEY = 1;
    public static $EXTRA_MONEY = 2;
    public static $BROKER_CONVEYANCE = 3;
    public static $MOBILE_BILL = 4;
    public static $ENTERTAINMENT = 5;

    public function brokerPaymentStatement(){
        return $this ->hasMany(\App\Models\LandBroker\LandBrokerPaymentStatement::class, 'land_broker_payment_information_id', 'id');
    }

}
