@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Edit Kebutuhan</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('kebutuhan.update', $kebutuhan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="kebutuhan" class="form-label">Nama Kebutuhan</label>
                    <input type="text" class="form-control" id="kebutuhan" name="kebutuhan" value="{{ $kebutuhan->kebutuhan }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('kebutuhan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
