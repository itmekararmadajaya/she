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
                    <div class="col-md-3" style="width: 15%">
                        <label for="end_date" class="form-label">Bulan</label>
                        <input type="month" name="month" id="end_date" value="{{ $month }}" class="form-control">
                    </div>

                    {{-- Tombol --}}
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>


            <!-- APAR -->
            <div>
                <div class="row g-3 mb-4">
                    {{-- <div class="col-md-4">
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
                    </div> --}}

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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = {!! json_encode(collect($generateGraphAparPerGedung)->pluck('gedung')) !!};
            const okData = {!! json_encode(collect($generateGraphAparPerGedung)->pluck('OK')) !!};
            const nokData = {!! json_encode(collect($generateGraphAparPerGedung)->pluck('NOK')) !!};
            
            const allValues = okData.concat(nokData);
            const maxValue = Math.max(...allValues);
            const suggestedMax = maxValue + 3;

            const ctx = document.getElementById('aparChart').getContext('2d');
            const aparChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'OK',
                            data: okData,
                            backgroundColor: '#4caf50',
                            borderColor: '#388e3c',
                            borderWidth: 1
                        },
                        {
                            label: 'NOK',
                            data: nokData,
                            backgroundColor: '#ef5350',
                            borderColor: '#c62828',
                            borderWidth: 1
                        },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#000',
                            font: {
                                weight: 'bold'
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: suggestedMax,
                        ticks: {
                            precision: 0
                        }
                    }
                    }
                },
                plugins: [ChartDataLabels]
            });
        });
        </script>
@endpush