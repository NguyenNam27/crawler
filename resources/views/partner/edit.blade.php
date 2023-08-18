@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Cập nhập Đối Tác &nbsp;&nbsp;&nbsp;<a href="{{route('list-partner')}}" class="btn bg-purple "><i class="fa fa-list"></i> Danh sách đối tác</a>

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#"> CẬP NHẬP THÔNG TIN ĐỐI TÁC </a></li>

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

                        <form action="{{URL::to('update-partner/'.$edit_partner->id)}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="box-body">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name Partner</label>
                                        <input value="{{$edit_partner->name}}" data-validation="required"
                                               data-validation-error-msg=""
                                               type="text" class="form-control" name="name"
                                               placeholder="Enter name">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">URL</label>
                                        <input value="{{$edit_partner->url}}" class="form-control" type="text"  name="url">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Keyword</label>
                                        <input value="{{$edit_partner->keyword}}" class="form-control" type="text"  name="keyword">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Category</label>
                                        <input value="{{$edit_partner->category_id}}" class="form-control" type="text"  name="category_id">

                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class cha </label>
                                        <input value="{{$decodeData->class_parent}}" class="form-control" type="text"  name="values_parent">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class NameSP </label>
                                        <input value="{{$decodeData->class_name}}" class="form-control" type="text"  name="values_name">
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class PriceSP </label>
                                        <input value="{{$decodeData->class_price}}" class="form-control" type="text"  name="values_price">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class PriceSale </label>
                                        <input value="{{$decodeData->class_sale}}" class="form-control" type="text"  name="values_sale">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class LinkSP </label>
                                        <input value="{{$decodeData->class_link}}" class="form-control" type="text"  name="values_link">
                                    </div>

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Value class CodeSP </label>
                                        <input value="{{ $decodeData->class_code ?? '' }}" class="form-control" type="text"  name="values_code">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="{{$edit_partner->status}}" disabled>--Chọn--</option>
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
