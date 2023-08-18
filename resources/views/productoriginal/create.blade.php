@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Thêm sản phẩm <a href="{{route('list-product-original')}}" class="btn bg-purple "><i
                        class="fa fa-plus"></i> Danh sách sản phẩm</a>

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">SẢN PHẨM </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Nhập thông tin sản phẩm</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form role="form" action="{{route('save-product-original')}}" method="post"
                              enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="box-body">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Code </label>
                                        <input class="form-control" type="text" name="code_product">


                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"> Tên sản phẩm </label>
                                        <input data-validation="required"
                                               data-validation-error-msg="Vui lòng điền tên sản phẩm "
                                               type="text" class="form-control" name="name" id=""
                                               placeholder="Enter name">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Giá sản phẩm</label>
                                        <input class="form-control" type="text" name="price_cost">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Giá Min</label>
                                        <input class="form-control" type="text" name="price_min">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Link</label>
                                        <input class="form-control" type="text" name="link_product">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Thương hiệu </label>
                                        <input class="form-control" type="text" name="brand">
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
