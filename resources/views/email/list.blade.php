@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                CÀI ĐẶT GỬI EMAIL &nbsp;&nbsp;&nbsp;&nbsp;<a href="{{route('addMail')}}" class="btn bg-purple btn-flat"><i
                        class="fa fa-plus"></i> Thêm Email Người Nhận</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> CÀI ĐẶT GỬI EMAIL </a></li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box">

                        <div class="box-header with-border">

                            <h3 class="box-title">Danh Sách Email Người Nhận </h3><br/>
                            <small>Bấm nút "Gửi kết quả" để để thông báo kết quả đến người nhận.</small>

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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created_at</th>
                                    <th>Status</th>
                                    <th >Thao tác</th>
                                </tr>
                                @foreach($listMail as $key => $item)
                                    <tr class="item-{{ $item-> id }}">
                                        <td>{{ $key +1 }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->email }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ ($item->status==1) ? 'Hiển thị' : 'Không Hiển thị' }}</td>
                                        <td style="margin-right: auto;float: ">
                                            <a href="{{URL::to('edit-email/'.$item->id)}}" class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>

                                            <a onclick="return confirm('Bạn có chắc là muốn xóa email này ko?')"
                                               class="btn btn-danger btn-delete"
                                               href="{{URL::to('delete-email/'.$item->id)}}"><i
                                                    class="fa fa-trash"></i></a>
                                            <a href="{{ URL::to('send-notification/'.$item->id) }}" class="btn btn-primary">Gửi kết quả</a>
                                        </td>

                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer clearfix">
                            {{--                            {{ $data->links() }}--}}
                        </div>
                    </div>
                </div>

            </div>
        </section>
@endsection
