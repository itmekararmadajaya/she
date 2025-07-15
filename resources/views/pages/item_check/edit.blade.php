@extends('layouts.app')

@section('title', 'Edit Item Check')

@section('content')
<div class="card col-md-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Edit Item Check</h1>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="POST" action="{{route('item-check.update', $itemCheck)}}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="form-group mb-3">
                        <label for="nama">Nama</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            id="name" value="{{ $itemCheck->name }}" placeholder="Nama item check">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{route('item-check.index')}}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection