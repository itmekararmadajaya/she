<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'New Armada')</title>

    @include('layouts.ui.css')
    <style>
      .swal2-container {
          z-index: 9999 !important;
      }
  </style>
</head>
<body>
  @include('layouts.partials.sidebar')

  @include('layouts.partials.header')

  <div class="pc-container">
    <div class="pc-content">
      <!-- [ Main Content ] start -->
      <div class="row">        
        @yield('content')
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>

  @include('layouts.ui.js')

  @stack('scripts')
</body>
</html>