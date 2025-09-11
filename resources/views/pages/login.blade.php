@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="auth-main">
    <div class="position-absolute top-0 start-50 translate-middle" style="z-index: 100; margin-top: 180px;">
            <div class="bg-success rounded-circle d-flex justify-content-center align-items-center" style="width: 150px; height: 150px; background-color: white !important;">
                <a href="#">
                    <img src="{{asset('asset/logo/k3.png')}}" class="w-100" alt="logo k3" />
                </a>
            </div>
        </div>
    <div class="card rounded-4 position-relative" style="width: 30rem; background-color: white">
        <div class="card-body" style="padding-top: 70px;">
            <div class="text-center">
                <a href="#">
                    <img src="{{asset('asset/logo/logona2.png')}}" class="w-75" alt="logo new armada" />
                </a>
                <a href="#">
                    <img src="{{ asset('asset/logo/she.png') }}" style="width: 100px; object-fit: contain;" alt="logo she" />
                </a>
            </div>
            
            <div class="error-container mb-3" style="min-height: 40px;">
                <form action="{{ route('weblogin') }}" method="post">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="nikInput" class="form-label" style="color: black"><strong>NIK</strong></label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background-color: #d2d4d6ff;">
                                <i class="fa fa-id-card" style="color: #374151"></i>
                            </span>
                            <input type="text" name="nik" class="form-control border-0" style="background-color: #d2d4d6ff; color: black;" id="nikInput">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="floatingInput" class="form-label" style="color: black"><strong>Sandi</strong></label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background-color: #d2d4d6ff;">
                                <i class="fa fa-lock" style="color: #374151"></i>
                            </span>
                            <input type="password" name="password" class="form-control border-0" style="background-color: #d2d4d6ff; color: black;" id="passwordInput"/>
                            <span class="input-group-text border-0" style="background-color: #d2d4d6ff;" id="togglePassword">
                                <button class="btn p-0" type="button">
                                    <i class="fa fa-eye" style="color: #374151;"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn w-100 py-2" style="color: #fff; background-color: #2563eb;">Login</button>
                    </div>
                </form>
                @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
                    {{ $errors->first() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function (e) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
</script>
@endpush

<style>
    .auth-main {
        background: #d2d4d6ff;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .form-label {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .auth-wrapper.v1 {
        width: 100%;
        max-width: 400px;
    }

    .auth-form {
        background: #1f2937;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }

    .card-body {
        padding: 20px;
    }

    .text-center img {
        margin-bottom: 20px;
    }

    h4 {
        font-weight: 500;
        color: #333;
    }

    .text-center .btn {
        width: 100%;
        margin-top: 10px;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .mt-4 {
        margin-top: 1.5rem !important;
    }
</style>