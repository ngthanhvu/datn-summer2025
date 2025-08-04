<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng bạn đến với DEVGANG</title>
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
            max-width: 700px;
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
        
        .steps-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #e6f0f8;
        }
        
        .steps-title {
            font-size: 18px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .step {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .step-number {
            background: #81aacc;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
            font-weight: 600;
        }
        
        .step-content {
            color: #374151;
            font-size: 14px;
        }
        
        .step-content strong {
            color: #2a6a98;
        }
        
        .action-section {
            background: #f0f8ff;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid #cce5ff;
            text-align: center;
        }
        
        .action-title {
            font-size: 20px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 15px;
        }
        
        .action-button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #81aacc 0%, #5a8bb8 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 15px 0;
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(129, 170, 204, 0.3);
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
        
        .warning-text {
            color: #856404;
            font-size: 14px;
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
            .greeting-title, .action-title { font-size: 18px; }
            .steps-title { font-size: 16px; }
            .step {
                flex-direction: column;
                text-align: center;
            }
            .step-number {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header-area">
            <div class="brand-logo">DEVGANG</div>
            <div class="header-title">Chào mừng bạn đến với DEVGANG</div>
            <div class="header-subtitle">Tài khoản của bạn đã được tạo thành công</div>
        </div>
        
        <div class="main-content">
            <div class="greeting-section">
                <div class="greeting-title">
                    Xin chào <span style="font-weight: bold;">{{ $user->username }}</span>!
                </div>
                <div class="greeting-description">
                    Cảm ơn bạn đã đăng ký tài khoản tại DEVGANG. Để bắt đầu sử dụng dịch vụ, vui lòng xác nhận email của bạn.
                </div>
            </div>
            
            <div class="steps-section">
                <div class="steps-title">Quá trình đăng ký của bạn</div>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        Bạn đã đăng ký thành công với email: <strong>{{ $user->email }}</strong>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        Cảm ơn bạn đã đăng ký tài khoản
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        Bắt đầu khám phá các tính năng của chúng tôi
                    </div>
                </div>
            </div>
            
            <div class="action-section">
                <div class="action-title">Bắt đầu mua sắm ngay</div>
                <a href="{{ env('FRONTEND_URL') }}" class="action-button">MUA SẮM NGAY</a>
            </div>
            
            <div class="warning-section">
                <div class="warning-title">Lưu ý quan trọng</div>
                <div class="warning-text">
                    Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email hoặc <a href="#" style="color: #0066cc;">liên hệ hỗ trợ</a>.
                </div>
            </div>
        </div>
        
        <div class="footer-area">
            <div class="footer-title">Cảm ơn bạn đã tin tưởng</div>
            <div class="footer-description">
                Chúng tôi luôn cố gắng cung cấp dịch vụ tốt nhất cho khách hàng. Mọi phản hồi của bạn đều rất quan trọng với chúng tôi.
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                © 2025 DEVGANG. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>