@extends('layouts.guest')

@section('title', 'Menu Utama')

@section('content')
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="text-center mb-4">
        <h4 class="fw-bold">Main Menu</h4>
      </div>
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 px-4 pt-4">
          <h5 class="mb-0 fw-semibold text-danger">
            <i class="ph ph-fire-extinguisher me-1"></i> APAR
          </h5>
        </div>
        <div class="card-body px-4 pb-4 pt-0">
            <!-- Menu -->
          <div class="d-flex flex-wrap justify-content-start gap-3">
            <a href="{{route('apar.index')}}" class="text-decoration-none">
              <div class="d-flex flex-column align-items-center text-center rounded hover-shadow transition">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2"
                     style="width: 56px; height: 56px; background-color: #3f51b5;">
                     <i class="ph ph-qr-code text-white fs-4"></i>
                </div>
                <span class="fw-semibold text-dark small">Inspeksi</span>
              </div>
            </a>
            <a href="{{route('laporan.apar.yearly-index')}}" class="text-decoration-none">
              <div class="d-flex flex-column align-items-center text-center rounded hover-shadow transition">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2"
                     style="width: 56px; height: 56px; background-color: #4CAF50;">
                  <i class="ph ph-clipboard-text text-white fs-4"></i>
                </div>
                <span class="fw-semibold text-dark small">Laporan Yearly</span>
              </div>
            </a>
          </div>
        </div>
      </div>
      <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 px-4 pt-4">
          <h5 class="mb-0 fw-semibold text-dark">
            <i class="ph ph-gear me-1"></i> Setting
          </h5>
        </div>
        <div class="card-body px-4 pb-4 pt-0">
            <!-- Menu -->
            <div class="d-flex flex-wrap justify-content-start gap-3">
                {{-- Logout --}}
                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="border-0 bg-transparent p-0">
                            <div class="d-flex flex-column align-items-center text-center rounded hover-shadow transition">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2"
                                    style="width: 56px; height: 56px; background-color: #f44336;">
                                    <i class="ph ph-sign-out text-white fs-4"></i>
                                </div>
                                <span class="fw-semibold text-dark small">logout</span>
                            </div>
                        </button>
                    </form>
                </div>

                {{-- Dashboard --}}
                <div>
                  <a href="{{ route('dashboard') }}">
                    <button type="submit" class="border-0 bg-transparent p-0">
                      <div class="d-flex flex-column align-items-center text-center rounded hover-shadow transition">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2"
                                      style="width: 56px; height: 56px; background-color: #2196f3;">
                          <i class="ph ph-gauge text-white fs-4"></i>
                        </div>
                        <span class="fw-semibold text-dark small">Dashboard</span>
                      </div>
                    </button>
                    </a>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
