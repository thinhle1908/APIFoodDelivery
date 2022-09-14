<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";
    protected $fillable = ['id', 'product_name','price','image','qty','description','visible','created_at','updated_at'];
    public $timestamps = true;
    use HasFactory;
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
