<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'emails';
    protected $fillable = ['name','email','status'];

}
