@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Thêm quyền <a href="{{route('list-group')}}" class="btn bg-purple "><i class="fa fa-plus"></i> Danh
                    sách nhóm</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
                <li><a href="#">  Thêm quyền </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Nhập thông tin quyền </h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form action="{{route('save-permission')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="box-body">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Tên key</label>
                                        <input data-validation="required"
                                               data-validation-error-msg="Vui lòng điền tên key"
                                               type="text" class="form-control" name="permission_key"
                                               placeholder="Enter key">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Tên Quyền</label>
                                        <input class="form-control" type="text" name="permission_value">

                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Create</button>
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
