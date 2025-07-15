@extends('layouts.app')

@section('title', 'Edit APAR')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0 h3">Edit APAR</h1>
        </div>
    </div>
    <div class="card-body">
        <div>
            <form method="POST" action="{{route('master-apar.update', $masterApar)}}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Kode</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                            id="kode" value="{{ $masterApar->kode }}" placeholder="Kode APAR">
                        @error('kode')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="role">Gedung</label>
                        <select name="gedung_id" class="form-control @error('gedung_id') is-invalid @enderror">
                            @foreach ($gedungs as $gedung)
                            <option value="{{ $gedung->id }}" {{ $masterApar->gedung_id == $gedung->id ? 'selected' : '' }}>
                                {{ $gedung->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('gedung_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                            id="lokasi" value="{{ $masterApar->lokasi }}" placeholder="Lokasi APAR">
                        @error('lokasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="role">Jenis Isi</label>
                        <select name="jenis_isi" class="form-control @error('jenis_isi') is-invalid @enderror">
                            <option value="">Pilih jenis isi</option>
                            <option value="Powder" {{ $masterApar->jenis_isi == 'Powder' ? 'selected' : '' }}>Powder</option>
                            <option value="CO2" {{ $masterApar->jenis_isi == 'CO2' ? 'selected' : '' }}>CO2</option>
                            <option value="Foam" {{ $masterApar->jenis_isi == 'Foam' ? 'selected' : '' }}>Foam</option>
                            <option value="HCFC" {{ $masterApar->jenis_isi == 'HCFC' ? 'selected' : '' }}>HCFC</option>
                        </select>
                        @error('jenis_isi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Ukuran</label>
                        <input type="number" name="ukuran" class="form-control @error('ukuran') is-invalid @enderror"
                            id="ukuran" value="{{ $masterApar->ukuran }}" placeholder="Ukuran APAR">
                        @error('ukuran')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Satuan</label>
                        <select name="satuan" class="form-control @error('satuan') is-invalid @enderror">
                            <option value="">Pilih satuan</option>
                            <option value="KG" {{ $masterApar->satuan == 'KG' ? 'selected' : '' }}>KG</option>
                        </select>
                        @error('satuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Tgl Kadaluarsa</label>
                        <input type="date" name="tgl_kadaluarsa" class="form-control @error('tgl_kadaluarsa') is-invalid @enderror"
                            id="tgl_kadaluarsa" value="{{ $masterApar->tgl_kadaluarsa }}" placeholder="Tgl kadaluarsa APAR">
                        @error('tgl_kadaluarsa')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="role">Jenis Pemadam</label>
                        <select name="jenis_pemadam" class="form-control @error('jenis_pemadam') is-invalid @enderror">
                            <option value="">Pilih jenis pemadam</option>
                            <option value="APAR" {{ $masterApar->jenis_pemadam == 'APAR' ? 'selected' : '' }}>APAR</option>
                            <option value="EXP" {{ $masterApar->jenis_pemadam == 'EXP' ? 'selected' : '' }}>EXP</option>
                            <option value="APAB" {{ $masterApar->jenis_pemadam == 'APAB' ? 'selected' : '' }}>APAB</option>
                            <option value="ADA" {{ $masterApar->jenis_pemadam == 'ADA' ? 'selected' : '' }}>ADA</option>
                        </select>
                        @error('jenis_pemadam')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="role">Tanda</label>
                        <select name="tanda" class="form-control @error('tanda') is-invalid @enderror">
                            <option value="">Pilih tanda</option>
                            <option value="ADA" {{ $masterApar->tanda == 'ADA' ? 'selected' : '' }}>ADA</option>
                            <option value="TIDAK ADA" {{ $masterApar->tanda == 'TIDAK ADA' ? 'selected' : '' }}>TIDAK ADA</option>
                        </select>
                        @error('tanda')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Catatan</label>
                        <input type="text" name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                            id="catatan" value="{{ $masterApar->catatan }}" placeholder="Catatan APAR">
                        @error('catatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Tgl Refill</label>
                        <input type="date" name="tgl_refill" class="form-control @error('tgl_refill') is-invalid @enderror"
                            id="tgl_refill" value="{{ $masterApar->tgl_refill }}" placeholder="Tgl refill APAR">
                        @error('tgl_refill')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3 col-md-3">
                        <label for="kode">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                            id="keterangan" value="{{ $masterApar->keterangan }}" placeholder="Keterangan APAR">
                        @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{route('master-apar.index')}}" class="btn btn-light">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection