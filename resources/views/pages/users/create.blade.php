@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="card col-md-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Tambah User</h1>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="POST" action="{{route('users.store')}}">
                @csrf
                <div class="row">
                    {{-- Nama --}}
                    <div class="form-group mb-3">
                        <label for="name">Nama</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            id="name" value="{{ old('name') }}" placeholder="Nama lengkap">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIK --}}
                    <div class="form-group mb-3">
                        <label for="nik">NIK</label>
                        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                            id="nik" value="{{ old('nik') }}" placeholder="Masukkan NIK 10 digit">
                        @error('nik')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    {{-- Checkbox untuk Toggle Email --}}
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="toggleEmail" checked>
                        <label class="form-check-label" for="toggleEmail">Email Kustom</label>
                    </div>

                    <div class="form-group mb-3" id="email-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" value="{{ old('email') }}" placeholder="Email aktif">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="Isi password">
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="fa fa-eye"></i>
                            </button>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="form-group mb-3">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                placeholder="Ulangi password">
                            <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirmation">
                                <i class="fa fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Role --}}
                    <div class="form-group mb-3">
                        <label for="role">Role</label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror">
                            @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{route('users.index')}}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle Password
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye-slash');
        this.querySelector('i').classList.toggle('fa-eye');
    });

    // Toggle Password Confirmation
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordConfirmation = document.getElementById('password_confirmation');

    togglePasswordConfirmation.addEventListener('click', function () {
        const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmation.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye-slash');
        this.querySelector('i').classList.toggle('fa-eye');
    });

    // Logika Toggle Email
    const toggleEmailCheckbox = document.getElementById('toggleEmail');
    const emailGroup = document.getElementById('email-group');
    const emailInput = document.getElementById('email');

    // Cek status saat halaman dimuat (jika ada error validasi)
    if (!emailInput.value && !{{ old('email') ? 'true' : 'false' }}) {
        // Jika tidak ada 'old' email, sembunyikan secara default
        emailGroup.style.display = 'none';
        toggleEmailCheckbox.checked = false;
    }

    toggleEmailCheckbox.addEventListener('change', function () {
        if (this.checked) {
            // Tampilkan field Email
            emailGroup.style.display = 'block';
        } else {
            // Sembunyikan field Email dan kosongkan nilainya
            emailGroup.style.display = 'none';
            emailInput.value = '';
        }
    });
</script>
@endpush
@endsection