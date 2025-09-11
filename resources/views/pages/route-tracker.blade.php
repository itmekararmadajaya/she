@extends('layouts.app')

@section('title', 'Pelacak Rute')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <h1 class="mb-1 h3">Pelacak Rute</h1>

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" id="searchBox" class="form-control" placeholder="Cari rute...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 10%;">No</th>
                                <th style="width: 15%;">File</th>
                                <th style="width: 18%;">Metode</th>
                                <th style="width: 15%;">URL</th>
                                <th style="width: 21%;">Nama Rute</th>
                                <th style="width: 20%;">Controller</th>
                                <th style="width: 20%;">Lokasi 
                                    File</th>
                                <th style="width: 15%;">Data 
                                    TF</th>
                                <th style="width: 10%;">Key</th>
                                <th style="width: 20%;">Fungsi</th>
                                <th style="width: 12%;">Auth</th>
                                <th style="width: 15%;">Contoh
                                    Respon</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($routes as $route)
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
                                <td>{{ $route['file_path'] }}:{{ $route['line'] }}</td>
                                <td>{{ $route['data_transfer'] }}</td>
                                <td>{{ $route['keys'] }}</td>
                                <td>{{ $route['function'] }}</td>
                                <td>{{ $route['auth'] }}</td>
                                <td><pre>{{ $route['response_example'] }}</pre></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

<style>
    /* Styling untuk tabel */
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
        word-break: break-all;
        overflow: hidden
    }
    th {
        background-color: #f2f2f2;
    }
    .method {
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
        color: white;
        white-space: nowrap;
    }
    .GET { background-color: #4CAF50; }
    .POST { background-color: #2196F3; }
    .PUT, .PATCH { background-color: #FFC107; }
    .DELETE { background-color: #F44336; }

    /* Styling untuk card */
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
        background-color: white;
    }
    .card-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }
    .card-body {
        padding: 0;
    }
    
    /* Styling untuk input pencarian */
    #searchBox {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 8px;
    }

    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: monospace;
        margin: 0;
    }

    .pc-container {
    padding: 20px;
    box-sizing: border-box; /* Memastikan padding tidak menambah lebar */
    }
    
    .pc-content {
        max-width: 100%; /* Memastikan konten tidak melebihi lebar induknya */
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
                if (rowText.includes(searchText)) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>