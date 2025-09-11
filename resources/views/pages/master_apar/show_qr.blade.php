<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code APAR - {{ $apar->kode }}</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .container {
            text-align: center;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: #000;
        }
        img {
            max-width: 150px; /* Ukuran QR code lebih kecil */
            height: auto;
            margin-bottom: 25px; /* Jarak bawah gambar */
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .info {
            font-size: 22px; /* Ukuran huruf lebih besar */
            line-height: 1.6;
            text-align: left;
            padding: 0 20px;
            margin-bottom: 10px;
        }
        .info strong {
            font-weight: bold;
            display: inline-block;
            width: 80px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container" style="text-align: center">
        <img src="{{ $qrCodeDataUri }}" alt="QR Code">
        <div class="info" style="text-align: center">
            {{ $apar->kode }}
        </div>
        <div class="info" style="text-align: center">
            {{ $apar->lokasi }}
        </div>
    </div>
</body>
</html>