@extends('layouts.app')

@section('title', 'Laporan APAR Refill')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0 h3">Laporan APAR Refill</h1>
                <span class="h4">Total Data : {{$totalData}}</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Form Filter -->
            <div class="d-flex justify-content-between align-items-end">
                <div>
                    <form method="POST" action="{{route('laporan.apar.refill-export')}}" class="row gx-2 gy-1 align-items-end mb-3">
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
                                <th>Tgl Refill</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($aparPerluRefill as $i => $refill)
                                <tr>
                                    <td>{{$i+1 }}</td>
                                    <td>
                                        {{$refill->kode}}
                                    </td>
                                    <td>
                                        {{$refill->gedung->nama}}
                                    </td>
                                    <td>
                                        {{$refill->lokasi}}
                                    </td>
                                    <td>
                                        {{$refill->tglrefillFormatted}}
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