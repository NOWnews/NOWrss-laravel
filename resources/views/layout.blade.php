<!DOCTYPE html>
<html>
<head>
	<title>Feeds 2018</title>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.1/css/all.css" integrity="sha384-O8whS3fhG2OnA5Kas0Y9l3cfpmYjapjI0E4theH4iuMD+pLhbf6JI0jIMfYcK3yZ" crossorigin="anonymous">
</head>
<body>
<style>
    body, a {
        font-size: 14px !important;
    }
    .container{
        max-width: 100%;
    }
    .container:after {
        background-image: url(../../image/logo.png);
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center top;
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        z-index: -2;
        top: 0;
        left: 0;
    }
    .container:before {
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
	z-index: -1;
	opacity: 0.9;
	background: linear-gradient(to bottom, #ffffff 0%, #fec33f 100%);
    }
</style>

<div class="container mt-1 p-3">
    @yield('content')
</div>
<footer class="main-footer p-3">
    <div class="pull-right hidden-xs">
        <a href="http://www.nownews.com">NOWnews.com.</a> <span>All Rights Reserved</span>.
    </div>
    <strong>今日傳媒(股)公司版權所有，非經授權，不許轉載本網站內容 © 2018 </strong>
</footer>

</body>
</html>
