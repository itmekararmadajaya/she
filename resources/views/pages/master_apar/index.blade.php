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

            {{-- Area --}}
            <div class="col-md-3">
                <label for="gedung_id" class="form-label">Area</label>
                <select name="gedung_id" id="gedung_id" class="form-select">
                    <option value="">Semua Area</option>
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
                    <th>Area</th>
                    <th>Lokasi</th>
                    <th>Jenis Isi</th>
                    <th>Ukuran</th>
                    <th>Satuan</th>
                    <th>Tanggal Kadaluarsa</th>
                    <th>Jenis Pemadam</th>
                    <!-- <th>Tanda</th> -->
                    <th>Catatan</th>
                    <!-- <th>Tanggal Refill</th> -->
                    <!-- <th>Keterangan</th> -->
                    <th style="width: 10px;">Active</th>
                    <th style="width: 100px;" class="text-center">#</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($apars as $i => $apar)
                <tr class="{{ !$apar->is_active ? 'table-secondary' : '' }}">
                    <td>{{ $apars->firstItem() + $i }}</td>
                    <td>
                        <div class="text-center">
                            {{ $apar->kode }}
                        </div>
                    </td>
                    <td>{{ $apar->gedung->nama }}</td>
                    <td>{{ $apar->lokasi }}</td>
                    <td>{{ $apar->jenisIsi->jenis_isi }}</td>
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
                    <td>{{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->isoFormat('D MMMM YYYY') }}</td>
                    <td>
                        <div class="text-center">
                            {{ $apar->jenisPemadam->jenis_pemadam }}
                        </div>
                    </td>
                    <!-- <td>
                        <div class="text-center">
                            {{ $apar->tanda }}
                        </div>
                    </td> -->
                    <td>{{ $apar->catatan }}</td>
                    <!-- <td>{{ \Carbon\Carbon::parse($apar->tgl_refill)->isoFormat('D MMMM YYYY')  }}</td> -->
                    <!-- <td>{{ $apar->keterangan }}</td> -->
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
                        <a href="{{ route('apar.download.qr', $apar->id) }}" class="btn btn-sm btn-secondary">
                            <i class="ph ph-qr-code"></i>
                        </a>
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
@endsection

<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="qrCodeModalLabel">QR Code APAR</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body text-center">
<div id="qr-code-content">
<div class="spinner-border text-primary" role="status">
<span class="visually-hidden">Loading...</span>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
</div>
</div>
</div>
</div>

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
    // Logika untuk konfirmasi non-aktifkan data
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

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
                    form.submit();
                }
            });
        });
    });

    // Logika untuk menampilkan modal QR Code
    const qrCodeModal = document.getElementById('qrCodeModal');
    if (qrCodeModal) {
        qrCodeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const aparId = button.getAttribute('data-id');
            const modalBody = qrCodeModal.querySelector('#qr-code-content');

            // Tampilkan spinner saat memuat
            modalBody.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;

            // Lakukan request untuk mendapatkan konten QR Code dari server
            fetch(`/apar/qrcode/${aparId}`)
                .then(response => {
                    // Periksa jika respons berhasil
                    if (!response.ok) {
                        throw new Error('Gagal memuat QR Code');
                    }
                    return response.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    modalBody.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                });
        });
    }
</script>

@endpush