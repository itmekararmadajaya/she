@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4 card-custom-rounded">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Daftar Transaksi</h1>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form id="filterForm" action="{{ route('transaksi.index') }}" method="GET" class="mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            id="start_date" 
                            name="start_date" 
                            value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Selesai</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            id="end_date" 
                            name="end_date" 
                            value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-light border w-100">Reset</a>
                    </div>
                </div>
            </form>

            <div class="mb-3 d-flex justify-content-between">
                <a href="{{ route('transaksi.create') }}" class="btn btn-info">Tambah Transaksi Baru</a>
                <a href="#" id="exportExcelBtn" class="btn btn-success text-white">Download Excel</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode APAR</th>
                            <th>Vendor</th>
                            <th>Kebutuhan</th>
                            <th>Tanggal Pembelian</th>
                            <!-- <th>Tanggal Pelunasan</th> -->
                            <th>Biaya</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transaksis as $transaksi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($transaksi->masterApar)->kode }}</td>
                                <td>{{ $transaksi->vendor->nama_vendor }}</td>
                                <td>{{ $transaksi->kebutuhan->kebutuhan }}</td>
                                <td>{{ $transaksi->tanggal_pembelian }}</td>
                                <!-- <td>{{ $transaksi->tanggal_pelunasan }}</td> -->
                                <td>
                                    @php
                                        $biaya = optional($transaksi->hargaKebutuhan)->biaya ?? 0;
                                        $kebutuhanType = optional($transaksi->kebutuhan)->kebutuhan;
                                        $ukuranApar = optional($transaksi->masterApar)->ukuran ?? 1;
                                        
                                        // Kalikan biaya dengan ukuran jika kebutuhan adalah 'Beli Baru' atau 'Isi Ulang'
                                        if (in_array($kebutuhanType, ['Beli Baru', 'Isi Ulang'])) {
                                            $biaya = $biaya * $ukuranApar;
                                        }
                                    @endphp
                                    Rp {{ number_format($biaya, 0, ',', '.') }}
                                </td>
                                <td>
                                    <a href="{{ route('transaksi.edit', $transaksi->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('transaksi.destroy', $transaksi->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><strong>Total Biaya</strong></td>
                            <td>
                                <strong>
                                    Rp {{ number_format(
                                        $transaksis->sum(function($transaksi) {
                                            $biaya = optional($transaksi->hargaKebutuhan)->biaya ?? 0;
                                            $kebutuhanType = optional($transaksi->kebutuhan)->kebutuhan;
                                            $ukuranApar = optional($transaksi->masterApar)->ukuran ?? 1;
                                            
                                            // Kalikan biaya dengan ukuran jika kebutuhan adalah 'Beli Baru' atau 'Isi Ulang'
                                            if (in_array($kebutuhanType, ['Beli Baru', 'Isi Ulang'])) {
                                                return $biaya * $ukuranApar;
                                            }
                                            return $biaya;
                                        }), 
                                        0, ',', '.'
                                    ) }}
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-right"><strong>Total Transaksi: {{ $transaksis->count() }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('exportExcelBtn').addEventListener('click', function (event) {
            event.preventDefault();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            let url = "{{ route('transaksi.export.excel') }}";
            let params = [];
            if (startDate) {
                params.push(`start_date=${startDate}`);
            }
            if (endDate) {
                params.push(`end_date=${endDate}`);
            }
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            window.location.href = url;
        });
    });
</script>
@endsection