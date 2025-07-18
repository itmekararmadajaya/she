<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="../dashboard/index.html" class="b-brand text-primary">
                <img src="{{asset('asset/logo/logona2.png')}}" class="img-fluid logo-lg" alt="logo" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other">Dashboard</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item"><a href="{{route('dashboard')}}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-clock"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span></a>
                </li>
            </ul>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other">APAR</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"> <i class="ph ph-tree-structure"></i> </span><span class="pc-mtext">Menu Master</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{route('master-apar.index')}}">APAR</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('item-check.index')}}">Item Check</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('gedung.index')}}">Gedung</a></li>
                    </ul>
                </li>
                <li class="pc-item"><a href="{{route('riwayat-inspeksi')}}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-clock"></i>
                        </span>
                        <span class="pc-mtext">Riwayat Inspeksi</span></a>
                </li>
            </ul>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other">Laporan</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"> <i class="ph ph-book"></i> </span><span class="pc-mtext">APAR</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.rusak-index')}}">Rusak</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.refill-index')}}">Refill</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.yearly-index')}}">Yearly</a></li>
                        {{-- <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.refill-index')}}">Detail</a></li> --}}
                    </ul>
                </li>
            </ul>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other">Setting</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item"><a href="{{ route('users.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-cube"></i>
                        </span>
                        <span class="pc-mtext">User</span></a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->