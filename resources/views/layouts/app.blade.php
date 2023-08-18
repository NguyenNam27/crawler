<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title> Phần mềm Crawl Data BTP Holdings </title>
    <base href="{{asset('')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <!-- Fontawesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">
    <input type="checkbox" id="flip">
    <div class="cover">
        <div class="front">
            <img src="https://hrm.btpholdings.vn/img/bg-BTP.35acb40d.jpg" alt="">
            <div class="text">
                <span class="text-1">PHẦN MỀM CRAWL DATA</span>
                <span class="text-2">Let's get connected</span>
            </div>
        </div>
        <div class="back">
            <div class="text">
                <span class="text-1">Complete miles of journey <br> with one step</span>
                <span class="text-2">Let's get started</span>
            </div>
        </div>
    </div>
    <div class="forms">
        <div class="form-content">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
