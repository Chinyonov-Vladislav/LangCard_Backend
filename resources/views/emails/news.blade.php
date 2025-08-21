<!DOCTYPE html>
<html lang="{{$langCode}}">
<head>
    <meta charset="UTF-8">
    <title>{{ $news->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: #2563eb;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            color: #333333;
            line-height: 1.6;
        }
        .image {
            text-align: center;
        }
        .image img {
            max-width: 100%;
            border-radius: 8px;
        }
        .button {
            display: inline-block;
            background: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777777;
            padding: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        {{$headerText}}
    </div>
    <div class="content">
        <h2>{{ $news->title }}</h2>

        @if($news->main_image)
            <div class="image">
                <img src="{{ $news->main_image }}" alt="{{$alt_image_news}}">
            </div>
        @endif

        <div style="text-align:center;">
            <a href="{{ url('/news/'.$news->id) }}" class="button">{{$continueButtonText}}</a>
        </div>
    </div>
    <div class="footer">
        {{$footerTextPartOne}}<br>
        {{$footerTextPartTwo}}
    </div>
</div>
</body>
</html>
