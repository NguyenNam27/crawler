@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                BẢNG KẾT QUẢ SO SÁNH NGÀY

                @php
                    $page = $_GET['page'] ?? 1;
                    echo date('d/m/Y') . '('.$countTotal.' kết quả)';
                @endphp

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> BẢNG KẾT QUẢ SO SÁNH </a></li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box" style="overflow: auto">
                        <div class="box-header with-border">
                            <div class="col-sm-3 col-md-3">
                                <form action="{{route('result')}}" method="GET" class="navbar-form" name="search">
                                    @csrf
                                    <div class="input-group w-100" style="width: 100%">

                                        <input type="text" class="form-control" placeholder="Tìm kiếm sản phẩm"
                                               name="search" id="search" value="{{$search}}">
                                        @php
                                            if(isset($_GET['mySiteOption'])){
                                                echo '<input type="hidden" name="mySiteOption" value="'.$_GET['mySiteOption'].'">';
                                            }
                                        @endphp
                                        <div class="input-group-btn">
                                            <button class="btn btn-default" type="submit"><i
                                                    class="glyphicon glyphicon-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-3 col-md-3">
                                <div class="input-group" style="width: 100%">
                                    <form action="{{route('result')}}"
                                          style="display: flex;margin-top: 0.7rem">
                                        <select class="form-select form-select-sm filter" aria-label=".form-select-sm example"
                                                data-url="" name="mySiteOption">
                                            <option value="" >-- Chọn DM/Thương hiệu --</option>
                                            @foreach($cate as $key => $value)
                                                <option
                                                    value={{ $value }} @if($originalSite == $value) selected @endif>{{ $value  }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <select class="form-select form-select-sm " aria-label=".form-select-sm example"
                                                data-url="" name="partnerSiteOption">
                                            <option value="" >-- Chọn DM/Thương hiệu đối tác --</option>
                                            @foreach($catePartner as $key => $value2)
                                                <option value={{ $value2 }} @if($catePartner == $value2) selected @endif>{{ $value2  }}</option>
                                            @endforeach

                                        </select>

                                        @php
                                            if(isset($_GET['search'])){
                                                echo '<input type="hidden" name="search" value="'.$_GET['search'].'">';
                                            }
                                        @endphp
                                        <button type="submit" class="btn btn-primary">Lọc</button>
                                    </form>
                                </div>
                            </div>

                            <div class="col-sm-2 col-md-2">
                                <form action="{{route('exportProductResult')}}"
                                      style="display: flex;margin-top: 0.7rem;position: absolute;">
                                    <input type="hidden" name="mySiteOption" value="{{$originalSite}}">
                                    <input type="hidden" name="search" value="{{$search}}">
                                    <button type="submit" class="btn btn-primary" style="margin-left: 25rem;">Export Excel</button>
                                </form>
                            </div>
                        </div>
                        <div class="box-body">
                            <table class="table table-border" id="example">
                                <tr>
                                    <th style="width:10px">STT</th>
                                    <th>Ngày tháng</th>
                                    <th>DM/Thương hiệu</th>
                                    <th>Mã SP</th>
                                    <th>Tên SP</th>
                                    <th>Giá niêm yết</th>
                                    <th>Giá min</th>
                                    <th>Giá đối tác</th>
                                    <th>Link tham chiếu</th>
                                    <th>Quà tặng ĐT</th>
                                    <th>Chênh lệch giá niêm yết</th>
                                    <th>Chênh lệch giá min</th>

                                </tr>
                                @foreach($products as $key => $item)
                                    <tr class="item">
                                        <td>{{ ($key + 1 + ($page -1) * 25) }}</td>
                                        <td>{{ $item->date }}</td>
                                        <td>{{$item->cate_pr_or}}</td>
                                        <td><span class="no-wrap"
                                                  style="white-space: nowrap"> {{$item->code_product}}</span></td>
                                        <td>
                                            <a TARGET="_blank" href="{{$item->link_product_pr_or}}">
                                                {{$item->nam_pr_or}}
                                            </a>
                                        </td>
                                        <td style="text-align: right">{{ number_format($item->original_price) }}đ</td>
                                        <td style="text-align: right">{{ number_format($item->original_priceMin) }}đ
                                        </td>
                                        <td style="text-align: right">{{ number_format($item->partner_price) }}đ</td>
                                        <td><a target="_blank"
                                               href="{{$item->link_pr_cus}}">{{ $item->link_pr_cus }}</a></td>
                                        <td>{{$item->partner_priceSale}}</td>
                                        <td style="color: red; text-align: right">
                                            {{ number_format($item->price_difference) }}đ
                                        </td>
                                        <td style="color: red; text-align: right">{{ number_format($item->priceMin_difference) }}đ
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <input type="hidden" name="hidden_page" id="hidden_page" value="1"/>
                            <div class="item-paginate" style="border: 1px">
                                {{ $products->appends($_GET)->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
