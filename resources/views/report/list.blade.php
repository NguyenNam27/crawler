@extends('layouts.main')
@section('content')
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                BẢNG BÁO CÁO SO SÁNH NGÀY

                @php
                    $page = $_GET['page'] ?? 1;
                    echo date('d/m/Y') . '('.$countTotal.' kết quả)';
                @endphp

            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> BẢNG BÁO CÁO SO SÁNH </a></li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box" style="overflow: auto">
                        <div class="box-header with-border">
                            <div class="col-sm-3 col-md-3">
                                <form action="{{route('report')}}" method="GET" class="navbar-form" name="search">
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
                            <div class="col-sm-3 col-md-6">
                                <div class="input-group" style="width: 100%">
                                    <form action="{{route('report')}}"
                                          style="display: flex;margin-top: 0.7rem">
                                        <select class="form-select form-select-sm filter" aria-label=".form-select-sm example"
                                                data-url="" name="brand">
                                            <option value="" >-- Chọn Thương hiệu --</option>
                                            @foreach($brands as $key => $value)
                                                <option
                                                    value={{ $value->code }} @if($brand == $value->code) selected @endif>{{ $value->name  }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input value="{{$startDate}}" type="date" class="form-control"
                                               name="start_date" id="search">

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
                                      style="">
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
                                    <th>Thương hiệu</th>
                                    <th>Mã SP</th>
                                    <th>Giá niêm yết</th>
                                    <th>Giá min</th>
                                    @foreach($partners as $partner)
                                        <th>{{$partner->name}}</th>
                                    @endforeach

                                </tr>
                                @foreach($products as $key => $item)
                                    <tr class="item">
                                        <td>{{ ($key + 1 + ($page -1) * 25) }}</td>

                                        <td>{{$item->cate_pr_or}}</td>
                                        <td><span class="no-wrap"
                                                  style="white-space: nowrap"> {{$item->code_product}}</span></td>

                                        <td style="text-align: right">{{ number_format($item->original_price) }}đ</td>
                                        <td style="text-align: right">
                                            {{ number_format($item->original_priceMin) }}đ
                                        </td>
                                        @php
                                        $prices = $item->getProductPartner()->toArray();
                                        @endphp
                                        @foreach($partners as $partner)

                                            <td style="text-align: right">

                                                @php
                                                if(!empty($prices)){
                                                    $exits = 0;
                                                    foreach ($prices as $k=> $price){
                                                        if($price['partner_id'] == $partner->id){
                                                            echo '<a href="'.$price['link_product'].'" target="_blank">';
                                                            echo number_format($price['price_partner']);
                                                            $diff = ($price['price_partner'] - $item->original_price)/1000;
                                                            $diffMin = ($price['price_partner'] - $item->original_priceMin)/1000;

                                                            $diffPrice = [];

                                                            if($diff < 0){
                                                                array_push($diffPrice, number_format($diff) . 'k');
                                                            }else{
                                                                array_push($diffPrice, '+' . number_format($diff) . 'k');
                                                            }

                                                            if($diffMin != $diff){
                                                                if($diff < 0){
                                                                    array_push($diffPrice, number_format($diffMin) . 'k');
                                                                }else{
                                                                    array_push($diffPrice, '+' . number_format($diffMin) . 'k');
                                                                }
                                                            }

                                                            echo '<p style="margin:0;white-space: nowrap"><i style="color:red">' . implode('</i>|<i style="color:red">', $diffPrice) . '</i></p>';

                                                            echo '</a>';
                                                            if($price['price_sale'] != ""){
//                                                                echo '<p class="line-clamp l1 m0" title="' . $price['price_sale'] . '">' . $price['price_sale'] . '</p>';
                                                            }
                                                            unset($prices[$k]);
                                                            $exits++;
                                                        }
                                                    }

                                                    if($exits == 0){
                                                         echo '-';
                                                    }
                                                }else{
                                                    echo '-';
                                                }
 @endphp
                                            </td>
                                        @endforeach

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
