<!DOCTYPE html>
<html>
<head>
    <title>{{$title}}</title>
</head>
<body>
<h2>{{$password_reset_request}}</h2>
<p>{{$click_link_to_reset_password}}</p>
<a href="{{ $url }}">{{$reset_password}}</a>
<p>{{$link_expiration_notice}}</p>
</body>
</html>
