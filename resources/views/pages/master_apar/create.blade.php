@extends('layouts.app')

@section('title', 'Tambah APAR')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Tambah APAR</h1>
            </div>
        </div>
@if($errors->any())
    <div class="mb-4 p3 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
        <div class="card-body">
            <form method="POST" action="{{ route('master-apar.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="kode">Kode</label>
                            <input type="text" 
                                   name="kode" 
                                   id="kode" 
                                   class="form-control @error('kode') is-invalid @enderror"
                                   value="{{ old('kode') }}" 
                                   placeholder="Kode APAR" maxlength="4">
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="gedung_id">Area</label>
                            <select name="gedung_id" 
                                    class="form-control @error('gedung_id') is-invalid @enderror">
                                <option value="">Pilih Area</option>
                                @foreach ($gedungs as $gedung)
                                    <option value="{{ $gedung->id }}" 
                                            {{ old('gedung_id') == $gedung->id ? 'selected' : '' }}>
                                        {{ $gedung->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gedung_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <input type="text" 
                                   name="lokasi" 
                                   id="lokasi" 
                                   class="form-control @error('lokasi') is-invalid @enderror"
                                   value="{{ old('lokasi') }}" 
                                   placeholder="Lokasi APAR">
                            @error('lokasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="jenis_isi_id">Jenis Isi</label>
                            <select name="jenis_isi_id" 
                                    id="jenis_isi_id"
                                    class="form-control @error('jenis_isi_id') is-invalid @enderror">
                                <option value="">Pilih jenis isi</option>
                                @foreach ($jenisIsis as $jenisIsi)
                                    <option value="{{ $jenisIsi->id }}"
                                        {{ old('jenis_isi_id') == $jenisIsi->id ? 'selected' : '' }}>
                                        {{ $jenisIsi->jenis_isi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_isi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="ukuran">Ukuran</label>
                            <input type="number" 
                                   name="ukuran" 
                                   id="ukuran" 
                                   class="form-control @error('ukuran') is-invalid @enderror"
                                   value="{{ old('ukuran') }}" 
                                   placeholder="Ukuran APAR">
                            @error('ukuran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="satuan">Satuan</label>
                            <select name="satuan" 
                                    class="form-control @error('satuan') is-invalid @enderror">
                                <option value="">Pilih satuan</option>
                                <option value="KG" {{ old('satuan') == 'KG' ? 'selected' : '' }}>KG</option>
                                <option value="L" {{ old('satuan') == 'L' ? 'selected' : '' }}>L</option>
                            </select>
                            @error('satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="tgl_kadaluarsa">Tgl Kadaluarsa</label>
                            <input type="date" 
                                   name="tgl_kadaluarsa" 
                                   id="tgl_kadaluarsa" 
                                   class="form-control @error('tgl_kadaluarsa') is-invalid @enderror"
                                   value="{{ old('tgl_kadaluarsa') }}" 
                                   placeholder="Tgl kadaluarsa APAR">
                            @error('tgl_kadaluarsa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="jenis_pemadam_id">Jenis Pemadam</label>
                            <select name="jenis_pemadam_id" 
                                    id="jenis_pemadam_id"
                                    class="form-control @error('jenis_pemadam_id') is-invalid @enderror">
                                <option value="">Pilih jenis pemadam</option>
                                @foreach ($jenisPemadams as $jenisPemadam)
                                    <option value="{{ $jenisPemadam->id }}"
                                        {{ old('jenis_pemadam_id') == $jenisPemadam->id ? 'selected' : '' }}>
                                        {{ $jenisPemadam->jenis_pemadam }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_pemadam_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="catatan">Catatan (tidak wajib)</label>
                            <input type="text" 
                                   name="catatan" 
                                   id="catatan" 
                                   class="form-control @error('catatan') is-invalid @enderror"
                                   value="{{ old('catatan') }}" 
                                   placeholder="catatan">
                            @error('catatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- MENAMBAHKAN CHECKBOX UNTUK APAR BARU --}}
                    <div class="col-md-3 mb-3">
                        <div class="form-check mt-5">
                            <input class="form-check-input" type="checkbox" id="apar_baru_checkbox" name="is_new_apar" value="1">
                            <label class="form-check-label" for="apar_baru_checkbox">
                                APAR BARU
                            </label>
                        </div>
                    </div>

                </div> {{-- END OF ROW --}}

                <div class="row" id="apar_baru_form_fields" style="display: none;">
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="vendor_id">Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control @error('vendor_id') is-invalid @enderror">
                                <option value="">Pilih Vendor</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->nama_vendor }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="form-group">
                            <label for="tanggal_pembelian">Tanggal Pembelian</label>
                            <input type="date" name="tanggal_pembelian" id="tanggal_pembelian" class="form-control @error('tanggal_pembelian') is-invalid @enderror"
                                value="{{ old('tanggal_pembelian') }}">
                            @error('tanggal_pembelian')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('master-apar.index') }}" class="btn btn-light">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('apar_baru_checkbox');
        const formFields = document.getElementById('apar_baru_form_fields');

        // Check if the old input for the checkbox exists on page load
        @if(old('is_new_apar'))
            checkbox.checked = true;
            formFields.style.display = 'flex';
        @endif

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                formFields.style.display = 'flex';
            } else {
                formFields.style.display = 'none';
            }
        });
    });
</script>
@endpush