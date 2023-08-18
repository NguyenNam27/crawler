<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Crawl Data</title>
    <base href="{{asset("")}}">
    <meta name="csrf-token" content="{{csrf_token()}}">

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="backend/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="backend/bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="backend/bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="backend/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="backend/dist/css/skins/_all-skins.min.css">

    <!-- Daterange picker -->
    <link rel="stylesheet" href="backend/bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        .line-clamp{overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;max-height:1rem;-webkit-box-orient:vertical;line-height: 1rem}
        .line-clamp *{
            line-height: 1.2em
        }
        .line-clamp.l2{-webkit-line-clamp:2;max-height:2.4em}
        .line-clamp.l3{-webkit-line-clamp:3;max-height:3.6em}
        .line-clamp.l4{-webkit-line-clamp:4;max-height:4.8em}
        .line-clamp.l5{-webkit-line-clamp:5;max-height:6em}
        .line-clamp.l6{-webkit-line-clamp:6;max-height:7.2em}
        .line-clamp.l7{-webkit-line-clamp:7;max-height:8.4em}
        .line-clamp.l8{-webkit-line-clamp:8;max-height:9.6em}
        .chrome .line-clamp{
            max-height: inherit;
        }
        .m0{
            margin: 0;
        }
    </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    @include('layouts.header')
    @include('layouts.sidebar')
    @yield('content')
    @include('layouts.footer')
    <script src="backend/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="backend/bower_components/jquery-ui/jquery-ui.min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="backend/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="backend/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
    <script src="backend/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="backend/bower_components/fastclick/lib/fastclick.js"></script>
    <script src="backend/dist/js/adminlte.min.js"></script>
    <script src="backend/dist/js/pages/dashboard.js"></script>
    <script src="backend/dist/js/demo.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@yield('my_js')
@yield('scripts')
</body>
</html>
