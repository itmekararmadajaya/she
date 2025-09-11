@extends('layouts.app')

@section('title', 'APAR Belum Diinspeksi')

@section('content')
    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-header bg-white rounded-top-4 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3 fw-semibold">APAR Belum Diinspeksi Bulan Ini</h1>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <form action="{{ route('apar.uninspected') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="month" class="form-label">Bulan</label>
                            <select name="month" id="month" class="form-control">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Tahun</label>
                            <select name="year" id="year" class="form-control">
                                @foreach(range(Carbon\Carbon::now()->year, Carbon\Carbon::now()->year - 5) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('apar.uninspected') }}" class="btn btn-warning">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <p><b>Total APAR yang belum diinspeksi pada bulan {{ \Carbon\Carbon::createFromDate($year, $month)->translatedFormat('F Y') }}: {{ $totalData }}</b></p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle rounded-3 overflow-hidden">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 1px;">No</th>
                            <th>Kode APAR</th>
                            <th>Lokasi</th>
                            <th>Jenis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uninspectedApars as $i => $apar)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <span class="fw-semibold">{{$apar->kode}}</span>
                                </td>
                                <td>
                                    {{$apar->gedung->nama ?? 'N/A'}} - {{$apar->lokasi}}
                                </td>
                                <td>
                                    {{ $apar->jenisIsi->jenis_isi ?? 'Jenis isi tidak ditemukan' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="ph ph-info-circle fs-4 me-2"></i>
                                    Semua APAR sudah diinspeksi pada bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection