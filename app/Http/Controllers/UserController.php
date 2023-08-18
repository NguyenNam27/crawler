<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function listUser(){
//        $this->authorize('viewAny');
        $userList = DB::table('users')
            ->orderBy('id','desc')
            ->paginate(10);
        return view('user.list',[
            'userList'=>$userList
        ]);
    }
}
