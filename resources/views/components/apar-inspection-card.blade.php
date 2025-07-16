<style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px 5px;
        }

        thead {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        td:first-child {
            text-align: left;
            font-weight: bold;
        }
    </style>
    
<div class="card">
            <div class="card-body">
                <div class="text-center">
                    <h3>Kartu Pemeriksaan APAR</h3>
                </div>
                <table style="text-align: left;">
                    <tr>
                        <td style="width: 10%">Tahun</td>
                        <td style="width: 90%">{{$year}}</td>
                    </tr>
                    <tr>
                        <td>Kode</td>
                        <td>{{$apar->kode}}</td>
                    </tr>
                    <tr>
                        <td>Jenis</td>
                        <td>{{$apar->jenis_isi}}</td>
                    </tr>
                    <tr>
                        <td>Ukuran</td>
                        <td>{{$apar->ukuran}} {{$apar->satuan}}</td>
                    </tr>
                    <tr>
                        <td>Lokasi</td>
                        <td>{{$apar->gedung->nama}} - {{$apar->lokasi}}</td>
                    </tr>
                </table>
                <div class="table-responsive">
                <table class="text-sm text-center">
                    <thead>
                        <tr>
                            <td style="text-align: center">Item Check</td>
                            <td style="text-align: center">1</td>
                            <td style="text-align: center">2</td>
                            <td style="text-align: center">3</td>
                            <td style="text-align: center">4</td>
                            <td style="text-align: center">5</td>
                            <td style="text-align: center">6</td>
                            <td style="text-align: center">7</td>
                            <td style="text-align: center">8</td>
                            <td style="text-align: center">9</td>
                            <td style="text-align: center">10</td>
                            <td style="text-align: center">11</td>
                            <td style="text-align: center">12</td>
                            <td>Remarks</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($finalData as $data)
                            <tr>
                                <td><div class="text-xs text-nowrap">{{$data['item_check']}}</div></td>
                                @foreach ($data['data'] as $monthly)
                                    <td class="text-xs text-nowrap">{{$monthly['value']}}</td>
                                @endforeach
                                <td class="text-xs">
                                    {{ collect($data['data'])->firstWhere('bulan', now()->month)['remark'] ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="mt-3 w-50 text-sm">
                    Keterangan
                    <div>
                        <div style="margin-bottom: 4px;">
                            <strong>B</strong> : Baik
                        </div>
                        <div style="margin-bottom: 4px;">
                            <strong>R</strong> : Rusak
                        </div>
                        <div>
                            <strong>T/A</strong> : Tidak Ada
                        </div>
                    </div>
                </div>
            </div>
        </div>