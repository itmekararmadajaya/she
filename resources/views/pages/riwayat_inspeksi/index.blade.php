@extends('layouts.app')

@section('title', 'Riwayat Inspeksi')

@section('content')
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white rounded-top-4 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3 fw-semibold">Riwayat Inspeksi</h1>
                <a href="{{ route('riwayat-inspeksi.export', request()->query()) }}" class="btn btn-success">
                    <i class="ph ph-file-excel me-2"></i>Excel
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Formulir Filter dan Pencarian -->
            <div class="mb-4">
                <form action="{{ route('riwayat-inspeksi') }}" method="GET">
                    <div class="row">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="date" class="form-control" name="start_date"
                                    value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="date" class="form-control" name="end_date"
                                    value="{{ request('end_date', now()->endOfMonth()->toDateString()) }}">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="q" placeholder="Cari..."
                                    value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            @if (request()->has(['start_date', 'end_date', 'q']))
                                <a href="{{ route('riwayat-inspeksi') }}" class="btn btn-warning ml-2">Hapus Filter</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabel Riwayat Inspeksi -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle rounded-3 overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1px;">No</th>
                            <th>Apar</th>
                            <th>Lokasi</th>
                            <th>Petugas</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <!-- <th>Bukti Inspeksi</th> -->
                            <th style="width: 50px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aparInspections as $i => $inspection)
                            <tr>
                                <td>{{ $aparInspections->firstItem() + $i }}</td>
                                <td>
                                    <span class="fw-semibold">{{$inspection->masterApar->kode}}</span>
                                </td>
                                <td>
                                    {{$inspection->gedung->nama}} - {{$inspection->lokasi}}
                                </td>
                                <td>
                                    {{$inspection->user->name}}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($inspection->date)->translatedFormat('d F Y') }}
                                </td>
                                <td>
                                    @if($inspection->status == 'GOOD')
                                        <span class="badge bg-success rounded-pill px-3 py-2">GOOD</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3 py-2">NOT GOOD</span>
                                    @endif
                                </td>
                                <!-- <td>
                                    @if($inspection->final_foto_path)
                                        <a href="{{ asset('storage/' . $inspection->final_foto_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $inspection->final_foto_path) }}" alt="Foto Terakhir" style="height: 50px; border-radius:4px;">
                                        </a>
                                    @else
                                        <span class="text-muted">Tidak ada foto</span>
                                    @endif
                                </td> -->
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white rounded-circle" data-bs-toggle="modal" data-bs-target="#modal-{{ $inspection->id }}">
                                        <i class="ph ph-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="ph ph-info-circle fs-4 me-2"></i>
                                    Tidak ada data yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginasi -->
            <div class="d-flex justify-content-end">
                {{ $aparInspections->withQueryString()->links() }}
            </div>
        </div>
    </div>

@foreach ($aparInspections as $inspection)
    <!-- Modal detail -->
    <div class="modal fade" id="modal-{{ $inspection->id }}" tabindex="-1" aria-labelledby="modalTitle-{{ $inspection->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header text-white rounded-top-4" style="background-color: #1f2937;">
                    <h5 class="modal-title fw-semibold" id="modalTitle-{{ $inspection->id }}">
                        <i class="ph ph-magnifying-glass me-2"></i>
                        Detail Inspeksi APAR: {{ $inspection->masterApar->kode }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Lokasi:</h6>
                            <div class="fw-semibold">{{ $inspection->masterApar->gedung->nama }} - {{ $inspection->masterApar->lokasi }}</div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Petugas:</h6>
                            <div class="fw-semibold">{{ $inspection->user->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Tanggal Inspeksi:</h6>
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($inspection->date)->translatedFormat('d F Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Bukti Inspeksi:</h6>
                            @if($inspection->final_foto_path)
                            <a href="{{ asset('storage/' . $inspection->final_foto_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $inspection->final_foto_path) }}" alt="Foto Terakhir" style="height: 100px; border-radius:4px;">
                            </a>
                            @else
                            <span class="text-muted">Tidak ada foto</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-3">Hasil Pemeriksaan:</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-bordered">
                            <thead class="table-light text-left">
                                <tr>
                                    <th style="width: 25%">Item Check</th>
                                    <th style="width: 10%">Nilai</th>
                                    <th style="width: 25%">Keterangan</th>
                                    <th style="width: 20%">Foto Inspeksi</th>
                                    <!-- <th style="width: 20%">Foto Perbaikan</th> -->
                                    <th style="width: 20%">Perbaikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inspection->details as $detail)
                                <tr>
                                    <td>{{ $detail->itemCheck->name }}</td>
                                    <td class="text-center">
                                        @if ($detail->value == 'B')
                                            <span class="badge bg-success rounded-pill">Baik</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill">Rusak</span>
                                        @endif
                                    </td>
                                    <td>{{ $detail->remark ?: '-' }}</td>
                                    <td>
                                        @forelse ($detail->photos as $photo)
                                            <a href="{{ asset('storage/' . $photo->foto_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $photo->foto_path) }}" alt="Foto Inspeksi" style="height: 50px; margin-right: 4px; border-radius:4px;">
                                            </a>
                                        @empty
                                            <span class="text-muted">Tidak ada foto</span>
                                        @endforelse
                                    </td>
                                    <!-- <td>
                                        @forelse ($detail->reparasiPhotos as $reparasiPhoto)
                                            <a href="{{ asset('storage/' . $reparasiPhoto->foto_path) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $reparasiPhoto->foto_path) }}" alt="Foto Perbaikan" style="height: 50px; margin-right: 4px; border-radius:4px;">
                                            </a>
                                        @empty
                                        <span class="text-muted">Tidak ada foto</span>
                                        @endforelse
                                    </td> -->
                                    <td style="width: 20%">
                                        @if ($detail->value != 'B')
                                            <button class="btn btn-sm btn-success btn-repair" data-detail-id="{{ $detail->id }}">
                                                <i class="ph ph-check me-1"></i> Perbaiki
                                            </button>
                                            <form id="form-repair-{{ $detail->id }}" action="{{ route('riwayat-inspeksi.repair', $detail->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                        @else
                                            <span class="text-muted">Sesuai</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

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
    document.addEventListener('DOMContentLoaded', function () {
        // Tambahkan event listener untuk semua tombol .btn-recycle
        document.querySelectorAll('.btn-recycle').forEach(function (button) {
            button.addEventListener('click', function () {
                const detailId = this.getAttribute('data-detail-id');

                Swal.fire({
                    title: 'Yakin ingin me-recycle item ini?',
                    text: "Tindakan ini akan menghapus remark dan mengubah nilai menjadi Baik",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1f2937',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Recycle!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-recycle-' + detailId).submit();
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Event listener baru untuk tombol 'Perbaiki'
        document.querySelectorAll('.btn-repair').forEach(function (button) {
            button.addEventListener('click', function () {
                const detailId = this.getAttribute('data-detail-id');
                Swal.fire({
                    title: 'Kamu yakin untuk mengubah status item check menjadi "Baik"?',
                    text: "Tindakan ini akan menandai item sebagai sudah diperbaiki.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Iya, Perbaiki',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim form untuk memperbarui status
                        document.getElementById('form-repair-' + detailId).submit();
                    }
                });
            });
        });
    });
</script>
@endpush
