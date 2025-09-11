@extends('layouts.guest')

@section('title', 'Inspeksi APAR')

@section('content')
<div class="container mt-5 card-custom-rounded">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="text-center mb-4">
        <h4 class="fw-bold">Inspeksi APAR</h4>
      </div>
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 px-4 pt-4">
        </div>
        <div class="card-body px-4 pb-4 pt-0">
            <form action="{{route('apar.inspeksi')}}" method="GET">
                <div class="row">
                    <div class="form-group mb-3">
                        <label for="nama">APAR</label>
                        <div class="d-flex gap-1">
                            <input type="text" name="kode_apar" class="form-control @error('kode_apar') is-invalid @enderror"
                            id="kode_apar" value="{{ $codeApar }}" placeholder="Kode APAR">
                            <button type="submit" class="btn btn-primary"><i class="ph ph-magnifying-glass"></i></button>
                            <button type="button" class="btn btn-secondary" onclick="openScanner()"><i class="ph ph-qr-code"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            @if (!empty($apar))
                <!-- Detail APAR -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="ph ph-fire-extinguisher me-2"></i> Detail APAR
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-borderless table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th class="text-nowrap">Kode</th>
                                        <td class="text-muted">:</td>
                                        <td class="fw-semibold">{{ $apar->kode }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Jenis</th>
                                        <td class="text-muted">:</td>
                                        <td class="fw-semibold">{{ $apar->jenis_isi }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Ukuran</th>
                                        <td class="text-muted">:</td>
                                        <td class="fw-semibold">{{ $apar->ukuran }} {{ $apar->satuan }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-nowrap">Lokasi</th>
                                        <td class="text-muted">:</td>
                                        <td class="fw-semibold">{{ $apar->gedung->nama }} - {{ $apar->lokasi }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                @if ($doneInspection == true)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-warning text-dark fw-bold">
                            <i class="ph ph-warning me-2"></i> APAR sudah di inspeksi bulan ini. Lanjutkan inspeksi jika ingin update hasil inspeksi terbaru.
                        </div>
                    </div>
                @endif

                <!-- Item Check -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-secondary text-white fw-bold">
                        <i class="ph ph-fire-extinguisher me-2"></i> Item Check
                    </div>
                    <div class="card-body">
                        <form action="{{route('apar.inspeksi')}}" method="post">
                            @csrf
                            <input type="text" class="d-none" name="kode" value="{{$apar->kode}}">
                            @foreach ($itemChecks as $index => $itemCheck)
                                <div class="mb-4 p-3 border rounded shadow-sm bg-light">
                                    <label class="fw-semibold d-block mb-2">{{ $itemCheck->name }}</label>

                                    <!-- Select Kondisi -->
                                    <select name="checks[{{ $index }}][value]" class="form-select mb-2 w-50" required>
                                        <option value="" disabled selected>Pilih kondisi</option>
                                        <option value="B">Baik</option>
                                        <option value="R">Rusak</option>
                                        <option value="T/A">Tidak Ada</option>
                                    </select>

                                    <!-- Remark -->
                                    <input type="text" name="checks[{{ $index }}][remark]" class="form-control" placeholder="Keterangan / Remark">
                                    
                                    <!-- Hidden item_check_id -->
                                    <input type="hidden" name="checks[{{ $index }}][item_check_id]" value="{{ $itemCheck->id }}">
                                </div>
                            @endforeach

                            <button type="submit" class="btn btn-primary w-100 mt-3">Simpan</button>
                        </form>

                    </div>
                </div>
            @else
                <div class="text-center">
                    Silahkan scan/cari APAR terlebih dahulu
                </div>
            @endif

            <div class="d-flex justify-content-end">
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
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false,
            position: 'center'
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: "{{ session('error') }}",
            timer: 3000,
            showConfirmButton: false,
            position: 'center'
        });
    </script>
@endif

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

<style>
.card-custom-rounded {
    border-radius: 1rem !important;
}
@keyframes scroll-left {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}
</style>