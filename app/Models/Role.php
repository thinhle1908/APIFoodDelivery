<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = "roles";
    public $primaryKey = 'id';
    protected $fillable = ['id','role_name','created_at','updated_at'];   

    public function users()
    {
        return $this->belongsTo(User::class,'id','role');
    }
}
