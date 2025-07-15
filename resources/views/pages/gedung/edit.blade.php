@extends('layouts.app')

@section('title', 'Edit Gedung')

@section('content')
<div class="card col-md-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Edit Gedung</h1>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="POST" action="{{route('gedung.update', $gedung)}}">
                @csrf
                @method('PUT')
                <div class="row">
                    {{-- Nama --}}
                    <div class="form-group mb-3">
                        <label for="nama">Nama</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                            id="nama" value="{{ $gedung->nama }}" placeholder="Nama gedung">
                        @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{route('gedung.index')}}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection