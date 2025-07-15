@extends('layouts.app')

@section('title', 'Item Check')

@section('content')
<div class="card w-50">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Item Check</h1>
            <a href="{{ route('item-check.create') }}" class="btn btn-success">Tambah</a>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="GET" action="{{ route('item-check.index') }}" class="d-flex gap-2 align-items-center mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari item check">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 1px;;">No</th>
                        <th>Nama</th>
                        <th style="width: 10px;">Active</th>
                        <th style="width: 100px;" class="text-center">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $i => $item)
                    <tr>
                        <td>{{ $items->firstItem() + $i }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            <div class="text-center">
                                @if ($item->is_active)
                                    <i class="ph ph-check text-success"></i>
                                @else
                                    <i class="text-danger">x</i>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('item-check.edit', $item->id) }}" class="btn btn-sm btn-warning"><i class="ph ph-pencil"></i></a>
                            @if ($item->is_active)
                                <form action="{{ route('item-check.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-delete"><i class="ph ph-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('item-check.restore', $item->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    <button class="btn btn-sm btn-secondary btn-recycle"><i class="ph ph-recycle"></i></button>
                                </form>
                            @endif
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
            {{ $items->withQueryString()->links() }}
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
                    title: 'Yakin ingin non aktifkan data?',
                    text: "Data dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, non aktifkan!',
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