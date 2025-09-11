<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Inspeksi APAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
        }
        .card-footer {
            background-color: #e9ecef;
        }
        @media (max-width: 768px) {
            .row.mb-3 > div {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                <h4>Riwayat Inspeksi APAR</h4>
                <p class="mb-0">APAR Kode: {{ $apar->kode }}</p>
                <p class="mb-0">Lokasi: {{ $apar->gedung->nama ?? '-' }} - {{ $apar->lokasi }}</p>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 col-12">
                        <strong>Jenis Isi:</strong> {{ $apar->jenis_isi ?? '-' }}
                    </div>
                    <div class="col-md-4 col-12">
                        <strong>Jenis Pemadam:</strong> {{ $apar->jenis_pemadam ?? '-' }}
                    </div>
                    <div class="col-md-4 col-12">
                        <strong>Tgl. Kadaluarsa:</strong> {{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->isoFormat('D MMMM YYYY') ?? '-' }}
                    </div>
                    <div class="col-md-4 col-12">
                        <strong>Ukuran:</strong> {{ $apar->ukuran ?? '-' }} {{ $apar->satuan ?? '' }}
                    </div>
                </div>
                <hr>
                @if($apar->inspections->isEmpty())
                    <div class="alert alert-info text-center">
                        Belum ada riwayat inspeksi untuk APAR ini.
                    </div>
                @else
                    {{-- Mengambil inspeksi terbaru saja --}}
                    @php
                        $latestInspection = $apar->inspections->sortByDesc('date')->first();
                    @endphp
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($latestInspection->date)->isoFormat('D MMMM YYYY') }} <br>
                            <strong>Diperiksa oleh:</strong> {{ $latestInspection->user->name ?? 'Tidak diketahui' }} <br>
                            <strong>Status:</strong>
                            @php
                                $allGood = $latestInspection->details->every(fn($detail) => $detail->value === 'B');
                            @endphp
                            @if($allGood)
                                <span class="badge bg-success">GOOD</span>
                            @else
                                <span class="badge bg-danger">NOT GOOD</span>
                            @endif
                            <br>
                            @if($latestInspection->details->isNotEmpty())
                                <h6 class="mt-2 mb-1">Detail Cek:</h6>
                                <ul class="list-unstyled">
                                    @foreach($latestInspection->details as $detail)
                                        <li class="mb-1">
                                            {{ $detail->itemCheck->name ?? 'Item tidak diketahui' }}:
                                            @if($detail->value === 'B')
                                                <span class="text-success">Baik</span>
                                            @else
                                                <span class="text-danger">Rusak/Tidak Lengkap</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    </ul>
                @endif
            </div>
            <div class="card-footer text-center text-muted">
                <small>Data ini disajikan secara publik untuk transparansi. Terakhir diperbarui: {{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }} WIB</small>
            </div>
        </div>
    </div>
</body>
</html>