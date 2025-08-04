<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP đặt lại mật khẩu</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .email-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #81aacc, #5a8bb8, #3a7aa8, #2a6a98);
        }
        
        .header-area {
            background: linear-gradient(135deg, #81aacc 0%, #5a8bb8 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        
        .brand-logo {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .header-subtitle {
            font-size: 16px;
            color: #e6f0f8;
        }
        
        .main-content {
            padding: 40px 20px;
        }
        
        .greeting-section {
            background: #f0f8ff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #cce5ff;
        }
        
        .greeting-title {
            font-size: 20px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 10px;
        }
        
        .greeting-description {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .otp-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: 20px 0;
            border: 1px solid #e6f0f8;
            text-align: center;
        }
        
        .otp-title {
            font-size: 18px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 20px;
        }
        
        .otp-code {
            background: #f0f8ff;
            border: 2px dashed #81aacc;
            padding: 25px;
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #2a6a98;
            margin: 20px 0;
            border-radius: 8px;
            letter-spacing: 4px;
        }
        
        .warning-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .warning-title {
            font-size: 16px;
            font-weight: 600;
            color: #856404;
            margin-bottom: 10px;
        }
        
        .warning-list {
            list-style: none;
            color: #856404;
        }
        
        .warning-list li {
            padding: 4px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .warning-list li::before {
            content: '•';
            color: #856404;
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .footer-area {
            background: #2a6a98;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .footer-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .footer-description {
            color: #cbd5e1;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .footer-divider {
            height: 1px;
            background: #5a8bb8;
            margin: 20px 0;
        }
        
        .footer-bottom {
            color: #94a3b8;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .email-wrapper { 
                margin: 10px; 
                border-radius: 12px;
            }
            .header-area, .main-content, .footer-area { 
                padding: 20px 15px; 
            }
            .brand-logo { font-size: 28px; }
            .header-title { font-size: 20px; }
            .greeting-title { font-size: 18px; }
            .otp-title { font-size: 16px; }
            .otp-code { 
                font-size: 24px; 
                letter-spacing: 2px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header-area">
            <div class="brand-logo">DEVGANG</div>
            <div class="header-title">Mã OTP đặt lại mật khẩu</div>
            <div class="header-subtitle">Bảo mật tài khoản của bạn</div>
        </div>
        
        <div class="main-content">
            <div class="greeting-section">
                <div class="greeting-title">
                    Xin chào <span style="font-weight: bold;">{{ $user->username ?? 'bạn' }}</span>!
                </div>
                <div class="greeting-description">
                    Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản của mình.
                </div>
            </div>
            
            <div class="otp-section">
                <div class="otp-title">Mã OTP của bạn là:</div>
                <div class="otp-code">
                    {{ $otp }}
                </div>
            </div>
            
            <div class="warning-section">
                <div class="warning-title">Lưu ý quan trọng:</div>
                <ul class="warning-list">
                    <li>Mã OTP này sẽ hết hạn sau 10 phút</li>
                    <li>Vui lòng không chia sẻ mã này với bất kỳ ai</li>
                    <li>Nếu bạn không yêu cầu đặt lại mật khẩu, hãy bỏ qua email này</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-area">
            <div class="footer-title">Bảo mật tài khoản</div>
            <div class="footer-description">
                Chúng tôi luôn cố gắng bảo vệ thông tin tài khoản của bạn. Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi.
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                © 2025 DEVGANG. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>