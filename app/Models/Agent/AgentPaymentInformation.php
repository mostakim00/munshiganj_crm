<?php

namespace App\Models\Agent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPaymentInformation extends Model
{
    use HasFactory;
    protected $guarded=[];

    public static $AGENT_MONEY = 1;
    public static $EXTRA_MONEY = 2;
    public static $AGENT_CONVEYANCE = 3;
    public static $MOBILE_BILL = 4;
    public static $ENTERTAINMENT = 5;


    public function agentPaymentStatement(){
        return $this ->hasMany(\App\Models\Agent\AgentPaymentStatement::class, 'agent_payment_information_id', 'id');
    }
    

}
