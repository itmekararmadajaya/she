<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header" style="display: flex; justify-content: center; align-items: center; gap: 2px; margin-top: 35px; margin-bottom: 5px;">
            <a href="{{route('dashboard')}}" class="b-brand text-primary" style="width: 60px; height: 80px">
                <img src="{{asset('asset/logo/logona.png')}}" class="img-fluid logo-lg" alt="logo" />
            </a>
            <a href="{{route('dashboard')}}" class="b-brand text-primary" style="width: 160px; height: 100px">
                <img src="{{asset('asset/logo/she.png')}}" class="img-fluid logo-lg" alt="logo" />
            </a>
        </div>
        <div class="navbar-content" style="margin-top: 20px;">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption" style="margin-top: 5px; margin-bottom: 5px;">
                    <label data-i18n="Other" style="font-size: 1rem">Dashboard</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item"><a href="{{route('dashboard')}}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-house-line"></i>
                        </span>
                        <span class="pc-mtext">Dashboard</span></a>
                </li>
            </ul>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other" style="font-size: 1rem">APAR</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"> <i class="ph ph-tree-structure"></i> </span><span class="pc-mtext">Menu Master</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{route('master-apar.index')}}">APAR</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('item-check.index')}}">Item Check</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('gedung.index')}}">Area</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"> <i class="ph ph-align-bottom"></i> </span><span class="pc-mtext">Inspeksi</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{route('riwayat-inspeksi')}}">Riwayat Inspeksi</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('apar.uninspected')}}">Belum Inspeksi</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.refill-index')}}">Kadaluarsa</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{route('laporan.apar.yearly-index')}}">Laporan Yearly</a></li>
                    </ul>
                </li>
                <li class="pc-item pc-hasmenu">
                    <a href="#!" class="pc-link"><span class="pc-micon"> <i class="ph ph-article"></i> </span><span class="pc-mtext">Keuangan dan Vendor</span><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span></a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('vendor.index') }}">Vendor</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('kebutuhan.index') }}">Kebutuhan</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('transaksi.index') }}">Keuangan</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label data-i18n="Other" style="font-size: 1rem">Setting</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item">
                    <a href="{{ route('users.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-cube"></i>
                        </span>
                        <span class="pc-mtext">User</span>
                    </a>
                </li>
                <li class="pc-item pc-caption">
                    <label data-i18n="Other" style="font-size: 1rem">API</label>
                    <i class="ph ph-tree-structure"></i>
                </li>
                <li class="pc-item">
                    <a href="{{ route('route-tracker') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ph ph-list-dashes"></i>
                        </span>
                        <span class="pc-mtext">Pelacak Rute</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>