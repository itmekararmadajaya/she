@extends('layouts.app')

@section('title', 'Pelacak Rute')

@section('content')
    <div class="container">
        <div class="card shadow mb-4 card-custom-rounded">
            <div class="card-header py-3">
                <h1 class="h3 mb-0 text-gray-800">Pelacak Rute</h1>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" id="searchBox" class="form-control" placeholder="Cari rute..." aria-label="Search routes" style="max-width: 300px;">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>File</th>
                                <th>Metode</th>
                                <th>URL</th>
                                <th>Nama Rute</th>
                                <th>Controller</th>
                                <th>Lokasi File</th>
                                <th>Data TF</th>
                                <th>Key</th>
                                <th>Fungsi</th>
                                <th>Auth</th>
                                <th>Contoh Respon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($routes as $route)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $route['file'] }}</td>
                                    <td>
                                        @foreach(explode('|', $route['methods']) as $method)
                                            <span class="method {{ $method }}">{{ $method }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $route['uri'] }}</td>
                                    <td>{{ $route['name'] }}</td>
                                    <td>{{ $route['action'] }}</td>
                                    <td>{{ Str::limit($route['file_path'] . ':' . $route['line'], 20, '...') }}</td>
                                    <td>{{ $route['data_transfer'] }}</td>
                                    <td>{{ $route['keys'] }}</td>
                                    <td>{{ $route['function'] }}</td>
                                    <td>{{ $route['auth'] }}</td>
                                    <td><pre>{{ Str::limit($route['response_example'], 20, '...') }}</pre></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data rute.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .card-custom-rounded {
        border-radius: 1rem !important;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        vertical-align: middle;
        word-wrap: break-word;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 0;
    }

    .method {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 3px;
        font-weight: bold;
        color: white;
        margin: 2px;
    }

    .GET { background-color: #4CAF50; }
    .POST { background-color: #2196F3; }
    .PUT, .PATCH { background-color: #FFC107; }
    .DELETE { background-color: #F44336; }

    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: monospace;
        margin: 0;
        background-color: #f8f9fa;
        padding: 5px;
        border-radius: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 768px) {
        th, td {
            font-size: 12px;
            padding: 6px;
        }
        .method {
            padding: 2px 4px;
            font-size: 10px;
        }
        #searchBox {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchBox = document.getElementById('searchBox');
        const tableBody = document.querySelector('tbody');
        const tableRows = tableBody.querySelectorAll('tr');

        searchBox.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();

            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchText) ? 'table-row' : 'none';
            });
        });
    });
</script>