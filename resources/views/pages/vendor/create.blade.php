@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow mb-4 card-custom-rounded">
        <div class="card-header py-3">
            <h1 class="h3 mb-0 text-gray-800">Tambah Vendor</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('vendor.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label for="nama_vendor" class="form-label">Nama Vendor</label>
                    <input type="text" name="nama_vendor" id="nama_vendor" class="form-control @error('nama_vendor') is-invalid @enderror" value="{{ old('nama_vendor') }}" required>
                    @error('nama_vendor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-2">
                    <label for="kontak" class="form-label">Kontak (Opsional)</label>
                    <input type="text" name="kontak" id="kontak" class="form-control" value="{{ old('kontak') }}">
                </div>
                
                <hr>

                <h4 class="h5 mt-3 mb-2 text-gray-800">Harga Kebutuhan</h4>
                
                <div id="kebutuhan-container">
                    {{-- Blok kebutuhan dinamis akan ditambahkan di sini oleh JavaScript --}}
                </div>
                
                <button type="button" class="btn btn-secondary mt-2" onclick="addKebutuhanBlock()">Tambah Kebutuhan</button>
                
                <hr class="my-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
    // Data dari controller
    const jenisPemadams = @json($jenisPemadams);
    const jenisIsis = @json($jenisIsis);
    const itemChecks = @json($itemChecks);
    const kebutuhans = @json($kebutuhans);
    const existingData = @json([]);

    let kebutuhanBlockCount = 0;

    // Fungsi untuk menambah baris "Beli Baru" atau "Isi Ulang"
    function addJenisIsiRow(container, data = {}) {
        const blockIndex = container.closest('.kebutuhan-block').dataset.index;
        const isiIndex = container.children.length;

        let isiOptions = '';
        jenisIsis.forEach(isi => {
            const isSelected = data.jenis_isi_id == isi.id ? 'selected' : '';
            isiOptions += `<option value="${isi.id}" ${isSelected}>${isi.jenis_isi}</option>`;
        });

        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-2', 'align-items-center', 'jenis-isi-row');
        newRow.innerHTML = `
            ${data.id ? `<input type="hidden" name="kebutuhan[${blockIndex}][jenis_isi][${isiIndex}][id]" value="${data.id}">` : ''}
            <div class="col-md-4">
                <label class="form-label">Jenis Isi</label>
                <select class="form-control form-control-sm" name="kebutuhan[${blockIndex}][jenis_isi][${isiIndex}][jenis_isi_id]" required>
                    ${isiOptions}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya</label>
                <input type="number" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][jenis_isi][${isiIndex}][biaya]" value="${data.biaya || ''}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Perubahan</label>
                <input type="date" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][jenis_isi][${isiIndex}][tanggal_perubahan]" value="${data.tanggal_perubahan || new Date().toISOString().slice(0, 10)}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeJenisIsiRow(this)">Hapus</button>
            </div>
        `;
        container.appendChild(newRow);
    }

    // Fungsi untuk menambah baris "Ganti Komponen"
    function addJenisKomponenRow(container, data = {}) {
        const blockIndex = container.closest('.kebutuhan-block').dataset.index;
        const komponenIndex = container.children.length;

        let komponenOptions = '';
        itemChecks.forEach(komponen => {
            const isSelected = data.item_check_id == komponen.id ? 'selected' : '';
            komponenOptions += `<option value="${komponen.id}" ${isSelected}>${komponen.name}</option>`;
        });

        const newRow = document.createElement('div');
        newRow.classList.add('row', 'mb-2', 'align-items-center', 'jenis-komponen-row');
        newRow.innerHTML = `
            ${data.id ? `<input type="hidden" name="kebutuhan[${blockIndex}][komponen][${komponenIndex}][id]" value="${data.id}">` : ''}
            <div class="col-md-4">
                <label class="form-label">Jenis Komponen</label>
                <select class="form-control form-control-sm" name="kebutuhan[${blockIndex}][komponen][${komponenIndex}][item_check_id]" required>
                    ${komponenOptions}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Biaya</label>
                <input type="number" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][komponen][${komponenIndex}][biaya]" value="${data.biaya || ''}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Perubahan</label>
                <input type="date" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][komponen][${komponenIndex}][tanggal_perubahan]" value="${data.tanggal_perubahan || new Date().toISOString().slice(0, 10)}" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeJenisKomponenRow(this)">Hapus</button>
            </div>
        `;
        container.appendChild(newRow);
    }

    // Fungsi untuk menambah blok kebutuhan baru
    function addKebutuhanBlock(data = {}) {
        const container = document.getElementById('kebutuhan-container');
        const block = document.createElement('div');
        block.classList.add('card', 'p-4', 'mb-3', 'kebutuhan-block');
        block.dataset.index = kebutuhanBlockCount;
        
        let kebutuhanOptions = '';
        kebutuhans.forEach(kebutuhan => {
            const isSelected = data.kebutuhan_id == kebutuhan.id ? 'selected' : '';
            kebutuhanOptions += `<option value="${kebutuhan.id}" ${isSelected}>${kebutuhan.kebutuhan}</option>`;
        });
        
        block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Kebutuhan #${kebutuhanBlockCount + 1}</h5>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeKebutuhanBlock(this)">Hapus Kebutuhan</button>
            </div>
            <div class="mb-2">
                <label class="form-label">Jenis Kebutuhan</label>
                <select class="form-control form-control-sm kebutuhan-select" name="kebutuhan[${kebutuhanBlockCount}][kebutuhan_id]" onchange="updateSubForm(this)">
                    <option value="">-- Pilih Jenis Kebutuhan --</option>
                    ${kebutuhanOptions}
                </select>
            </div>
            <div class="sub-form-container">
                {{-- Sub-form akan diisi oleh JavaScript --}}
            </div>
        `;
        
        container.appendChild(block);

        // Muat sub-form yang benar berdasarkan data yang ada
        if (data.kebutuhan_id) {
            updateSubForm(block.querySelector('.kebutuhan-select'), data);
        }

        kebutuhanBlockCount++;
    }

    // Fungsi untuk mengubah sub-form berdasarkan pilihan kebutuhan
    function updateSubForm(selectElement, existingData = null) {
        const block = selectElement.closest('.kebutuhan-block');
        const subFormContainer = block.querySelector('.sub-form-container');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const kebutuhanName = selectedOption.textContent;
        const blockIndex = block.dataset.index;

        subFormContainer.innerHTML = '';

        if (kebutuhanName === 'Beli Baru' || kebutuhanName === 'Isi Ulang') {
            let pemadamOptions = '';
            jenisPemadams.forEach(pemadam => {
                const isSelected = existingData?.jenis_pemadam_id == pemadam.id ? 'selected' : '';
                pemadamOptions += `<option value="${pemadam.id}" ${isSelected}>${pemadam.jenis_pemadam}</option>`;
            });

            subFormContainer.innerHTML = `
                <div class="mb-2">
                    <label class="form-label">Jenis Pemadam</label>
                    <select class="form-control form-control-sm" name="kebutuhan[${blockIndex}][jenis_pemadam_id]" required>
                        ${pemadamOptions}
                    </select>
                </div>
                <h6 class="mb-2">Harga per Jenis Isi</h6>
                <div class="card p-3 mb-2">
                    <div id="jenis-isi-container-${blockIndex}" class="jenis-isi-container"></div>
                </div>
                <button type="button" class="btn btn-secondary mt-2" onclick="addJenisIsiRow(document.getElementById('jenis-isi-container-${blockIndex}'))">Tambah Jenis Isi</button>
            `;
            if (existingData && existingData.jenis_isi && existingData.jenis_isi.length > 0) {
                existingData.jenis_isi.forEach(isi => addJenisIsiRow(subFormContainer.querySelector(`#jenis-isi-container-${blockIndex}`), isi));
            } else {
                addJenisIsiRow(subFormContainer.querySelector(`#jenis-isi-container-${blockIndex}`));
            }
        } else if (kebutuhanName === 'Ganti Komponen') {
            subFormContainer.innerHTML = `
                <h6 class="mb-2">Harga per Jenis Komponen</h6>
                <div class="card p-3 mb-2">
                    <div id="jenis-komponen-container-${blockIndex}" class="jenis-komponen-container"></div>
                </div>
                <button type="button" class="btn btn-secondary mt-2" onclick="addJenisKomponenRow(document.getElementById('jenis-komponen-container-${blockIndex}'))">Tambah Komponen</button>
            `;
            if (existingData && existingData.komponen && existingData.komponen.length > 0) {
                existingData.komponen.forEach(komponen => addJenisKomponenRow(subFormContainer.querySelector(`#jenis-komponen-container-${blockIndex}`), komponen));
            } else {
                addJenisKomponenRow(subFormContainer.querySelector(`#jenis-komponen-container-${blockIndex}`));
            }
        } else {
            subFormContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Biaya</label>
                        <input type="number" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][biaya]" value="${existingData?.biaya || ''}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Perubahan</label>
                        <input type="date" class="form-control form-control-sm" name="kebutuhan[${blockIndex}][tanggal_perubahan]" value="${existingData?.tanggal_perubahan || new Date().toISOString().slice(0, 10)}" required>
                    </div>
                </div>
            `;
        }
    }

    // Hapus blok kebutuhan
    function removeKebutuhanBlock(button) {
        button.closest('.kebutuhan-block').remove();
    }
    
    // Hapus baris jenis isi
    function removeJenisIsiRow(button) {
        button.closest('.jenis-isi-row').remove();
    }

    function removeJenisKomponenRow(button) {
        button.closest('.jenis-komponen-row').remove();
    }

    // Muat data yang sudah ada saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        if (existingData && existingData.length > 0) {
            existingData.forEach(data => {
                addKebutuhanBlock(data);
            });
        } else {
            addKebutuhanBlock();
        }
    });
</script>
@endsection
