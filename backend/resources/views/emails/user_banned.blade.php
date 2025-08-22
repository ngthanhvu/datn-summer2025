<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo tài khoản bị khóa - DEVGANG</title>
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
        
        .notification-section {
            background: #f0f8ff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #cce5ff;
        }
        
        .notification-title {
            font-size: 20px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 10px;
        }
        
        .customer-name-highlight {
            color: #1e293b;
            font-weight: 700;
        }
        
        .notification-description {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .reason-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e6f0f8;
        }
        
        .reason-title {
            font-size: 18px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 15px;
        }
        
        .reason-list {
            list-style: none;
        }
        
        .reason-list li {
            padding: 6px 0;
            color: #374151;
            padding-left: 20px;
            position: relative;
        }
        
        .reason-list li::before {
            content: '•';
            color: #81aacc;
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .contact-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border: 1px solid #e9ecef;
        }
        
        .contact-title {
            font-size: 18px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 15px;
        }
        
        .simple-contact-info {
            background: white;
            border-radius: 6px;
            padding: 15px;
            border: 1px solid #dee2e6;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-size: 14px;
            color: #000000;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 14px;
            color: #81aacc;
            font-weight: 500;
        }
        
        .email-link {
            color: #0066cc !important;
            text-decoration: underline;
        }
        
        .message-content {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
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
            margin-bottom: 10px;
        }
        
        .action-description {
            color: #5a8bb8;
            font-size: 16px;
            margin-bottom: 20px;
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
            .notification-title, .action-title { font-size: 18px; }
            .reason-title, .contact-title { font-size: 16px; }
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header-area">
            <div class="brand-logo">DEVGANG</div>
            <div class="header-title">Thông báo quan trọng</div>
            <div class="header-subtitle">Về tài khoản của bạn</div>
        </div>
        
        <div class="main-content">
            <div class="notification-section">
                <div class="notification-title">
                    Xin chào <span class="customer-name-highlight">{{ $user->name }}</span>!
                </div>
                <div class="notification-description">
                    Chúng tôi rất tiếc phải thông báo rằng tài khoản của bạn đã bị khóa khỏi hệ thống của chúng tôi.
                </div>
            </div>
            
            <div class="reason-section">
                <div class="reason-title">Lý do tài khoản bị khóa:</div>
                <ul class="reason-list">
                    <li>{{ $reason }}</li>
                </ul>
            </div>

            <div class="contact-details">
                <div class="contact-title">Thông tin tài khoản bị khóa</div>
                <div class="simple-contact-info">
                    <div class="info-row">
                        <span class="info-label">Họ tên:</span>
                        <span class="info-value">{{ $user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value email-link">{{ $user->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày đăng ký:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ID tài khoản:</span>
                        <span class="info-value">{{ $user->id }}</span>
                    </div>
                </div>
                
                <div class="message-content">
                    <strong>Thông tin bổ sung:</strong><br>
                    Tài khoản của bạn đã bị khóa do phát hiện các hoạt động vi phạm. Vui lòng xem lại lý do bên trên để hiểu rõ hơn.
                </div>
            </div>
            
            <div class="action-section">
                <div class="action-title">Bạn muốn khôi phục tài khoản?</div>
                <div class="action-description">
                    Nếu bạn cho rằng đây là một nhầm lẫn hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi qua email hỗ trợ hoặc gửi yêu cầu khôi phục tài khoản.
                </div>
            </div>
        </div>
        
        <div class="footer-area">
            <div class="footer-title">Cảm ơn bạn đã hiểu</div>
            <div class="footer-description">
                Chúng tôi luôn cố gắng duy trì một môi trường an toàn và công bằng cho tất cả người dùng. Nếu có bất kỳ thắc mắc nào, đừng ngần ngại liên hệ với chúng tôi.
            </div>
            <div class="footer-divider"></div>
            <div class="footer-bottom">
                © 2025 DEVGANG. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>