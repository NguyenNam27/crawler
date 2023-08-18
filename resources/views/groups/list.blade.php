@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                DANH SÁCH NHÓM NGƯỜI DÙNG <a href="{{route('add-group')}}" class="btn bg-purple btn-flat"><i class="fa fa-plus"></i> Thêm nhóm</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> QUẢN LÝ NHÓM </a></li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box">

                        <div class="box-header with-border">

                            <h3 class="box-title">Danh Sách Nhóm </h3><br/>

                        </div>
                        <?php
                        $message = Session::get('message');
                        if($message){
                            echo '<h3 class="text-alert" style="color: red">' .$message. '</h3>';
                            Session::put('message',null);
                        }
                        ?>


                        <div class="box-body">
                            <table class="table table-border">
                                <tbody>
                                <tr>
                                    <th style="width: 10px">STT</th>
                                    <th>Tên</th>
                                    <th>Người tạo</th>
                                    <th>Ngày tạo</th>
                                    <th >Thao tác</th>
                                </tr>
                                @foreach($groupList as $key => $item)
                                    <tr class="item-{{ $item-> id }}">
                                        <td>{{ $key +1 }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->user_id }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td style="display: flex;margin-right: auto">
                                            <a href="{{URL::to('edit-group/'.$item->id)}}" class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>
                                            <a onclick="return confirm('Bạn có chắc là muốn xóa nhóm này ko?')" class="btn btn-danger btn-delete" href="{{URL::to('delete-group/'.$item->id)}}"><i class="fa fa-trash"></i></a>
                                            <a href="{{URL::to('permisstion-group/'.$item->id)}}"  class="btn btn-primary">Phân quyền</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer clearfix">
                            {{ $groupList->links()}}
                        </div>
                    </div>
                </div>

            </div>


        </section>
@endsection
