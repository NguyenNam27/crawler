@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cập nhập danh mục sản phẩm <a href="{{route('list-category')}}" class="btn bg-purple "><i
                        class="fa fa-plus"></i> Danh sách danh mục sản phẩm</a>

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">CẬP NHẬP DANH MỤC SẢN PHẨM </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Nhập thông tin danh mục</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <form  action="{{URL::to('update-category/'.$edit_category->id)}}" method="POST" enctype="multipart/form-data" >
                            {{ csrf_field() }}
                            <div class="box-body">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Tên danh mục mới </label>
                                        <input value="{{$edit_category->name}}" data-validation="required"
                                               data-validation-error-msg="Vui lòng điền tên danh mục"
                                               type="text" class="form-control" name="name" id=""
                                               placeholder="Enter name">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Link</label>
                                        <input value="{{$edit_category->url}}" class="form-control" type="text" name="url">


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
