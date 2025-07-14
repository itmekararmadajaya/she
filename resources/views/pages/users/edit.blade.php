@extends('layouts.app')

@section('title', 'User Edit')

@section('content')
<div class="card col-md-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">User Edit</h1>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="POST" action="{{route('users.update', $user)}}">
                @csrf
                @method('PUT')
                <div class="row">
                    {{-- Nama --}}
                    <div class="form-group mb-3">
                        <label for="name">Nama</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            id="name" value="{{ $user->name }}" placeholder="Nama lengkap">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" value="{{ $user->email }}" placeholder="Email aktif">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" placeholder="Password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div class="form-group mb-3">
                        <label for="role">Role</label>
                        <select name="role" class="form-control @error('role') is-invalid @enderror">
                            @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ $user->getRoleNames()[0] == $role ? 'selected' : '' }}>
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
@endsection