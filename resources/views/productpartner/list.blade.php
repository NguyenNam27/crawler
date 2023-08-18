@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                DANH SÁCH SẢN PHẨM ĐỐI TÁC

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> SẢN PHẨM ĐỐI TÁC </a></li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    <div class="box">

                        <div class="box-header with-border">

                            <div class="col-sm-3 col-md-3">
                                <div class="input-group">
                                    <a href="{{route('exportProductPartner')}}" class="btn btn-primary "> Export Excel </a>
                                </div>
                            </div>

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
                                    <th >MaSP</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá đối tác</th>
                                    <th>Qùa tặng</th>
                                    <th>Link sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Ngày tạo</th>
                                    <th>Tình trạng</th>
                                    <th>Thao tác</th>
                                </tr>
                                @foreach($partnerProductList as $key => $item)
                                    <tr class="item">
                                        <td>{{ $key +1 }}</td>
                                        <td>{{ $item->code_product }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format($item->price_partner) }}đ</td>
                                        <td>{{ $item->price_sale }}</td>
                                        <td><a href="{{ $item->link_product }}">{{ $item->link_product }}</a></td>
                                        <td>{{ $item->category_id }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ ($item->status==1) ? 'Hiển thị' : 'Không Hiển thị' }}</td>
                                        <td>
                                            <a onclick="return confirm('Bạn có chắc là muốn xóa đối tác này ko?')" class="btn btn-danger btn-delete" href=""><i class="fa fa-trash"></i></a>
{{--                                            {{URL::to('delete-partner/'.$item->id)}}--}}

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer clearfix">
                                                        {{ $partnerProductList->links() }}
                        </div>
                    </div>
                </div>

            </div>


        </section>
@endsection
