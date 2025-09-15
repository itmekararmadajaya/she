@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Tambah Alamat Email Baru</h1>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Email</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('email.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="email">Alamat Email:</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('email.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection
