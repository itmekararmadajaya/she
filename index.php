<?php
    // =========================================================
    // 1. DEFINISI URL DAN IP TARGET
    // =========================================================

    // URL internal yang menjadi prioritas (menggunakan IP lokal)
    $url_internal = "http://102.155.20.100/she_new/login";

    // URL eksternal (failover)
    $url_eksternal = "https://portal.newarmada.com/she_new/login";

    // Rentang IP yang menandakan jaringan internal New Armada
    // Kita cek apakah host yang diakses pengguna diawali dengan IP ini.
    // ASUMSI: Alamat server internal adalah 102.155.20.100, jadi kita cek awalan 102.155.20.
    $internal_ip_prefix = '102.155.20.';

    // =========================================================
    // 2. LOGIKA PENGECEKAN HOST (Prioritas Internal)
    // =========================================================

    // Ambil hostname yang digunakan pengguna untuk mengakses skrip ini (misalnya '102.155.20.100' atau 'portal.newarmada.com')
    $host_yang_diakses = $_SERVER['HTTP_HOST']; 

    // Periksa apakah host yang diakses dimulai dengan awalan IP internal
    $is_internal = str_starts_with($host_yang_diakses, $internal_ip_prefix);

    // Cek tambahan untuk memastikan hostname adalah IP internal secara eksplisit
    $is_internal_host_exact = ($host_yang_diakses === '102.155.20.100');


    // Tentukan URL yang akan digunakan
    if ($is_internal || $is_internal_host_exact) {
        // KONDISI 1: Prioritas Internal
        // Jika pengguna mengakses menggunakan IP internal atau nama host internal (jika ada)
        $redirect_url = $url_internal;
    } else {
        // KONDISI 2: Failover Eksternal
        // Jika pengguna mengakses menggunakan hostname publik (portal.newarmada.com)
        // atau alamat yang tidak dikenal (berarti di luar jaringan).
        $redirect_url = $url_eksternal;
    }

    // =========================================================
    // 3. REDIRECTION
    // =========================================================
    header("Location: " . $redirect_url);
    exit();

?>