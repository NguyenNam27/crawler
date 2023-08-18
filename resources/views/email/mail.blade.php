<!DOCTYPE html>
<html>
<head>
    <title>Sản phẩm thay đổi giá</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>
<body>
<h2>Kết quả so sánh ngày
@php
    $page = $_GET['page'] ?? 1;
    echo date('d/m/Y') . ' ('.$tableData->count().' kết quả)';
@endphp
</h2>
<div class="box-body">
    <table class="table table-border">
        <tr>
            <th style="width:10px">STT</th>
            <th>Ngày tháng</th>
            <th>DM/Thương hiệu</th>
            <th>Kết quả </th>
        </tr>
        @foreach($cate as $key=> $item)
            <tr class="item">
                <td><h3> {{ $key + 1 }} </h3></td>
                <td>
                        @php
                            echo date('d/m/Y');
                        @endphp
                </td>
                <td>
                    {{$item}}
                </td>
                <td>
                  {{ $categoryCountsTemp[$item] }} kết quả
                </td>
            </tr>
        @endforeach
    </table>
<h2> Chi tiết xem tại: <a href="#">Click Here</a>  </h2>
</div>
<p>Thank you</p>
</body>
</html>
