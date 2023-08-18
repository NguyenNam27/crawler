<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="/backend/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">

          <p>
              Admin
          </p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>

      <!-- sidebar menu: : style can be found in sidebar.less -->

      <ul class="sidebar-menu" data-widget="tree">
{{--
          <li class="active">
              <a href="{{route('result')}}">
                  <i class="fa fa-list"></i> <span>KẾT QUẢ SO SÁNH GIÁ </span>
              </a>
          </li>
--}}
          <li class="active">
              <a href="{{route('report')}}">
                  <i class="fa fa-list"></i> <span>KẾT QUẢ SO SÁNH GIÁ </span>
              </a>
          </li>
          <li class="active">
              <a href="{{route('historyList')}}">
                  <i class="fa fa-list"></i> <span>LỊCH SỬ SO SÁNH</span>
              </a>
          </li>

          <li class="active">
              <a href="{{route('list-partner')}}">
                  <i class="fa fa-list"></i> <span>QUẢN LÝ ĐỐI TÁC </span>
              </a>
          </li>

          <li class="active">
              <a href="{{route('list-product-original')}}">
                  <i class="fa fa-list"></i> <span>QUẢN LÝ SẢN PHẨM GỐC</span>
              </a>
          </li>
{{--              @can('users')--}}
          <li class="active">
              <a href="{{URL::to('users')}}">
                  <i class="fa fa-list"></i> <span>QUẢN LÝ NGƯỜI DÙNG </span>
              </a>
          </li>
{{--              @endcan--}}
          <li class="active">
              <a href="{{URL::to('list-email')}}">
                  <i class="fa fa-list"></i> <span> CÀI ĐẶT GỬI EMAIL </span>
              </a>
          </li>

          <li class="active">
              <a href="{{URL::to('list-group')}}">
                  <i class="fa fa-list"></i> <span> NHÓM NGƯỜI DÙNG </span>
              </a>
          </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
