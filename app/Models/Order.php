<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";
    protected $primaryKey = ['order_id', 'user_id'];
    protected $fillable = ['order_id','user_id','customer_stripe_id','name','address','phone','email','note','total','order_status','created_at','updated_at'];
    public $timestamps = true;
    public $incrementing = false;
    use HasFactory;
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
