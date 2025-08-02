<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{$title}}</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
<table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <tr>
        <td style="padding: 30px; text-align: center;">
            <h2 style="color: #333333;">{{$password_reset_request}}</h2>
            <p style="font-size: 16px; color: #555555;">
                {{$click_link_to_reset_password}}
            </p>
            <div style="margin: 20px auto; font-size: 24px; font-weight: bold; color: #2c3e50; background-color: #ecf0f1; padding: 15px 20px; display: inline-block; border-radius: 6px;">
                <a href="{{ $url }}">{{$reset_password}}</a>
            </div>
            <p style="font-size: 14px; color: #777777; margin-top: 30px;">
                {{$link_expiration_notice}}
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding: 20px; text-align: center; font-size: 12px; color: #999999;">
            © {{now()->year}} LangCards. All rights reserved.
        </td>
    </tr>
</table>
</body>
</html>
