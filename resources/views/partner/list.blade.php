@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                DANH SÁCH ĐỐI TÁC/ĐẠI LÝ &nbsp;&nbsp;&nbsp;&nbsp;<a href="{{route('add-partner')}}" class="btn bg-purple btn-flat"><i class="fa fa-plus"></i> Thêm đối tác</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> QUẢN LÝ ĐỐI TÁC </a></li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box" style="overflow: auto">

                        <div class="box-header with-border">

                            <h3 class="box-title">Danh Sách đối tác </h3><br/>
                            <small>Bấm nút "Quét dữ liệu" để tải dữ liệu mới nhất từ website đối tác (đã chọn).</small><br>
                            <small>Dữ liệu sẽ tự động cập nhập lúc 1h30' hàng ngày.</small>

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
                                    <th>Tên đối tác</th>
                                    <th>Đường dẫn</th>
                                    <th>Từ khóa tìm kiếm</th>
                                    <th>Website đối tác</th>
                                    <th>Ngày tạo</th>
                                    <th>Tình trạng</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                                    @foreach($partnerList as $key => $item)
                                        <tr class="item-{{ $item-> id }}">
                                            <td>{{ $key +1 }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td><a href="{{ $item->url }}">{{ $item->url }}</a></td>

                                            <td>{{ $item->keyword }}</td>
                                            <td>{{ $item->category_id }}</td>

                                            <td>{{ $item->created_at }}</td>
                                            <td>{{ ($item->status==1) ? 'Hiển thị' : 'Không Hiển thị' }}</td>
                                            <td style="display: flex">
                                                <a href="{{URL::to('edit-partner/'.$item->id)}}" class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>
                                                <a onclick="return confirm('Bạn có chắc là muốn xóa đối tác này ko?')" class="btn btn-danger btn-delete" href="{{URL::to('delete-partner/'.$item->id)}}"><i class="fa fa-trash"></i></a>
                                                <a href="{{ URL::to('crawl-data/'.$item->id) }}"  class="btn btn-primary">Quét dữ liệu</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer clearfix">
                            {{ $partnerList->links() }}
                        </div>
                    </div>
                </div>

            </div>


        </section>
@endsection
