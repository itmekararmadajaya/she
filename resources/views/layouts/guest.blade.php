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
      
  @yield('content')

  <script src="{{asset('admin template/dist/assets/js/plugins/popper.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/simplebar.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/bootstrap.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/script.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/theme.js')}}"></script>
</body>
</html>