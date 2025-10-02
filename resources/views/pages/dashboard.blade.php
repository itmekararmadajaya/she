@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    {{-- Container untuk Push Notifikasi --}}
    <div id="notification-container" class="position-fixed bottom-0 right-0 p-3" style="z-index: 1050;">
        {{-- Notifikasi akan ditambahkan di sini oleh JavaScript --}}
    </div>

    {{-- Kode notifikasi yang lama telah dihapus dan diganti dengan JavaScript di bawah --}}

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
                                    <h6 class="mb-0 fw-semibold fs-3">PENGELUARAN</h6>
                                    <i class="fas fa-arrow-up-right-from-square fa-2x" style="color: white"></i>
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
                    <div class="card h-100 card-custom-rounded shadow-lg d-flex flex-column" style="max-height: 655px; overflow-y: auto;">
                        <div class="card-header bg-white rounded-top-4 p-4">
                            <h2 class="mb-0 h3 fw-semibold">Rekap Inspeksi Bulanan</h2>
                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($startDate)->isoFormat('D MMMM YYYY') }} hingga {{ \Carbon\Carbon::parse($endDate)->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 46px;">No</th>
                                        <th style="width: 89px;">AREA</th>
                                        {{-- Kolom baru untuk Jumlah Total APAR --}}
                                        <th class="text-center" style="width: 131px;">TOTAL</th>
                                        {{-- Pindahkan kolom 'SUDAH' ke kanan --}}
                                        <th class="text-center" style="width: 130px;">SUDAH</th>
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
                                                {{-- Tampilkan jumlah total APAR --}}
                                                <td class="text-center" style="width: 130px;">{{ $data['inspected'] + $data['uninspected'] }}</td>
                                                {{-- Pindahkan kolom 'SUDAH' ke kanan --}}
                                                <td class="text-center" style="width: 130px;">{{ $data['inspected'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
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
                                        <td colspan="2" style="width: 135px;">TOTAL</td>
                                        {{-- Tambahkan total untuk kolom baru --}}
                                        <td class="text-center" style="width:100px;">{{ $totalInspected + $totalUninspected }}</td>
                                        {{-- Pindahkan total 'SUDAH' ke kanan --}}
                                        <td class="text-center" style="width: 100px;">{{ $totalInspected }}</td>
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
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifications = [
            @if (isset($expiredApar) && $expiredApar->isNotEmpty() || isset($expiringSoonApar) && $expiringSoonApar->isNotEmpty())
            {
                type: 'danger',
                title: 'PERINGATAN KADALUARSA!',
                message: 'Mohon segera refill',
                link: '{{ route('laporan.apar.refill-index') }}'
            },
            @endif

            @if (isset($uninspectedApars) && $uninspectedApars->isNotEmpty())
            {
                type: 'warning',
                title: 'APAR BELUM INSPEKSI!',
                message: 'Mohon segera inspeksi',
                link: '{{ route('apar.uninspected') }}'
            },
            @endif

            @if (isset($usedApars) && $usedApars->isNotEmpty())
            {
                type: 'success',
                title: 'APAR TELAH DIGUNAKAN!',
                message: 'Mohon segera refill',
                link: '{{ route('history.pengisian') }}'
            }
            @endif
        ];

        function createToast(notification) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${notification.type} border-0 show mt-2`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            const toastBody = document.createElement('div');
            toastBody.className = 'd-flex';

            const toastContent = document.createElement('div');
            toastContent.className = 'toast-body';
            toastContent.innerHTML = `<b>${notification.title}</b><br>${notification.message}`;

            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'btn-close btn-close-white me-2 m-auto';
            closeButton.setAttribute('data-bs-dismiss', 'toast');
            closeButton.setAttribute('aria-label', 'Close');

            closeButton.addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            });

            if (notification.link && notification.link !== '#') {
                const link = document.createElement('a');
                link.href = notification.link;
                link.className = 'text-decoration-none text-white';
                link.appendChild(toastContent);
                toastBody.appendChild(link);
            } else {
                toastBody.appendChild(toastContent);
            }
            toastBody.appendChild(closeButton);
            toast.appendChild(toastBody);

            document.getElementById('notification-container').appendChild(toast);
        }

        function showNotifications() {
            notifications.forEach(notification => createToast(notification));
        }

        // --- Logika notifikasi baru yang tidak mengganggu skrip lain ---
        const lastShown = localStorage.getItem('lastNotificationShown');
        const now = Date.now();
        const expiryDuration = 1000; 

        if (!lastShown || (now - lastShown) > expiryDuration) {
            showNotifications();
            localStorage.setItem('lastNotificationShown', now);
        }
        // --- Akhir logika notifikasi baru ---
    });

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
</script>
@endpush