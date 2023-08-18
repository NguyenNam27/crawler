@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                QUẢN LÝ NGƯỜI DÙNG
{{--                <a href="" class="btn bg-purple btn-flat"><i--}}
{{--                        class="fa fa-plus"></i></a>--}}

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> QUẢN LÝ NGƯỜI DÙNG </a></li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Danh Sách Người Dùng</h3>
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
                                <tr>
                                    <th style="width:10px">STT</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Thao tác</th>
                                </tr>
                                @foreach($userList as $key => $item)
                                    <tr class="item-{{ $item->id }}">
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>...</td>
                                        <td>
                                            <a href=""
                                               class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>
                                            <a onclick="return confirm('Bạn có chắc là muốn xóa người này ko?')" class="btn btn-danger btn-delete" href="">
                                                <i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                            </table>
                            <div class="box-footer clearfix">
                                {{--                                {{ $data->links() }}--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
