<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signatory Notification Email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Open Sans', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333333;
        }

        .wrapper {
            width: 100%;
            min-width: 750px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #043B4D;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        .header img {
            width: 150px;
            margin-bottom: 10px;
        }

        .content {
            padding: 20px 50px;
            font-size: 14px;
            line-height: 1.6;
        }

        .content h1 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .content p {
            margin-bottom: 15px;
        }

        .content a {
            color: #fff;
            text-decoration: none;
        }

        .content a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            background-color: #eeeeee;
            padding: 15px;
            font-size: 12px;
            color: #666666;
        }

        .btn {
            display: inline-block;
            background-color: #008CBA;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
        }

        .btn:hover {
            background-color: #005f80;
        }
    </style>
</head>

<body>
    <table class="wrapper">
        <tr>
            <td class="header">
                <img src="{{ URL::to('/assets/img/SD-Logo_Attorneys_Red.png') }}" alt="Strauss Daly Logo" style="width:250px;">
                <h2 style="margin-top:0px;padding-top:0px;">Signatory Notification</h2>
            </td>
        </tr>
        <tr>
            <td class="content">
                <h1>{{ $emailData['greeting'] }}</h1>
                <p>{{ $emailData['message'] }}</p>
                <p>Please follow the link below to authorize:</p>
                <a href="{{ $emailData['url'] }}" class="btn" target="_blank">Authorize Now</a>
                <p>For any issues, please contact us directly.</p>
                <p>Kind regards,<br>{{ $emailData['senderName'] }}</p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p>&copy; {{ date('Y') }} Iconis Pay. All rights reserved.</p>
                <p>
                <a href="{{ $emailData['unsubscribeLink'] }}">Unsubscribe</a> |
                <a href="{{ $emailData['preferencesLink'] }}">Preferences</a>
                </p>
            </td>
        </tr>
    </table>
</body>

</html>
