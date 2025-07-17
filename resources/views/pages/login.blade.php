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
                      @if ($errors->any())
                          <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                              {{ $errors->first() }}
                          </div>
                      @endif
                      <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                          <input type="email" name="email" class="form-control" id="floatingInput" placeholder="Email Address" />
                        </div>
                        <div class="form-group mb-3">
                          <input type="password" name="password" class="form-control" id="floatingInput1" placeholder="Password" />
                        </div>
                        <div class="text-center">
                          <button type="submit" class="w-100 btn btn-primary shadow px-sm-4">Login</button>
                        </div>
                      </form>

                      <div class="mt-4">
                        <div class="text-center mb-1">
                          Quick Menu
                        </div>
                        <div class="d-flex gap-2 overflow-auto px-1">
                          <a href="{{route('apar.user.laporan.yearly')}}" class="text-decoration-none">
                            <div class="d-flex flex-column align-items-center text-center rounded hover-shadow transition">
                              <div class="rounded-circle d-flex align-items-center justify-content-center mb-2"
                                  style="width: 56px; height: 56px; background-color: #4CAF50;">
                                <i class="ph ph-clipboard-text text-white fs-4"></i>
                              </div>
                              <span class="fw-semibold text-dark small">Laporan APAR</span>
                            </div>
                          </a>
                        </div>
                      </div>
                  </div>
                </div>
          </div>
        </div>
      </div>
    </div>
@endsection