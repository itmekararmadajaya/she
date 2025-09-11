@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="card card-custom-rounded">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Users</h1>
            <a href="{{ route('users.create') }}" class="btn btn-success">Tambah</a>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="GET" action="{{ route('users.index') }}" class="d-flex gap-2 align-items-center mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari nama atau email...">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 1px;;">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 100px;" class="text-center">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $i => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->getRoleNames()->first() ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning"><i class="ph ph-pencil"></i></a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger btn-delete"><i class="ph ph-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-right'
            });
        </script>
    @endif

    <script>
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Cegah form langsung submit

                const form = this.closest('form');

                Swal.fire({
                    title: 'Yakin ingin hapus?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit form jika dikonfirmasi
                    }
                });
            });
        });
    </script>
@endpush