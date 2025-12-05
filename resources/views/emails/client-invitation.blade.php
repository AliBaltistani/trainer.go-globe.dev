<!DOCTYPE html>
<html>
<head>
    <title>Invitation to Join {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}</h1>
        </div>
        <div class="content">
            <p>Hello {{ $client->name }},</p>
            
            <p>Your trainer, <strong>{{ $trainer->name }}</strong>, has invited you to join {{ config('app.name') }} to track your fitness journey together.</p>
            
            <p>An account has been created for you. You can login using the following credentials:</p>
            
            <p>
                <strong>Email:</strong> {{ $client->email }}<br>
                @if($password)
                <strong>Password:</strong> {{ $password }}
                @else
                <strong>Password:</strong> <em>(Please use the "Forgot Password" feature to set your password)</em>
                @endif
            </p>
            
            <p>Please click the button below to login and get started:</p>
            
            <a href="{{ config('app.url') }}/login" class="button">Login Now</a>
            
            <p>If you have any questions, please contact your trainer directly.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>