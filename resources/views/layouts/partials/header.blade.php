<header class="pc-header">
    <div class="header-wrapper d-flex align-items-center justify-content-between">
        <div class="pc-mob-drp d-flex align-items-center">
            <ul class="list-unstyled mb-0">
                <li class="pc-h-item pc-sidebar-collapse">
                    <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                        <i class="ph ph-list"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                        <i class="ph ph-list"></i>
                    </a>
                </li>
            </ul>
            <h3 class="text-black mb-0 ms-3">Halo {{Auth::user()->name}}</h3>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="ph ph-sign-out"></i>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">
                        <div class="dropdown-header d-flex align-items-center justify-content-between bg-primary">
                            <div class="d-flex my-2">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-white mb-1">{{Auth::user()->name}}</h6>
                                    <span class="text-white text-opacity-75">{{Auth::user()->email}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                                <div class="d-grid my-2">
                                    <form action="{{route('logout')}}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary"> <i class="ph ph-sign-out align-middle me-2"></i>Logout </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>