@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="auth-main">
      <div class="auth-wrapper v1">
        <div class="auth-form">
          <div class="position-relative my-5">
                <div class="card mb-0">
                <div class="card-body">
                    <div class="text-center">
                    <a href="#"><img src="{{asset('asset/logo/logona2.png')}}" class="w-75" alt="img" /></a>
                    </div>
                    <h4 class="text-center f-w-500 mt-4 mb-3">Login</h4>
                    <div class="form-group mb-3">
                    <input type="email" class="form-control" id="floatingInput" placeholder="Email Address" />
                    </div>
                    <div class="form-group mb-3">
                    <input type="password" class="form-control" id="floatingInput1" placeholder="Password" />
                    </div>
                    <div class="text-center mt-4">
                    <button type="button" class="btn btn-primary shadow px-sm-4">Login</button>
                    </div>
                </div>
                </div>
          </div>
        </div>
      </div>
    </div>
@endsection