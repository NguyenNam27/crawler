@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Chỉnh sửa người dùng<a href="" class="btn bg-purple "><i
                        class="fa fa-plus"></i> Danh sách người dùng</a>

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
                <li><a href="#">Người dùng </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cập nhập thông tin </h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form role="form" action="" method="post"
                              enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="box-body">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"> Tên Mới </label>
                                        <input value="" class="form-control" type="text" name="name">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"> Email </label>
                                        <input value="" data-validation="required"
                                               data-validation-error-msg="Vui lòng nhập email "
                                               type="text" class="form-control" name="email" id=""
                                               placeholder="Enter email">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Mật khẩu</label>
                                        <input value="" class="form-control" type="text" name="password">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Tình trạng</label>
                                        <select class="form-control" name="status">
                                            <option value="" disabled>--Chọn--</option>
                                            <option value="1">Kích hoạt</option>
                                            <option value="0">Không kích hoạt</option>
                                        </select>

                                    </div>

                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.box -->
                </div>
            </div>

        </section>
        <!-- /.content -->
    </div>
@endsection
