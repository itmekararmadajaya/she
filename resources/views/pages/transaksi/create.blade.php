@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4 card-custom-rounded">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Tambah Transaksi Baru</h1>
        </div>
        <div class="card-body">
            <form id="transaksiForm" action="{{ route('transaksi.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="vendor_id" class="form-label">Vendor</label>
                    <select class="form-control" id="vendor_id" name="vendor_id" required>
                        <option value="">Pilih Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->nama_vendor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kebutuhan_id" class="form-label">Kebutuhan</label>
                    <select class="form-control" id="kebutuhan_id" name="kebutuhan_id" required>
                        <option value="">Pilih Kebutuhan</option>
                        @foreach($kebutuhans as $kebutuhan)
                            @if($kebutuhan->kebutuhan !== 'Beli Baru')
                                <option value="{{ $kebutuhan->id }}">{{ $kebutuhan->kebutuhan }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="aparGroup">
                    <label for="master_apar_id" class="form-label">APAR (Opsional)</label>
                    <select class="form-control" id="master_apar_id" name="master_apar_id">
                        <option value="">Pilih APAR</option>
                        @foreach($masterApars as $apar)
                            <option value="{{ $apar->id }}">{{ $apar->kode }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="isiUlangGroup" style="display:none;">
                    <div class="mb-3">
                        <label for="jenis_pemadam_id" class="form-label">Jenis Pemadam</label>
                        <select class="form-control" id="jenis_pemadam_id" name="jenis_pemadam_id">
                            <option value="">Pilih Jenis Pemadam</option>
                            @foreach($jenisPemadams as $jp)
                                <option value="{{ $jp->id }}">{{ $jp->jenis_pemadam }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_isi_id" class="form-label">Jenis Isi</label>
                        <select class="form-control" id="jenis_isi_id" name="jenis_isi_id">
                            <option value="">Pilih Jenis Isi</option>
                            @foreach($jenisIsis as $ji)
                                <option value="{{ $ji->id }}">{{ $ji->jenis_isi }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="gantiKomponenGroup" style="display:none;">
                    <div class="mb-3">
                        <label for="item_check_id" class="form-label">Jenis Komponen</label>
                        <select class="form-control" id="item_check_id" name="item_check_id">
                            <option value="">Pilih Jenis Komponen</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" id="biaya_id" name="biaya_id">

                <div class="mb-3">
                    <label for="biaya_display" class="form-label">Biaya</label>
                    <input type="text" class="form-control" id="biaya_display" readonly placeholder="Pilih Vendor dan Kebutuhan">
                </div>
                <input type="hidden" id="hidden_biaya" name="biaya">

                <div class="mb-3">
                    <label for="tanggal_pembelian" class="form-label">Tanggal Pembelian</label>
                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" required>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
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
        const masterApars = @json($masterApars);
        const itemChecks = @json($itemChecks);
        const hargaKebutuhans = @json($hargaKebutuhans);
        const isiUlangGroup = $('#isiUlangGroup');
        const gantiKomponenGroup = $('#gantiKomponenGroup');
        const jenisPemadamSelect = $('#jenis_pemadam_id');
        const jenisIsiSelect = $('#jenis_isi_id');
        const masterAparSelect = $('#master_apar_id');
        const kebutuhanSelect = $('#kebutuhan_id');
        const biayaDisplayInput = $('#biaya_display');
        const biayaIdInput = $('#biaya_id');
        const hiddenBiayaInput = $('#hidden_biaya');
        const itemCheckSelect = $('#item_check_id');

        function toggleInputStatus(isEnabled) {
            jenisPemadamSelect.prop('disabled', !isEnabled);
            jenisIsiSelect.prop('disabled', !isEnabled);
        }

        function toggleKebutuhanFields() {
            const selectedKebutuhan = kebutuhanSelect.find('option:selected').text().trim();
            
            isiUlangGroup.hide();
            gantiKomponenGroup.hide();
            masterAparSelect.prop('disabled', false);
            toggleInputStatus(true);
            masterAparSelect.val('');
            jenisPemadamSelect.val('');
            jenisIsiSelect.val('');
            itemCheckSelect.val('');
            itemCheckSelect.empty().append('<option value="">Pilih Jenis Komponen</option>');

            if (selectedKebutuhan === 'Isi Ulang') {
                isiUlangGroup.show();
            } else if (selectedKebutuhan === 'Ganti Komponen') {
                gantiKomponenGroup.show();
                populateItemCheckDropdown();
            }

            getHargaData();
        }

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
        }

        function getHargaData() {
            const vendorId = parseInt($('#vendor_id').val());
            const kebutuhanId = parseInt(kebutuhanSelect.val());
            const selectedKebutuhan = kebutuhanSelect.find('option:selected').text().trim();
            const masterAparId = masterAparSelect.val();
            const itemCheckId = parseInt(itemCheckSelect.val());

            biayaDisplayInput.val('').attr('placeholder', 'Memuat biaya...');
            biayaIdInput.val('');
            hiddenBiayaInput.val('');

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
                    } else {
                        const jenisPemadamId = parseInt(jenisPemadamSelect.val());
                        const jenisIsiId = parseInt(jenisIsiSelect.val());
                        if (!isNaN(jenisPemadamId) && !isNaN(jenisIsiId)) {
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
                            biayaDisplayInput.val('').attr('placeholder', 'Pilih Jenis Pemadam dan Jenis Isi');
                            return;
                        }
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
                        biayaDisplayInput.val('').attr('placeholder', 'Pilih Jenis Komponen');
                        return;
                    }
                }
            } else {
                biayaDisplayInput.val('').attr('placeholder', 'Pilih Vendor dan Kebutuhan');
                return;
            }

            if (foundPrice) {
                const formattedBiaya = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(finalBiaya);
                biayaDisplayInput.val(formattedBiaya);
                hiddenBiayaInput.val(finalBiaya);
                biayaIdInput.val(foundPrice.id);
            } else {
                biayaDisplayInput.val('').attr('placeholder', 'Harga tidak ditemukan untuk kombinasi ini.');
                hiddenBiayaInput.val('');
                biayaIdInput.val('');
            }
        }

        kebutuhanSelect.change(function() {
            toggleKebutuhanFields();
        });

        masterAparSelect.change(function() {
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

        $('#vendor_id').change(function() {
            toggleKebutuhanFields();
        });

        $('#jenis_pemadam_id, #jenis_isi_id, #item_check_id').change(function() {
            getHargaData();
        });

        toggleKebutuhanFields();
    });
</script>
@endpush