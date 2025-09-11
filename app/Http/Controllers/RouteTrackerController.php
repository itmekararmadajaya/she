<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteTrackerController extends Controller
{
    public function showRoutes()
    {
        $routeCollection = Route::getRoutes();
        $routes = [];

        foreach ($routeCollection as $route) {
            $file = str_contains((string)$route->getPrefix(), 'api') ? 'api.php' : 'web.php';
            $uri = $route->uri();
            $methods = $route->methods();
            $name = $route->getName() ?? '-';

            $dataTransfer = 'None';
            $keys = '-';
            $function = '-';
            $middlewares = $route->gatherMiddleware();
            $auth = (in_array('auth:sanctum', $middlewares) || in_array('auth', $middlewares)) ? '✅' : '❌';
            $response_example = '-';

            // ==============================
            // 🔎 Lokasi file & baris (robust)
            // ==============================
            $uses = $route->getAction('uses');
            $filePath = base_path("routes/$file"); // default: file routes
            $line = '-';

            try {
                if ($uses instanceof \Closure) {
                    // Biarkan default (route didefinisikan di web.php/api.php)
                } elseif (is_string($uses)) {
                    if (str_contains($uses, '@')) {
                        // Controller@method
                        [$controller, $method] = explode('@', $uses, 2);
                        if (class_exists($controller) && method_exists($controller, $method)) {
                            $ref = new \ReflectionMethod($controller, $method);
                            $filePath = $ref->getFileName();
                            $line = $ref->getStartLine();
                        }
                    } else {
                        // Invokable controller (single __invoke)
                        if (class_exists($uses)) {
                            $refClass = new \ReflectionClass($uses);
                            $filePath = $refClass->getFileName();
                            // Cari __invoke kalau ada
                            if ($refClass->hasMethod('__invoke')) {
                                $refMeth = $refClass->getMethod('__invoke');
                                $line = $refMeth->getStartLine();
                            } else {
                                $line = $refClass->getStartLine();
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fallback aman
                $filePath = $filePath ?: 'Tidak diketahui';
                $line = $line ?: '-';
            }

            // ==============================
            // 📌 Mapping dokumentasi manual
            // ==============================
            switch ($uri) {
                // API
                case 'api/login':
                    $function = 'Otentikasi pengguna APK dan mendapatkan token akses.';
                    $dataTransfer = 'Body';
                    $keys = 'email, password';
                    $auth = '❌';
                    $response_example = '{ "token": "...", "user": { ... } }';
                    break;
                case 'api/apar/all':
                    $function = 'Mengambil daftar semua APAR untuk tampilan utama APK.';
                    $response_example = '[ { "id": 1, "kode": "AP-001", ... } ]';
                    break;
                case 'api/apar/search':
                    $function = 'Mencari data APAR berdasarkan kode dan tahun untuk laporan tahunan.';
                    $dataTransfer = 'URL Query';
                    $keys = 'kode, tahun';
                    $response_example = '{ "apar": { ... }, "inspections_by_item": { ... } }';
                    break;
                case 'api/apar/inspeksi':
                    $function = 'Menyimpan data hasil inspeksi APAR.';
                    $dataTransfer = 'Body';
                    $keys = 'master_apar_id, user_id, date, details';
                    $response_example = '{ "success": true, "message": "..." }';
                    break;
                case 'api/apar/{id}/update':
                    $function = 'Memperbarui data APAR berdasarkan ID.';
                    $dataTransfer = 'URL Params & Body';
                    $keys = 'id, [data_lain]';
                    $response_example = '{ "message": "Data berhasil diperbarui." }';
                    break;
                case 'api/apar/expiring':
                    $function = 'Mengambil daftar APAR yang akan kadaluarsa untuk notifikasi.';
                    $response_example = '[ { "kode": "AP-005", "tgl_kadaluarsa": "..." } ]';
                    break;
                case 'api/apar/inspection-years':
                    $function = 'Mengambil daftar tahun inspeksi yang tersedia.';
                    $response_example = '{ "data": ["2024", "2025"] }';
                    break;
                case 'api/user':
                    $function = 'Mengambil data pengguna yang sedang login.';
                    $response_example = '{ "id": 1, "name": "...", "email": "..." }';
                    break;

                // Web
                case '/':
                    $function = 'Halaman utama (menu).';
                    $auth = '✅';
                    break;
                case 'route-tracker':
                    $function = 'Menampilkan daftar semua rute API dan Web.';
                    $auth = '❌';
                    break;
                case 'login':
                    $function = 'Halaman form login.';
                    $auth = '❌';
                    break;
                case 'weblogin':
                    $function = 'Memproses data login dari form.';
                    $dataTransfer = 'Body';
                    $keys = 'email, password';
                    $auth = '❌';
                    break;
                case 'logout':
                    $function = 'Mengeluarkan pengguna dari sesi.';
                    $auth = '✅';
                    break;
                case 'dashboard':
                    $function = 'Halaman dashboard admin.';
                    $auth = '✅';
                    break;
                case 'users':
                    $function = 'Halaman manajemen pengguna.';
                    $auth = '✅';
                    break;
                case 'laporan/apar/rusak':
                    $function = 'Halaman laporan APAR rusak.';
                    $auth = '✅';
                    break;
                case 'laporan/apar/refill':
                    $function = 'Halaman laporan APAR refill.';
                    $auth = '✅';
                    break;
                case 'laporan/apar/yearly':
                    $function = 'Halaman laporan tahunan APAR.';
                    $auth = '✅';
                    break;
            }

            // Aturan umum POST di web.php
            if ($file === 'web.php' && in_array('POST', $methods) && $dataTransfer === 'None') {
                $dataTransfer = 'Form Data';
            }

            $routes[] = [
                'uri' => $uri,
                'methods' => implode('|', $methods),
                'name' => $name,
                'action' => is_string($uses) ? $uses : 'Closure',
                'file' => $file,
                'file_path' => $filePath,
                'line' => $line,
                'data_transfer' => $dataTransfer,
                'keys' => $keys,
                'function' => $function,
                'auth' => $auth,
                'response_example' => $response_example,
            ];
        }

        // (opsional) urutkan biar rapi
        usort($routes, fn($a, $b) => strcmp($a['uri'], $b['uri']));

        return view('pages.route-tracker', ['routes' => $routes]);
    }
}
