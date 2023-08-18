@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Chỉnh sửa Email <a href="{{route('listEmail')}}" class="btn bg-purple "><i class="fa fa-plus"></i> Danh sách email</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="{{route('listEmail')}}"><i class="fa fa-dashboard"></i> Danh sách</a></li>
                <li><a href="{{route('listEmail')}}"> Email </a></li>

            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">

                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Chỉnh sửa thông tin mail</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        <form action="{{URL::to('update-email/'.$editMail->id)}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="box-body">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Name</label>
                                        <input value="{{$editMail->name}}" data-validation="required"
                                               data-validation-error-msg="Vui lòng điền tên "
                                               type="text" class="form-control" name="name"
                                               placeholder="Enter name">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Email</label>
                                        <input value="{{$editMail->email}}" class="form-control" type="email"  name="email" placeholder="Enter email">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Status</label>
                                        <select class="form-control" name="status">
                                            <option value="{{$editMail->status}}" disabled>--Chọn--</option>
                                            <option value="1">Kích hoạt</option>
                                            <option value="0">Không kích hoạt</option>
                                        </select>

                                    </div>

                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Cập nhập</button>
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
