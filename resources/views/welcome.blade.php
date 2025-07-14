<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <!-- [Font] Family -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{asset('admin template/dist/assets/fonts/phosphor/regular/style.css')}}" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{asset('admin template/dist/assets/fonts/tabler-icons.min.css')}}" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{asset('admin template/dist/assets/css/style.css')}}" id="main-style-link" />
    <link rel="stylesheet" href="{{asset('admin template/dist/assets/css/style-preset.css')}}" />
</head>
<body>
<!-- [ Sidebar Menu ] start -->
  <nav class="pc-sidebar">
    <div class="navbar-wrapper">
      <div class="m-header">
        <a href="../dashboard/index.html" class="b-brand text-primary">
          <!-- ========   Change your logo from here   ============ -->
          <img src="{{asset('asset/logo/logona2.png')}}" class="img-fluid logo-lg" alt="logo" />
        </a>
      </div>
      <div class="navbar-content">
        <ul class="pc-navbar">
            <li class="pc-item pc-caption">
                <label data-i18n="Other">APAR</label>
                <i class="ph ph-tree-structure"></i>
            </li>
            <li class="pc-item pc-hasmenu">
                <a href="#!" class="pc-link"
                    ><span class="pc-micon"> <i class="ph ph-tree-structure"></i> </span><span class="pc-mtext">Menu Master</span
                    ><span class="pc-arrow"><i class="ti ti-chevron-right"></i></span
                ></a>
                <ul class="pc-submenu">
                    <li class="pc-item"><a class="pc-link" href="#!">APAR</a></li>
                    <li class="pc-item"><a class="pc-link" href="#!">Item Check</a></li>
                    <li class="pc-item"><a class="pc-link" href="#!">Gedung</a></li>
                </ul>
            </li>
            <li class="pc-item"><a href="../other/sample-page.html" class="pc-link">
              <span class="pc-micon">
                <i class="ph ph-cube"></i>
              </span>
              <span class="pc-mtext">Master APAR</span></a>
            </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- [ Sidebar Menu ] end -->

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
                    <button class="btn btn-primary"> <i class="ph ph-sign-out align-middle me-2"></i>Logout </button>
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

  <div class="pc-container">
    <div class="pc-content">
      <!-- [ breadcrumb ] start -->
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Home</h5>
              </div>
            </div>
            <div class="col-md-12">
              <ul class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard/index.html">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript: void(0)">Dashboard</a></li>
                <li class="breadcrumb-item" aria-current="page">Home</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- [ breadcrumb ] end -->

      <!-- [ Main Content ] start -->
      <div class="row">        
        
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>

  <script src="{{asset('admin template/dist/assets/js/plugins/popper.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/simplebar.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/bootstrap.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/script.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/theme.js')}}"></script>
</body>
</html>