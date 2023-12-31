<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'groups';
    protected $fillable = ['name', 'permissions', 'user_id'];

    public function users(){
        return $this->hasMany(User::class,'user_id','id');
    }
}
