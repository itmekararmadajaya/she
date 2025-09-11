<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'New Armada')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      integrity="sha512-pO3X1iKkPz6xS3V9g2uBZpZB9lQHhUoyG8t0uR3W1+a0C7zWjpX0M5R3og+N6X6u1xBjOZ9hG1t1Yx5b3VgkzA=="
      crossorigin="anonymous" referrerpolicy="no-referrer" />

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