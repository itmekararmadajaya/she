@extends('layouts.guest')

@section('title', 'Inspeksi APAR')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Laporan Apar Yearly (Tahunan)</h4>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 px-4 pt-4">
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <!-- Form Filter -->
                    <div class="d-flex justify-content-between align-items-end">
                        <div class="" style="width: 100%">
                            <form method="GET" action="{{ route('apar.user.laporan.yearly') }}" class="row gx-2 gy-1 align-items-end mb-3">
                                <div class="col-md-3 mb-3">
                                    <label for="end_date" class="form-label">Tahun</label>
                                    <input type="number" name="year" id="year" min="1900" max="2100" class="form-control" value="{{$year}}" placeholder="Tahun" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="end_date" class="form-label">Kode APAR</label>
                                    <input type="text" name="kode" id="kode" class="form-control" placeholder="Kode APAR" value="{{$kode}}" required>
                                </div>
                                <div class="col-md-auto mb-3">
                                    <button type="submit" class="btn btn-primary"><i class="ph ph-magnifying-glass"></i></button>
                                    <button type="button" class="btn btn-secondary" onclick="openScanner()"><i class="ph ph-qr-code"></i></button>
                                </div>
                            </form>
                        </div>                
                    </div>
                </div>
            </div>
            @if (!empty($apar))
                <x-apar-inspection-card :apar="$apar" :year="$year" />
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center">
                            @if (empty($apar))
                                <span>Silahkan scan/cari APAR terlebih dahulu</span>
                            @endif
                        </div>
                        <a href="{{route('main-menu')}}" class="btn btn-light">Kembali</a>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
            <div class="modal-header">
                <h5 class="modal-title">Scan QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reader" style="width: 100%;"></div>
            </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let scanner;

        function openScanner() {
            const modal = new bootstrap.Modal(document.getElementById('scannerModal'));
            modal.show();

            scanner = new Html5Qrcode("reader");
            scanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText) => {
                    document.getElementById("kode_apar").value = decodedText;
                    scanner.stop().then(() => {
                        modal.hide();
                    }).catch(err => console.log("Stop Error: ", err));
                },
                (errorMessage) => {
                    // optional: console.log("Scan error", errorMessage);
                    console.log(errorMessage);
                }
            ).catch(err => console.log("Start Error: ", err));
        }

        // Stop scanner when modal is closed manually
        document.getElementById('scannerModal').addEventListener('hidden.bs.modal', () => {
            if (scanner) {
                scanner.stop().catch(err => console.log("Stop Error on modal close: ", err));
            }
        });
    </script>
@endpush