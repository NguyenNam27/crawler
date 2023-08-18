@extends('layouts.app')

@section('content')

    <div class="login-form">
        <div class="title">Login</div>
        @if (count($errors) >0)
        <ul>
            @foreach($errors->all() as $error)
                <li class="text-danger"> {{ $error }}</li>
            @endforeach
        </ul>
        @endif

        @if (session('status'))
            <ul>
                <li class="text-danger"> {{ session('status') }}</li>
            </ul>
        @endif
        <form action="{{route('login.post')}}" method="POST">
            @csrf
            <div class="input-boxes">
                <div class="input-box">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" placeholder="Enter your email" required>
                    @if ($errors->has('email'))
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                    @endif
                </div>
                <div class="input-box">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                    @if ($errors->has('password'))
                        <span class="text-danger">{{ $errors->first('password') }}</span>
                    @endif
                </div>
                <div class="text">
                    <a href="#">Forgot password?</a>
                </div>
                <div class="text">
                    <input type="checkbox">     Remember password
                </div>
                <div class="button input-box">
                    <input type="submit" value="Login">
                </div>

                <div class="text sign-up-text">Don't have an account? <a href="{{route('register')}}">Sigup now</a>
                </div>

            </div>
        </form>

    </div>

@endsection
