@extends('layouts.app')

@section('title', 'Laporan APAR Rusak')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3">Laporan APAR Rusak</h1>
                <span class="h4">Total Data : {{$totalData}}</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Form Filter -->
            <div class="d-flex justify-content-between align-items-end">
                <div class="" style="width: 60%">
                    <form method="GET" action="{{ route('laporan.apar.rusak-index') }}" class="row gx-2 gy-1 align-items-end mb-3">
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Bulan</label>
                            <input type="month" name="month" id="end_date" value="{{ $month }}" class="form-control">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div>
                    <form method="POST" action="{{route('laporan.apar.rusak-export')}}" class="row gx-2 gy-1 align-items-end mb-3">
                        @csrf
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-success w-100">Export</button>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 1px;;">No</th>
                                <th>Kode</th>
                                <th>Gedung</th>
                                <th>Lokasi</th>
                                <th>Tgl Inspeksi</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($aparInspections as $i => $inspection)
                                <tr>
                                    <td>{{$i+1 }}</td>
                                    <td>
                                        {{$inspection->masterApar->kode}}
                                    </td>
                                    <td>
                                        {{$inspection->masterApar->gedung->nama}}
                                    </td>
                                    <td>
                                        {{$inspection->masterApar->lokasi}}
                                    </td>
                                    <td>
                                        {{$inspection->dateFormatted}}
                                    </td>
                                    <td>
                                        {{$inspection->user->name}}
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection