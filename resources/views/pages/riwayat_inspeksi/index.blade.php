@extends('layouts.app')

@section('title', 'Riwayat Inspeksi')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3">Riwayat Inspeksi</h1>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1px;;">No</th>
                            <th>Apar</th>
                            <th>Lokasi</th>
                            <th>User</th>
                            <th>Tgl</th>
                            <th style="width: 50px;" class="text-center">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aparInspections as $i => $inspection)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>
                                    {{$inspection->masterApar->kode}}
                                </td>
                                <td>
                                    {{$inspection->masterApar->gedung->nama}} - {{$inspection->masterApar->lokasi}}
                                </td>
                                <td>
                                    {{$inspection->user->name}}
                                </td>
                                <td>
                                    {{$inspection->dateFormatted}}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-{{ $inspection->id }}">
                                        <i class="ph ph-eye"></i>
                                    </button>
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
                {{ $aparInspections->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal detail -->
    <div class="modal fade" id="modal-{{ $inspection->id }}" tabindex="-1" aria-labelledby="modalTitle-{{ $inspection->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header text-white rounded-top-4">
                <h5 class="modal-title fw-semibold" id="modalTitle-{{ $inspection->id }}">
                    <i class="ph ph-magnifying-glass me-2"></i>
                    Detail Inspeksi APAR: {{ $inspection->masterApar->kode }}
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h6 class="text-muted mb-1">Lokasi:</h6>
                    <div class="fw-semibold">
                        {{ $inspection->masterApar->gedung->nama }} - {{ $inspection->masterApar->lokasi }}
                    </div>
                </div>
                <div class="mb-4">
                    <h6 class="text-muted mb-1">Petugas:</h6>
                    <div class="fw-semibold">{{ $inspection->user->name }}</div>
                </div>
                <div class="mb-4">
                    <h6 class="text-muted mb-1">Tanggal Inspeksi:</h6>
                    <div class="fw-semibold">{{ $inspection->dateFormatted }}</div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Hasil Pemeriksaan:</h6>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-bordered">
                        <thead class="table-light text-left">
                            <tr>
                                <th style="width: 40%">Item Check</th>
                                <th style="width: 10%">Nilai</th>
                                <th style="width: 50%">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inspection->details as $detail)
                            <tr>
                                <td>{{ $detail->itemCheck->name }}</td>
                                <td class="text-center">
                                    @if ($detail->value == 'B')
                                        <span class="badge bg-success">Baik</span>
                                    @elseif ($detail->value == 'R')
                                        <span class="badge bg-danger">Rusak</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Ada</span>
                                    @endif
                                </td>
                                <td>{{ $detail->remark ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection