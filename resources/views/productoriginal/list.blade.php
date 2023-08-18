@extends('layouts.main')
@section('content')
    @php
        $search = $_GET['search'] ?? '';
        $mySiteOption = $_GET['mySiteOption'] ?? '';
    @endphp
    <div class="content-wrapper">

        <section class="content-header">
            <h1>
                QUẢN LÝ SẢN PHẨM BTP NGÀY
                @php
                    $page = $_GET['page'] ?? 1;
                    echo date('d/m/Y') . '('.$listProductOriginal->total().' sản phẩm)';
                @endphp
                <a href="{{route('add-product-original')}}" class="btn bg-purple btn-flat"><i
                        class="fa fa-plus"></i> Thêm sản phẩm</a>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-user"></i> QUẢN LÝ SẢN PHẨM </a></li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">

                            <div class="col-sm-3 col-md-3">
                                <form action="{{route('list-product-original')}}" method="GET" class="navbar-form" name="search">
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
                                    <form action="{{route('list-product-original')}}" style="display: flex;margin-top: 0.7rem">
                                        <select class="form-select form-select-sm " aria-label=".form-select-sm example"
                                                data-url=""
                                                name="mySiteOption">
                                            <option value="">-- Chọn DM/Thương hiệu --</option>
                                            @foreach($cate as $key => $value)
                                                <option value={{ $value }} @if($mySiteOption == $value) selected @endif>{{ $value  }}</option>
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

                            <div class="col-sm-3 col-md-3">
                                <div class="input-group">
                                    <form action="{{route('importProductOrigin')}}" method="POST" enctype="multipart/form-data" style="display:flex;margin-top: 0.8rem;">
                                        @csrf
                                        <input type="file" class="form-control"
                                               name="file" >
                                        <button type="submit" class="btn btn-primary">Import Excel</button>
                                        <!--
                                        <a href="{{route('exportProductOrigin')}}" class="btn btn-primary ">Export Excel </a>
                                        -->
                                    </form>
                                </div>
                            </div>
                            <!--
                            <div class="col-sm-3 col-md-3">
                                <div class="input-group">
                                    <form action="{{route('importJsonfile')}}" method="POST" enctype="multipart/form-data" >
                                        @csrf
                                        <input type="file" class="form-control"
                                               name="jsonfile" >
                                        <button type="submit" class="btn btn-primary">Import Json</button>
                                        <a href="" class="btn btn-primary ">Export Json </a>
                                    </form>
                                </div>
                            </div>
                            -->
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
                                    <th style="width:10px">Mã code</th>
                                    <th >Thương hiệu</th>
                                    <th>Tên</th>
                                    <th>Giá niêm yết</th>
                                    <th>Giá min</th>
                                    <th>Link</th>
                                    <th>Ngày tạo</th>
                                    <th>Tình Trạng</th>
                                    <th>Thao tác</th>
                                </tr>
                                @foreach($listProductOriginal as $key => $item)
                                    <tr class="item-{{ $item->id }}">
                                        <td>{{ ($key + 1 + ($page -1) * 20) }}</td>
                                        <td style="white-space: nowrap">{{ $item->code_product }}</td>
                                        <td>{{ $item->brand }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ number_format($item->price_cost) }}đ</td>
                                        <td>{{ number_format($item->price_min) }}đ</td>
                                        <td><a href="{{ $item->link_product }}"> {{ $item->link_product }} </a></td>

                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ ($item->status==1) ? 'Hiển thị' : 'Không Hiển thị' }}</td>
                                        <td style="display: flex">
                                            <a href="{{URL::to('edit-product-original/'.$item->id)}}"
                                               class="btn btn-warning btn-edit"><i class="fa fa-pencil"></i></a>
                                            <a onclick="return confirm('Bạn có chắc là muốn xóa danh mục này ko?')" class="btn btn-danger btn-delete" href="{{URL::to('delete-product-original/'.$item->id)}}">
                                                <i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                            </table>
                            <div class="box-footer clearfix">
                                                                {{ $listProductOriginal->appends($_GET)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
