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
    <div class="auth-main">
      <div class="auth-wrapper v1">
        <div class="auth-form">
          <div class="position-relative my-5">
                <div class="card mb-0">
                <div class="card-body">
                    <div class="text-center">
                    <a href="#"><img src="{{asset('asset/logo/logona2.png')}}" class="w-75" alt="img" /></a>
                    </div>
                    <h4 class="text-center f-w-500 mt-4 mb-3">Login</h4>
                    <div class="form-group mb-3">
                    <input type="email" class="form-control" id="floatingInput" placeholder="Email Address" />
                    </div>
                    <div class="form-group mb-3">
                    <input type="password" class="form-control" id="floatingInput1" placeholder="Password" />
                    </div>
                    <div class="text-center mt-4">
                    <button type="button" class="btn btn-primary shadow px-sm-4">Login</button>
                    </div>
                </div>
                </div>
          </div>
        </div>
      </div>
    </div>

  <script src="{{asset('admin template/dist/assets/js/plugins/popper.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/simplebar.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/plugins/bootstrap.min.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/script.js')}}"></script>
  <script src="{{asset('admin template/dist/assets/js/theme.js')}}"></script>
</body>
</html>