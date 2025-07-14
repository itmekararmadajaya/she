<!-- [ Header Topbar ] start -->
  <header class="pc-header">
    <div class="header-wrapper">
        <!-- [Mobile Media Block] start -->
      <div class="me-auto pc-mob-drp">
        <ul class="list-unstyled">
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
      </div>
      <!-- [Mobile Media Block end] -->
      <div class="ms-auto">
        <ul class="list-unstyled">
          <li class="dropdown pc-h-item header-user-profile">
            <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
              aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
              <i class="ph ph-user-circle"></i>
            </a>
            <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown p-0 overflow-hidden">
              <div class="dropdown-header d-flex align-items-center justify-content-between bg-primary">
                <div class="d-flex my-2">
                  <div class="flex-grow-1 ms-3">
                    <h6 class="text-white mb-1">Carson Darrin 🖖</h6>
                    <span class="text-white text-opacity-75">carson.darrin@company.io</span>
                  </div>
                </div>
              </div>
              <div class="dropdown-body">
                <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                  <a href="#" class="dropdown-item">
                    <span>
                      <i class="ph ph-gear align-middle me-2"></i>
                      <span>Profile</span>
                    </span>
                  </a>
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
  <!-- [ Header ] end -->