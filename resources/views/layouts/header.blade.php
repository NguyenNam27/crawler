<header class="main-header">
    <!-- Logo -->
    <a href="" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>A</b>LT</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>CRAWL</b>DATA</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
{{--              <img src="" class="user-image" alt="User Image">--}}
              <span class="hidden-xs"> Xin chào : Admin
{{--                  <?php--}}
{{--                  $name = Session::get('name');--}}
{{--                  if($name){--}}
{{--                      echo $name;--}}
{{--                  }--}}
{{--                  ?>--}}

              </span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">

                <h4>
                    Admin
{{--                    <?php--}}
{{--                    $name = Session::get('name');--}}
{{--                    if($name){--}}
{{--                        echo $name;--}}
{{--                    }--}}
{{--                    ?>--}}
                </h4>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">

                </div>
                <div class="pull-right">
                  <a href="{{URL::to('logout')}}" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->

        </ul>
      </div>
    </nav>
  </header>
