@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Thêm Đối Tác <a href="{{route('list-partner')}}" class="btn bg-purple "><i class="fa fa-plus"></i> Danh sách đối tác</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#"> BRAND </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Nhập thông tin đối tác</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form action="{{route('save-partner')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="box-body">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name Partner</label>
                                        <input data-validation="required"
                                               data-validation-error-msg="Vui lòng điền tên đối tác"
                                               type="text" class="form-control" name="name"
                                               placeholder="Enter name">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">URL</label>
                                        <input class="form-control" type="text"  name="url">

                                    </div>
                                    <div class="form-group">
                                            <label for="exampleInputEmail1">Keyword</label>
                                        <input class="form-control" type="text"  name="keyword">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Pagination</label>
                                        <input class="form-control" type="text"  name="pagination">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Category</label>
                                        <input class="form-control" type="text"  name="category_id">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class cha </label>
                                        <input class="form-control" type="text"  name="values_parent">
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class NameSP </label>
                                        <input class="form-control" type="text"  name="values_name">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class PriceSP </label>
                                        <input class="form-control" type="text"  name="values_price">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class PriceSale </label>
                                        <input class="form-control" type="text"  name="values_sale">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class LinkSP </label>
                                        <input class="form-control" type="text"  name="values_link">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class CodeSP </label>
                                        <input value="" class="form-control" type="text"  name="values_code">
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
