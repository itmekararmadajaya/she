@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    {{-- Bagian Notifikasi Berjalan --}}
    <div class="col-md-12 mb-1">
        {{-- Banner Peringatan Kadaluarsa (Merah) --}}
        @if (isset($expiredApar) && $expiredApar->isNotEmpty() || isset($expiringSoonApar) && $expiringSoonApar->isNotEmpty())
            <a href="{{ route('laporan.apar.refill-index') }}" class="text-decoration-none d-block">
                <div class="alert alert-danger mb-2 card p-3 card-custom-rounded shadow-lg" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span class="fw-bold">PERINGATAN KADALUARSA!</span>
                    <div class="d-flex overflow-hidden">
                        <span class="text-nowrap" style="animation: scroll-left 20s linear infinite;">
                            @foreach ($expiredApar as $apar)
                            <span>APAR dengan kode <b>{{ $apar->kode }}</b> di lokasi {{ $apar->gedung->nama }} - {{ $apar->lokasi }} <b>sudah kadaluarsa sejak {{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->isoFormat('D MMMM YYYY') }}</b>.</span> | &nbsp;
                            @endforeach
                            @foreach ($expiringSoonApar as $apar)
                            <span>APAR dengan kode <b>{{ $apar->kode }}</b> di lokasi {{ $apar->gedung->nama }} - {{ $apar->lokasi }} <b>akan kadaluarsa pada {{ \Carbon\Carbon::parse($apar->tgl_kadaluarsa)->isoFormat('D MMMM YYYY') }}</b>.</span> | &nbsp;
                            @endforeach
                        </span>
                    </div>
                </div>
            </a>
        @endif

        {{-- Banner Peringatan Belum Inspeksi (Kuning) --}}
        @if (isset($uninspectedApars) && $uninspectedApars->isNotEmpty())
            <a href="{{ route('apar.uninspected') }}" class="text-decoration-none d-block">
                <div class="alert alert-warning mb-2 card p-3 card-custom-rounded shadow-lg" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span class="fw-bold">APAR BELUM INSPEKSI!</span>
                    <div class="d-flex overflow-hidden">
                        <span class="text-nowrap" style="animation: scroll-left 20s linear infinite;">
                            @foreach ($uninspectedApars as $apar)
                                <span>APAR dengan kode <b>{{ $apar->kode }}</b> di lokasi {{ $apar->gedung->nama ?? 'N/A' }} - {{ $apar->lokasi }} <b>belum diinspeksi bulan ini</b>.</span> &nbsp;&nbsp; | &nbsp;&nbsp;
                            @endforeach
                        </span>
                    </div>
                </div>
            </a>
        @endif
    </div>
    


    <div class="card shadow-lg card p-3 card-custom-rounded">
        {{-- Bagian Filter Tanggal --}}
        <div class="card p-3 card-custom-rounded shadow-lg">
            <form action="{{ route('dashboard') }}" method="GET">
                <div class="row d-flex align-items-center">
                    <div class="col-md-5">
                        <input type="month" name="start_date" id="start_date" class="form-control" placeholder="Bulan Mulai" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-5">
                        <input type="month" name="end_date" id="end_date" class="form-control" placeholder="Bulan Selesai" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-auto d-flex gap-2 mt-2 mt-md-0">
                        <button type="submit" class="btn w-100" style="background-color: #169994; color: #fff;">Filter</button>
                        <a href="{{ route('dashboard') }}" class="btn w-100" style="background-color: #F87171; color: #fff;">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        {{-- Akhir Bagian Filter Tanggal --}}
        {{-- Bagian Card Dashboard --}}
        <div class="col-md-12 mb-1">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white h-100 card-custom-rounded p-3 shadow-lg" style="background-color: #F87171">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold fs-3">TOTAL NOT GOOD</h6>
                                <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                            </div>
                            <h3 class="mt-1 mb-0 display-4 fw-bold">{{ array_sum($dataNotGood) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-white h-100 card-custom-rounded p-3 shadow-lg" style="background-color: #169994">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold fs-3">TOTAL GOOD</h6>
                                <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                            </div>
                            <h3 class="mt-1 mb-0 display-4 fw-bold">{{ array_sum($dataGood) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <a href="{{ route('transaksi.index') }}">
                        <div class="card text-white h-100 card-custom-rounded p-3 shadow-lg" style="background-color: #39ade3ff">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold fs-3">TOTAL PENGELUARAN</h6>
                                    <i class="fas fa-arrow-up-right-from-square fa-sm"></i>
                                </div>
                                <h3 class="mt-1 mb-0 display-4 fw-bold">{{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        {{-- Akhir Bagian Card Dashboard --}}

        {{-- Container utama untuk 3 card --}}
        <div class="col-md-12 mb-1">
            <div class="row">
                {{-- Grup Kiri (Grafik) --}}
                <div class="col-md-8 d-flex flex-column">
                    {{-- Card untuk Grafik Inspeksi Apar --}}
                    <div class="col-md-12 mb-1">
                        <div class="card card-custom-rounded p-1 shadow-lg">
                            <div class="card-header bg-white rounded-top-4 p-4">
                                <h2 class="mb-0 h3 fw-semibold">Grafik Inspeksi Apar</h2>
                            </div>
                            <div class="card-body">
                                <canvas id="inspeksiAparChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Card untuk Grafik Pengeluaran Bulanan --}}
                    <div class="col-md-12 mb-1">
                        <div class="card card-custom-rounded p-1 shadow-lg">
                            <div class="card-header bg-white rounded-top-4 p-4">
                                <h2 class="mb-0 h3 fw-semibold">Grafik Pengeluaran Bulanan</h2>
                            </div>
                            <div class="card-body">
                                <canvas id="pengeluaranChart" width="400" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Grup Kanan (Tabel) --}}
                <div class="col-md-4">
                    {{-- Card untuk Rekap Inspeksi Bulanan (Tabel) --}}
                    <div class="card h-100 card-custom-rounded shadow-lg d-flex flex-column" style="max-height: 655px; overflow-y: auto;">
                        <div class="card-header bg-white rounded-top-4 p-4">
                            <h2 class="mb-0 h3 fw-semibold">Rekap Inspeksi Bulanan</h2>
                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} hingga {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>AREA</th>
                                        <th class="text-center" style="width: 130px;">SUDAH</th>
                                        <th class="text-center" style="width: 130px;">BELUM</th>
                                    </tr>
                                </thead>
                            </table>
                            
                            {{-- Bagian Tabel yang bisa di-scroll --}}
                            <div style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-bordered table-hover align-middle rounded-3">
                                    <tbody>
                                        @forelse ($inspectionsPerArea as $i => $data)
                                        <tr>
                                            <td style="width: 50px;">{{ $i + 1 }}</td>
                                            <td>{{ $data['nama'] }}</td>
                                            <td class="text-center" style="width: 130px;">{{ $data['inspected'] }}</td>
                                            <td class="text-center" style="width: 130px;">{{ $data['uninspected'] }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Tidak ada data inspeksi yang ditemukan dalam periode ini.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Bagian Total (tidak bisa di-scroll) --}}
                            <div class="mt-auto">
                                <table class="table table-bordered align-middle mb-0">
                                    <tr class="fw-bold table-primary">
                                        <td colspan="2" style="width: 50px;">TOTAL</td>
                                        <td class="text-center" style="width: 130px;">{{ $totalInspected }}</td>
                                        <td class="text-center" style="width: 130px;">{{ $totalUninspected }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
.card-custom-rounded {
    border-radius: 1rem !important;
}
@keyframes scroll-left {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Skrip untuk Grafik Inspeksi Apar
    var ctxApar = document.getElementById('inspeksiAparChart').getContext('2d');
    var inspeksiAparChart = new Chart(ctxApar, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [
                {
                    label: 'GOOD',
                    data: @json($dataGood),
                    backgroundColor: '#169994',
                    borderColor: '#169994',
                    borderWidth: 1,
                    borderRadius: 5,
                },
                {
                    label: 'NOT GOOD',
                    data: @json($dataNotGood),
                    backgroundColor: '#F87171',
                    borderColor: '#F87171',
                    borderWidth: 1,
                    borderRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: false,
                    beginAtZero: true,
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'top',
                },
            }
        }
    });

    // Skrip untuk Grafik Pengeluaran Bulanan
    const ctxPengeluaran = document.getElementById('pengeluaranChart').getContext('2d');
    new Chart(ctxPengeluaran, {
        type: 'line',
        data: {
            labels: @json($labelsPengeluaran),
            datasets: [{
                label: 'Total Pengeluaran (Rp)',
                data: @json($dataPengeluaran),
                backgroundColor: 'rgba(57, 173, 227, 0.5)',
                borderColor: '#39ade3ff',
                borderWidth: 2,
                pointBackgroundColor: '#39ade3ff',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#39ade3ff',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                },
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endpush