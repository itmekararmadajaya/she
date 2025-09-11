@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4 card-custom-rounded">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Edit Transaksi</h1>
        </div>
        <div class="card-body">
            <form id="transaksiForm" action="{{ route('transaksi.update', $transaksi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="vendor_id" class="form-label">Vendor</label>
                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                        <option value="">Pilih Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ $transaksi->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kebutuhan_id" class="form-label">Kebutuhan</label>
                    <select class="form-control" id="kebutuhan_id" name="kebutuhan_id" required>
                        <option value="">Pilih Kebutuhan</option>
                        @foreach($kebutuhans as $kebutuhan)
                            <option value="{{ $kebutuhan->id }}" {{ $transaksi->kebutuhan_id == $kebutuhan->id ? 'selected' : '' }}>{{ $kebutuhan->kebutuhan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="aparGroup">
                    <label for="master_apar_id" class="form-label">APAR (Opsional)</label>
                    <select class="form-control" id="master_apar_id" name="master_apar_id">
                        <option value="">Pilih APAR</option>
                        @foreach($masterApars as $apar)
                            <option value="{{ $apar->id }}" {{ $transaksi->master_apar_id == $apar->id ? 'selected' : '' }}>{{ $apar->kode }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Kelompok dropdown untuk "Isi Ulang" -->
                <div id="isiUlangGroup" style="display:none;">
                    <div class="mb-3">
                        <label for="jenis_pemadam_id" class="form-label">Jenis Pemadam</label>
                        <select class="form-control" id="jenis_pemadam_id" name="jenis_pemadam_id">
                            <option value="">Pilih Jenis Pemadam</option>
                            @foreach($jenisPemadams as $jp)
                                <option value="{{ $jp->id }}" {{ optional($transaksi->masterApar)->jenis_pemadam_id == $jp->id ? 'selected' : '' }}>{{ $jp->jenis_pemadam }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_isi_id" class="form-label">Jenis Isi</label>
                        <select class="form-control" id="jenis_isi_id" name="jenis_isi_id">
                            <option value="">Pilih Jenis Isi</option>
                            @foreach($jenisIsis as $ji)
                                <option value="{{ $ji->id }}" {{ optional($transaksi->masterApar)->jenis_isi_id == $ji->id ? 'selected' : '' }}>{{ $ji->jenis_isi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Kelompok dropdown untuk "Ganti Komponen" -->
                <div id="gantiKomponenGroup" style="display:none;">
                    <div class="mb-3">
                        <label for="item_check_id" class="form-label">Jenis Komponen</label>
                        <select class="form-control" id="item_check_id" name="item_check_id">
                            <option value="">Pilih Jenis Komponen</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian</label>
                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="{{ old('tanggal_pembelian', $transaksi->tanggal_pembelian) }}" required>
                </div>

                <div class="mb-3">
                    <label for="biaya" class="form-label">Biaya</label>
                    <input type="text" class="form-control" id="biaya" name="biaya_display" readonly placeholder="Pilih Vendor dan Kebutuhan">
                </div>
                
                <input type="hidden" id="harga_kebutuhan_id" name="harga_kebutuhan_id" value="{{ old('harga_kebutuhan_id', $transaksi->harga_kebutuhan_id) }}">

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Data master dari backend
        const masterApars = @json($masterApars);
        const itemChecks = @json($itemChecks);
        const hargaKebutuhans = @json($hargaKebutuhans);
        const currentTransaksi = @json($transaksi);

        const isiUlangGroup = $('#isiUlangGroup');
        const gantiKomponenGroup = $('#gantiKomponenGroup');
        const jenisPemadamSelect = $('#jenis_pemadam_id');
        const jenisIsiSelect = $('#jenis_isi_id');
        const masterAparSelect = $('#master_apar_id');
        const kebutuhanSelect = $('#kebutuhan_id');
        const biayaInput = $('#biaya');
        const hargaIdInput = $('#harga_kebutuhan_id');
        const itemCheckSelect = $('#item_check_id');

        // Fungsi untuk mengaktifkan/menonaktifkan input
        function toggleInputStatus(isEnabled) {
            jenisPemadamSelect.prop('disabled', !isEnabled);
            jenisIsiSelect.prop('disabled', !isEnabled);
        }

        // Fungsi untuk menampilkan/menyembunyikan kolom dinamis
        function toggleKebutuhanFields() {
            const selectedKebutuhan = kebutuhanSelect.find('option:selected').text().trim();
            
            // Sembunyikan semua grup terlebih dahulu
            isiUlangGroup.hide();
            gantiKomponenGroup.hide();
            
            // Reset dropdown dinamis
            jenisPemadamSelect.val('');
            jenisIsiSelect.val('');
            itemCheckSelect.val('');
            toggleInputStatus(true);

            // Tampilkan grup yang sesuai
            if (selectedKebutuhan === 'Isi Ulang') {
                isiUlangGroup.show();
            } else if (selectedKebutuhan === 'Ganti Komponen') {
                gantiKomponenGroup.show();
                populateItemCheckDropdown();
            }

            getHargaData();
        }

        // Fungsi untuk mengisi dropdown "Jenis Komponen"
        function populateItemCheckDropdown() {
            const vendorId = parseInt($('#vendor_id').val());
            const kebutuhanId = parseInt(kebutuhanSelect.val());
            
            itemCheckSelect.empty().append('<option value="">Pilih Jenis Komponen</option>');

            if (!isNaN(vendorId) && !isNaN(kebutuhanId)) {
                const availableItems = hargaKebutuhans.filter(harga => 
                    parseInt(harga.vendor_id) === vendorId && 
                    parseInt(harga.kebutuhan_id) === kebutuhanId &&
                    harga.item_check_id !== null
                );
                
                const uniqueItemCheckIds = [...new Set(availableItems.map(item => item.item_check_id))];
                
                const filteredItemChecks = itemChecks.filter(itemCheck => 
                    uniqueItemCheckIds.includes(itemCheck.id)
                );
                
                if (filteredItemChecks.length > 0) {
                    filteredItemChecks.forEach(item => {
                        itemCheckSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                    itemCheckSelect.prop('disabled', false);
                } else {
                    itemCheckSelect.append('<option value="" disabled>Tidak ada komponen yang tersedia untuk vendor ini</option>');
                    itemCheckSelect.prop('disabled', true);
                }
            } else {
                itemCheckSelect.prop('disabled', true);
            }

            // Set nilai awal jika sedang mengedit
            if (currentTransaksi && currentTransaksi.harga_kebutuhan && currentTransaksi.harga_kebutuhan.item_check_id) {
                 itemCheckSelect.val(currentTransaksi.harga_kebutuhan.item_check_id);
            }
        }

        // Fungsi untuk mendapatkan data biaya
        function getHargaData() {
            const vendorId = parseInt($('#vendor_id').val());
            const kebutuhanId = parseInt(kebutuhanSelect.val());
            const selectedKebutuhan = kebutuhanSelect.find('option:selected').text().trim();
            const masterAparId = masterAparSelect.val();
            const itemCheckId = parseInt(itemCheckSelect.val());
            const jenisPemadamId = parseInt(jenisPemadamSelect.val());
            const jenisIsiId = parseInt(jenisIsiSelect.val());

            biayaInput.val('').attr('placeholder', 'Memuat biaya...');
            hargaIdInput.val('');
            
            let foundPrice = null;
            let finalBiaya = null;

            if (!isNaN(vendorId) && !isNaN(kebutuhanId)) {
                if (selectedKebutuhan === 'Isi Ulang') {
                    if (masterAparId) {
                        const selectedApar = masterApars.find(apar => apar.id == masterAparId);
                        if (selectedApar) {
                            foundPrice = hargaKebutuhans.find(harga => 
                                parseInt(harga.vendor_id) === vendorId && 
                                parseInt(harga.kebutuhan_id) === kebutuhanId && 
                                parseInt(harga.jenis_pemadam_id) === parseInt(selectedApar.jenis_pemadam_id) &&
                                parseInt(harga.jenis_isi_id) === parseInt(selectedApar.jenis_isi_id)
                            );
                            if (foundPrice) {
                                finalBiaya = foundPrice.biaya * selectedApar.ukuran;
                            }
                        }
                    } else if (!isNaN(jenisPemadamId) && !isNaN(jenisIsiId)) {
                        foundPrice = hargaKebutuhans.find(harga => 
                            parseInt(harga.vendor_id) === vendorId && 
                            parseInt(harga.kebutuhan_id) === kebutuhanId && 
                            parseInt(harga.jenis_pemadam_id) === jenisPemadamId &&
                            parseInt(harga.jenis_isi_id) === jenisIsiId
                        );
                        if (foundPrice) {
                            finalBiaya = foundPrice.biaya;
                        }
                    } else {
                        biayaInput.val('').attr('placeholder', 'Pilih Jenis Pemadam dan Jenis Isi');
                        return;
                    }
                } else if (selectedKebutuhan === 'Ganti Komponen') {
                    if (!isNaN(itemCheckId)) {
                        foundPrice = hargaKebutuhans.find(harga =>
                            parseInt(harga.vendor_id) === vendorId &&
                            parseInt(harga.kebutuhan_id) === kebutuhanId &&
                            parseInt(harga.item_check_id) === itemCheckId
                        );
                        if (foundPrice) {
                            finalBiaya = foundPrice.biaya;
                        }
                    } else {
                        biayaInput.val('').attr('placeholder', 'Pilih Jenis Komponen');
                        return;
                    }
                }
            } else {
                biayaInput.val('').attr('placeholder', 'Pilih Vendor dan Kebutuhan');
                return;
            }

            if (foundPrice) {
                const formattedBiaya = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(finalBiaya);
                biayaInput.val(formattedBiaya);
                hargaIdInput.val(foundPrice.id);
            } else {
                biayaInput.val('').attr('placeholder', 'Harga tidak ditemukan untuk kombinasi ini.');
                hargaIdInput.val('');
            }
        }

        // Event listeners
        $('#vendor_id, #kebutuhan_id').change(function() {
            toggleKebutuhanFields();
            getHargaData();
        });
        
        $('#jenis_pemadam_id, #jenis_isi_id, #item_check_id').change(function() {
            getHargaData();
        });

        $('#master_apar_id').change(function() {
            const selectedAparId = $(this).val();
            const selectedKebutuhan = kebutuhanSelect.find('option:selected').text().trim();
            
            if (selectedKebutuhan === 'Isi Ulang' && selectedAparId) {
                const selectedApar = masterApars.find(apar => apar.id == selectedAparId);
                if (selectedApar) {
                    jenisPemadamSelect.val(selectedApar.jenis_pemadam_id);
                    jenisIsiSelect.val(selectedApar.jenis_isi_id);
                    toggleInputStatus(false);
                }
            } else {
                toggleInputStatus(true);
            }
            getHargaData();
        });

        // Panggil saat halaman dimuat untuk mengatur tampilan awal
        toggleKebutuhanFields();
        getHargaData();
    });
</script>
@endpush
