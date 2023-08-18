@extends('layouts.app')

@section('content')

        <div class="signup-form">
            <div class="title">Register</div>
            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="input-boxes">
                    <div class="input-box">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" placeholder="Enter your name" required>
                        @if ($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
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
                    <div class="button input-box">
                        <input type="submit" value="Register">
                    </div>
                    <div class="text sign-up-text">Already have an account? <a href="{{route('login')}}">Login now</a>
                    </div>
                </div>
            </form>
        </div>

@endsection
