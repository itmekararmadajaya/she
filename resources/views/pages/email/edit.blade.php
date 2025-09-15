@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Edit Alamat Email</h1>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Email</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('email.update', $email->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-3">
                        <label for="email">Alamat Email:</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $email->email }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('email.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection
