@extends('layouts.app')

@section('title', 'Laporan APAR Yearly (Tahunan)')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3">Laporan Inspeksi Tahunan APAR</h1>
            </div>
        </div>
        <div class="card-body">
            <!-- Form Filter -->
            <div class="d-flex justify-content-between align-items-end">
                <div class="" style="width: 60%">
                    <form method="GET" action="{{ route('laporan.apar.yearly-index') }}" class="row gx-2 gy-1 align-items-end mb-3">
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Tahun</label>
                            <input type="number" name="year" id="year" min="1900" max="2100" class="form-control" value="{{$year}}" placeholder="Tahun" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Kode APAR</label>
                            <input type="text" name="kode" id="kode" class="form-control" placeholder="Kode APAR" value="{{$kode}}" required>
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>                
            </div>
        </div>
    </div>
     @if ($apar != "")
     <div class="row p-0">
        <div class="col-md-6">
            <x-apar-inspection-card :apar="$apar" :year="$year" />
        </div>
    </div>
    @endif
@endsection