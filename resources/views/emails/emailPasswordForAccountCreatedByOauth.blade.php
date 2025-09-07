<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Данные от аккаунта</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
<table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <tr>
        <td style="padding: 30px; text-align: center;">
            <h3 style="color: #333333;">Добро пожаловать на портал LangCards</h3>
            <h2 style="color: #333333;">Ваш аккаунт был успешно создан с использованием {{$provider}}</h2>
            <p style="font-size: 16px; color: #555555;">
                Вы также можете использовать следующие данные для входа в аккаунт с использованием email - адреса и пароля
            </p>
            <p style="font-size: 16px; color: #555555; font-weight: bold;">
                Email - адрес
            </p>
            <div style="margin: 8px; font-size: 24px; font-weight: bold; color: #2c3e50; background-color: #ecf0f1; padding: 15px 20px; display: inline-block; border-radius: 6px;">
                {{$email}}
            </div>
            <p style="font-size: 16px; color: #555555; font-weight: bold;">
                Пароль
            </p>
            <div style="margin: 8px; font-size: 24px; font-weight: bold; color: #2c3e50; background-color: #ecf0f1; padding: 15px 20px; display: inline-block; border-radius: 6px;">
                {{$password}}
            </div>
            <p style="font-size: 14px; color: #777777; margin-top: 30px;">
               Вы можете изменить пароль в настройках профиля
            </p>
            <p style="font-size: 14px; color: #777777; margin-top: 30px;">
               Спасибо за использование платформы
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
