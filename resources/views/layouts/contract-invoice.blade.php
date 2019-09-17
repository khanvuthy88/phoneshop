<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{ $generalSetting->site_title }} @hasSection('title') - @endif @yield('title')</title>
    <meta charset="utf-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}" sizes="32x32">
    <link href="https://fonts.googleapis.com/css?family=Battambang|Moul" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <style type="text/css">
        .invoice{
            margin: 0 auto;
            width: 700px;
            padding: 10px;
        }
        .invoice h1{
            font-family: 'Moul', Arial, cursive;
            font-weight: normal;
            font-size: 18px;
            line-height: 24px;
            text-align: center;
        }
        table, td{
            font-size: 12px;
            font-family: 'Battambang', Arial, cursive;
            line-height: 20px;
            font-weight: normal;
        }
        
        .invoice p{
            font-family: 'Battambang', Arial, cursive;
            line-height: 18px;
        }
        
        .services{
            margin-top: 0;
            font-size: 13px;
            font-family: 'Moul', 'Arial Black', sans-serif;
            /*font-weight:normal;*/
            text-align: center;
        }
        
        .render table, .render td, .render th {
            padding: 5px;
            font-family: 'Battambang', Arial, cursive;
            font-weight: normal;
            font-size: 11px;
        }
        
        .render td, .render th{
            border: 1px solid #CCC;
        }
        
        .render table{
            width: 100%;
            margin-top:10px;
        }
        
        .render .border-0{
            border:0;
        }
        
        @media print {
            body{
                width: 21cm;
                height: 29.7cm;
                margin: 10mm;
                font-family: 'Battambang', Arial, cursive;
           } 
        }
        
        body{
            font-family: 'Battambang', Arial, cursive;
        }
        
        .header {
            margin-left: 0;
            padding-left: 0px;
        }
        
        .logo-wrapper {
            margin-right: 0;
            padding-right: 0;
        }
        
        .logo {
            width: 90px;
            position: absolute;
            left: 50%;
        }
        
        .banner {
            height: 32px;
            text-align: center;
        }
        
        @media (max-width: 575.98px) {
            .header {
                padding-left: 10px;
            }
            
            .logo {
                left: 40%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="row">
            <div class="col-xs-2 col-sm-2 text-right logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" class="logo">
            </div>
            <div class="col-xs-10 col-sm-10 header">
                <h1>ខូវ ឡៃភឿន ១៦៨ លក់ទូរស័ព្ទដៃទំនើប ផ្សារបាង្កសី</h1>
                <h3 class="services">
                    សេវាបង់រំលោះ  លេខទូរស័ព្ទ 09​9 99 3006 / 010 99 3006 / 070 99 3006
                </h3>
                <div class="banner">
                    <img src="{{ asset('images/phones.png') }}" class="banner">
                </div>
            </div>
        </div>
        <br>
        @yield('content')
    </div>
</body>
</html>
