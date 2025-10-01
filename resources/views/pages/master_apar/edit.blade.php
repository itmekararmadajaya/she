@extends('layouts.app')

@section('title', 'Edit APAR')

@section('content')

<div class="card">
<div class="card-header">
<div class="d-flex justify-content-between align-items-center">
<h1 class="h3 mb-0">Edit APAR</h1>
</div>
</div>
<div class="card-body">
<form method="POST" action="{{ route('master-apar.update', $masterApar) }}">
@csrf
@method('PUT')
<div class="row">
<!-- Kode APAR -->
<div class="col-md-3 mb-3">
<div class="form-group">
<label for="kode">Kode</label>
<input type="text"
name="kode"
id="kode"
class="form-control @error('kode') is-invalid @enderror"
value="{{ old('kode', $masterApar->kode) }}"
placeholder="Kode APAR">
@error('kode')
<div class="invalid-feedback">{{ $message }}</div>
@enderror
</div>
</div>

            <!-- Gedung -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="gedung_id">Gedung</label>
                    <select name="gedung_id" 
                            class="form-control @error('gedung_id') is-invalid @enderror"
                            required>
                        <option value="">Pilih Gedung</option>
                        @foreach ($gedungs as $gedung)
                            <option value="{{ $gedung->id }}" 
                                    {{ $masterApar->gedung_id == $gedung->id ? 'selected' : '' }}>
                                {{ $gedung->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('gedung_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Lokasi -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="lokasi">Lokasi</label>
                    <input type="text" 
                           name="lokasi" 
                           id="lokasi" 
                           class="form-control @error('lokasi') is-invalid @enderror"
                           value="{{ old('lokasi', $masterApar->lokasi) }}" 
                           placeholder="Lokasi APAR">
                    @error('lokasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Jenis Isi -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="jenis_isi_id">Jenis Isi</label>
                    <select name="jenis_isi_id" 
                            class="form-control @error('jenis_isi_id') is-invalid @enderror"
                            required>
                        <option value="">Pilih Jenis Isi</option>
                        @foreach ($jenisIsis as $jenisIsi)
                            <option value="{{ $jenisIsi->id }}" {{ $masterApar->jenis_isi_id == $jenisIsi->id ? 'selected' : '' }}>
                                {{ $jenisIsi->jenis_isi }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_isi_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ukuran -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="ukuran">Ukuran</label>
                    <input type="number" 
                           name="ukuran" 
                           id="ukuran" 
                           class="form-control @error('ukuran') is-invalid @enderror"
                           value="{{ old('ukuran', $masterApar->ukuran) }}" 
                           placeholder="Ukuran APAR">
                    @error('ukuran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Satuan -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="satuan">Satuan</label>
                    <select name="satuan" 
                            class="form-control @error('satuan') is-invalid @enderror"
                            required>
                        <option value="">Pilih satuan</option>
                        <option value="KG" {{ $masterApar->satuan == 'KG' ? 'selected' : '' }}>KG</option>
                        <option value="L" {{ $masterApar->satuan == 'L' ? 'selected' : '' }}>L</option>
                    </select>
                    @error('satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Tgl Kadaluarsa -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="tgl_kadaluarsa">Tgl Kadaluarsa</label>
                    <input type="date" 
                           name="tgl_kadaluarsa" 
                           id="tgl_kadaluarsa" 
                           class="form-control @error('tgl_kadaluarsa') is-invalid @enderror"
                           value="{{ old('tgl_kadaluarsa', $masterApar->tgl_kadaluarsa) }}" 
                           placeholder="Tgl kadaluarsa APAR">
                    @error('tgl_kadaluarsa')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Jenis Pemadam -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="jenis_pemadam_id">Jenis Pemadam</label>
                    <select name="jenis_pemadam_id" 
                            class="form-control @error('jenis_pemadam_id') is-invalid @enderror"
                            required>
                        <option value="">Pilih Jenis Pemadam</option>
                        @foreach ($jenisPemadams as $jenisPemadam)
                            <option value="{{ $jenisPemadam->id }}" {{ $masterApar->jenis_pemadam_id == $jenisPemadam->id ? 'selected' : '' }}>
                                {{ $jenisPemadam->jenis_pemadam }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_pemadam_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Catatan -->
            <div class="col-md-3 mb-3">
                <div class="form-group">
                    <label for="catatan">Catatan (Tidak Wajib)</label>
                    <input type="text" 
                           name="catatan" 
                           id="catatan" 
                           class="form-control @error('catatan') is-invalid @enderror"
                           value="{{ old('catatan', $masterApar->catatan) }}" 
                           placeholder="Catatan">
                    @error('catatan')
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