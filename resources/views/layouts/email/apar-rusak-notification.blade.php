<h2 style="font-family: sans-serif; color: #2c3e50;">Daftar APAR yang Rusak</h2>

<table cellpadding="8" cellspacing="0" border="1" style="width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 14px; color: #333; background-color: #ffffff;">
    <thead style="background-color: #e2e3e5;">
        <tr>
            <th style="width: 5%; text-align: center;">No</th>
            <th style="text-align: left;">Kode</th>
            <th style="text-align: left;">Gedung</th>
            <th style="text-align: left;">Lokasi</th>
            <th style="text-align: center;">Tgl Inspeksi</th>
            <th style="text-align: left;">User</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($aparRusak as $i => $apar)
            <tr style="background-color: {{ $i % 2 == 0 ? '#f8f9fa' : '#ffffff' }};">
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $apar->masterApar->kode }}</td>
                <td>{{ $apar->masterApar->gedung->nama }}</td>
                <td>{{ $apar->masterApar->lokasi }}</td>
                <td style="text-align: center;">{{ $apar->dateFormatted }}</td>
                <td>{{ $apar->user->name }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; font-style: italic; background-color: #fff3cd;">
                    Tidak ada data inspeksi APAR
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
