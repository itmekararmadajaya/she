@extends('layouts.app')

@section('content')

<div class="container">
    <div class="card shadow mb-4 card-custom-rounded">
        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap">
            <h1 class="h3 mb-0 text-gray-800">Riwayat Penggunaan APAR</h1>
            <br></br>
            <div class="d-flex align-items-center flex-wrap mt-2 mt-md-0">
                <form id="filter-form" class="d-flex me-3 align-items-center mb-2 mb-md-0" action="{{ route('history.pengisian') }}" method="GET">
                    <span class="me-2 text-gray-600">Dari:</span>
                    <input type="month" name="start_date" id="start-date-filter" class="form-control me-3" placeholder="Bulan Mulai" value="{{ request('start_date') }}">
                    <span class="me-2 text-gray-600">Hingga:</span>
                    <input type="month" name="end_date" id="end-date-filter" class="form-control me-3" placeholder="Bulan Selesai" value="{{ request('end_date') }}">
                    <div class="d-flex gap-2 mt-2 mt-md-0">
                        <button type="submit" class="btn" style="background-color: #169994; color: #fff;">Filter</button>
                        <a href="{{ route('history.pengisian') }}" class="btn" style="background-color: #F87171; color: #fff;">Reset</a>
                    </div>
                </form>
                <a href="{{ route('history.pengisian.export') }}" class="btn btn-success" id="export-excel-btn">
                    <i class="fas fa-file-excel me-2"></i> Ekspor ke Excel
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Petugas</th>
                            <th>Kode APAR</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Alasan</th>
                            <th style="width: 120px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($refillHistory as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->user->name }}</td>
                            <td>{{ $item->masterApar->kode }}</td>
                            <td>{{ $item->gedung->nama }} - {{ $item->lokasi }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_penggunaan)->isoFormat('D MMMM YYYY') }}</td>
                            <td>{{ $item->alasan }}</td>
                            <td class="status-cell">
                                @if($item->status == 'GOOD')
                                    <span class="status-badge status-good">GOOD</span>
                                @else
                                    <span class="status-badge status-not-good">NOT GOOD</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data riwayat Penggunaan APAR.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.card-custom-rounded {
    border-radius: 1rem !important;
}

/* Status Styling */
.status-badge {
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: bold;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-not-good {
    background-color: #dc3545; /* Merah untuk NOT GOOD */
}

.status-good {
    background-color: #28a745; /* Hijau untuk GOOD */
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');
    const exportBtn = document.getElementById('export-excel-btn');

    function updateExportLink() {
        let url = '{{ route("history.pengisian.export") }}';
        let params = new URLSearchParams();

        const startDate = startDateFilter.value;
        const endDate = endDateFilter.value;

        if (startDate && endDate) {
            const [startYear, startMonth] = startDate.split('-');
            const [endYear, endMonth] = endDate.split('-');
            
            params.append('start_month', startMonth);
            params.append('start_year', startYear);
            params.append('end_month', endMonth);
            params.append('end_year', endYear);
        }

        exportBtn.href = url + '?' + params.toString();
    }

    startDateFilter.addEventListener('change', updateExportLink);
    endDateFilter.addEventListener('change', updateExportLink);

    updateExportLink();
});
</script>
@endpush