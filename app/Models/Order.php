<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";
    protected $fillable = ['order_id', 'user_id','name','address','phone','email','note','total','created_at','updated_at'];
    public $timestamps = true;
    use HasFactory;
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
