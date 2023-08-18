<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Session;

class AuthController extends Controller
{

    public function getLogin()
    {
        if (Auth::check()) {
            return redirect('resultProduct');
        } else {
            return view('auth.login');
        }

    }
    public function postLogin(LoginRequest $request){
        $login = [
            'email' => $request->email,
            'password' => $request->password,
            'level' => 1,
            'status' => 1
        ];
        if (Auth::attempt($login)) {

            return redirect('resultProduct');

        } else {
            return redirect()->back()->with('status', 'Email hoặc Password không chính xác');
        }
    }
    public function register(){
        return view('auth.register');
    }
    public function postRegister(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);
        $data = $request->all();
        $this->create($data);
        return redirect('/');

    }
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }
    public function getLogout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
