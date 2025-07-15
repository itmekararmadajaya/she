@extends('layouts.app')

@section('title', 'Master APAR')

@section('content')
<div class="card w-100">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Master APAR</h1>
            <a href="{{ route('master-apar.create') }}" class="btn btn-success">Tambah</a>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="GET" action="{{ route('master-apar.index') }}" class="row gx-2 gy-1 align-items-end mb-3">
                {{-- Kode --}}
                <div class="col-md-3">
                    <label for="kode" class="form-label">Kode</label>
                    <input type="text" name="kode" id="kode" value="{{ request('kode') }}" class="form-control" placeholder="Cari Kode">
                </div>

                {{-- Gedung --}}
                <div class="col-md-3">
                    <label for="gedung_id" class="form-label">Gedung</label>
                    <select name="gedung_id" id="gedung_id" class="form-select">
                        <option value="">Semua Gedung</option>
                        @foreach ($gedungs as $gedung)
                            <option value="{{ $gedung->id }}" {{ request('gedung_id') == $gedung->id ? 'selected' : '' }}>
                                {{ $gedung->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi --}}
                <div class="col-md-3">
                    <label for="lokasi" class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi" value="{{ request('lokasi') }}" class="form-control" placeholder="Cari Lokasi">
                </div>

                {{-- Tombol --}}
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
        <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 1px;;">No</th>
                        <th>Kode</th>
                        <th>Gedung</th>
                        <th>Lokasi</th>
                        <th>Jenis Isi</th>
                        <th>Ukuran</th>
                        <th>Satuan</th>
                        <th>Tgl Kadaluarsa</th>
                        <th>Jenis Pemadam</th>
                        <th>Tanda</th>
                        <th>Catatan</th>
                        <th>Tgl Refill</th>
                        <th>Keterangan</th>
                        <th style="width: 10px;">Active</th>
                        <th style="width: 100px;" class="text-center">#</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($apars as $i => $apar)
                    <tr>
                        <td>{{ $apars->firstItem() + $i }}</td>
                        <td>
                            <div class="text-center">
                                {{ $apar->kode }}
                            </div>
                        </td>
                        <td>{{ $apar->gedung->nama }}</td>
                        <td>{{ $apar->lokasi }}</td>
                        <td>{{ $apar->jenis_isi }}</td>
                        <td>
                            <div class="text-end">
                                {{ $apar->ukuran }}
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                {{ $apar->satuan }}
                            </div>
                        </td>
                        <td>{{ $apar->tgl_kadaluarsa_formatted }}</td>
                        <td>
                            <div class="text-center">
                                {{ $apar->jenis_pemadam }}
                            </div>
                        </td>
                        <td>
                            <div class="text-center">
                                {{ $apar->tanda }}
                            </div>
                        </td>
                        <td>{{ $apar->catatan }}</td>
                        <td>{{ $apar->tgl_refill_formatted }}</td>
                        <td>{{ $apar->keterangan }}</td>
                        <td>
                            <div class="text-center">
                                @if ($apar->is_active)
                                    <i class="ph ph-check text-success"></i>
                                @else
                                    <i class="text-danger">x</i>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('master-apar.generate-qr', $apar) }}" class="btn btn-sm btn-secondary"><i class="ph ph-qr-code"></i></a>
                            <a href="{{ route('master-apar.edit', $apar->id) }}" class="btn btn-sm btn-warning"><i class="ph ph-pencil"></i></a>
                            @if ($apar->is_active)
                                <form action="{{ route('master-apar.destroy', $apar->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger btn-delete"><i class="ph ph-trash"></i></button>
                                </form>
                            @else
                                <form action="{{ route('master-apar.restore', $apar->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    <button class="btn btn-sm btn-secondary btn-recycle"><i class="ph ph-recycle"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end">
            {{ $apars->withQueryString()->links() }}
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