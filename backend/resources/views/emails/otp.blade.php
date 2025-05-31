<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mã OTP đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .otp-code {
            background: #f8f9fa;
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Xin chào {{ $user->username ?? 'bạn' }}!</h2>
    
    <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình.</p>
    
    <p>Mã OTP của bạn là:</p>
    
    <div class="otp-code">
        {{ $otp }}
    </div>
    
    <div class="warning">
        <strong>Lưu ý:</strong>
        <ul>
            <li>Mã OTP này sẽ hết hạn sau 10 phút</li>
            <li>Vui lòng không chia sẻ mã này với bất kỳ ai</li>
            <li>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này</li>
        </ul>
    </div>
    
    <p>Trân trọng,<br>{{ config('app.name') }}</p>
</body>
</html>