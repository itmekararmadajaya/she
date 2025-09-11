@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Daftar Kebutuhan</h1>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        
            <div class="mb-3">
                <a href="{{ route('kebutuhan.create') }}" class="btn btn-primary">Tambah Kebutuhan Baru</a>
            </div>
        
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Kebutuhan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kebutuhans as $kebutuhan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $kebutuhan->kebutuhan }}</td>
                                <td>
                                    <a href="{{ route('kebutuhan.edit', $kebutuhan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('kebutuhan.destroy', $kebutuhan->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kebutuhan ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data kebutuhan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
