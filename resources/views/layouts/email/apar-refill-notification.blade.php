<h2 style="font-family: sans-serif; color: #2c3e50;">Daftar APAR yang Perlu Refill</h2>

<table cellpadding="8" cellspacing="0" border="1" style="width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 14px; color: #333; background-color: #ffffff;">
    <thead style="background-color: #e2e3e5;">
        <tr>
            <th style="width: 5%; text-align: center;">No</th>
            <th style="text-align: left;">Kode</th>
            <th style="text-align: left;">Gedung</th>
            <th style="text-align: left;">Lokasi</th>
            <th style="text-align: center;">Tgl Refill</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($aparPerluRefill as $i => $refill)
            <tr style="background-color: {{ $i % 2 == 0 ? '#f8f9fa' : '#ffffff' }};">
                <td style="text-align: center;">{{ $i + 1 }}</td>
                <td>{{ $refill->kode }}</td>
                <td>{{ $refill->gedung->nama }}</td>
                <td>{{ $refill->lokasi }}</td>
                <td style="text-align: center;">{{ $refill->tglrefillFormatted }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; font-style: italic; background-color: #fff3cd;">Tidak ada APAR yang perlu di-refill</td>
            </tr>
        @endforelse
    </tbody>
</table>
