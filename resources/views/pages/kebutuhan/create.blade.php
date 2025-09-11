@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Tambah Kebutuhan Baru</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('kebutuhan.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="kebutuhan" class="form-label">Nama Kebutuhan</label>
                    <input type="text" name="kebutuhan" id="kebutuhan" class="form-control @error('kebutuhan') is-invalid @enderror" value="{{ old('kebutuhan') }}" required>
                    @error('kebutuhan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('kebutuhan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
