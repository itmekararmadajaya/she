@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="">
        <div class="">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-1 h3">Dashboard</h1>
            </div>
        </div>
        <div class="">
            <!-- Form Filter -->
            <div>
                <form method="GET" action="{{ route('dashboard') }}" class="row gx-2 gy-1 align-items-end mb-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $start_date }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $end_date }}" class="form-control">
                    </div>

                    {{-- Tombol --}}
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>


            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card p-4">
                        <div class="card-block">
                            <h6 class="mb-4">Summary Inspeksi APAR</h6>
                            <div class="row d-flex align-items-center">
                                <div class="col-9">
                                    <h3 class="f-w-300 d-flex align-items-center m-b-0"><i class="feather icon-arrow-up text-c-green f-30 m-r-10"></i>249</h3>
                                </div>
                                <div class="col-3 text-right">
                                    <p class="m-b-0">67%</p>
                                </div>
                            </div>
                            <div class="progress m-t-30" style="height: 7px;">
                                <div class="progress-bar progress-c-theme" role="progressbar" style="width: 50%;" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header fw-semibold">Grafik Inspeksi APAR per Gedung</div>
                        <div class="card-body">
                            <canvas id="aparChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('aparChart').getContext('2d');
            const aparChart = new Chart(ctx, {
            type: 'bar', // bisa juga: 'line', 'pie', dll
            data: {
                labels: {!! json_encode($labels) !!}, // contoh: ['Jan', 'Feb', 'Mar']
                datasets: [{
                label: 'Jumlah Inspeksi',
                data: {!! json_encode($data) !!}, // contoh: [5, 7, 3]
                backgroundColor: '#42a5f5',
                borderColor: '#1e88e5',
                borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                    precision: 0 // biar tidak ada angka desimal
                    }
                }
                }
            }
            });
        });
        </script>
@endpush