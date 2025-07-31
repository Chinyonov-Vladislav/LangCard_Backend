<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Your invite code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
<table style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <tr>
        <td style="padding: 30px; text-align: center;">
            <h2 style="color: #333333;">Welcome! Your account has been successfully created on the LangCards portal!</h2>
            <p style="font-size: 16px; color: #555555;">
                Your personal invitation code:
            </p>
            <div style="margin: 20px auto; font-size: 24px; font-weight: bold; color: #2c3e50; background-color: #ecf0f1; padding: 15px 20px; display: inline-block; border-radius: 6px;">
                {{$invite_code}}
            </div>
            <p style="font-size: 14px; color: #777777; margin-top: 30px;">
                Use the code to send it to other users, they will be able to specify it so that we know that it was you who invited them.
            </p>
            <p style="font-size: 14px; color: #777777; margin-top: 30px;">
                Thank you for sharing our platform.
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding: 20px; text-align: center; font-size: 12px; color: #999999;">
            © {{$year}} LangCards. All rights reserved.
        </td>
    </tr>
</table>
</body>
</html>
