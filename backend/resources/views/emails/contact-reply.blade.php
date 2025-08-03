<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Phản hồi liên hệ từ DEVGANG</title>
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
        }
        
        .email-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4, #10b981);
        }
        
        .header-area {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 40px;
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
            color: #bfdbfe;
        }
        
        .main-content {
            padding: 40px;
        }
        
        .greeting-section {
            background: #eff6ff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid #bfdbfe;
        }
        
        .greeting-title {
            font-size: 20px;
            font-weight: 600;
            color: #1d4ed8;
            margin-bottom: 10px;
        }
        
        .customer-name-highlight {
            color: #1e293b;
            font-weight: 700;
        }
        
        .greeting-description {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .message-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }
        
        .message-title {
            font-size: 18px;
            font-weight: 600;
            color: #1d4ed8;
            margin-bottom: 15px;
        }
        
        .customer-message {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3b82f6;
        }
        
        .admin-reply {
            background: #f0fdf4;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #10b981;
        }
        
        .message-content {
            color: #374151;
            font-size: 15px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .customer-message strong,
        .admin-reply strong {
            color: #000000;
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
            color: #7c3aed;
            font-weight: 500;
        }
        
        .email-link {
            color: #0066cc !important;
            text-decoration: underline;
        }
        
        .action-section {
            background: #f0fdf4;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid #bbf7d0;
            text-align: center;
        }
        
        .action-title {
            font-size: 20px;
            font-weight: 600;
            color: #059669;
            margin-bottom: 10px;
        }
        
        .action-description {
            color: #047857;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .footer-area {
            background: #1e293b;
            color: white;
            padding: 40px;
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
            background: #475569;
            margin: 20px 0;
        }
        
        .footer-bottom {
            color: #94a3b8;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .email-wrapper { margin: 10px; }
            .header-area, .main-content, .footer-area { padding: 20px; }
            .brand-logo { font-size: 28px; }
            .header-title { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header-area">
            <div class="brand-logo">DEVGANG</div>
            <div class="header-title">Phản hồi liên hệ</div>
            <div class="header-subtitle">Chúng tôi đã phản hồi yêu cầu của bạn</div>
        </div>
        
        <div class="main-content">
            <div class="greeting-section">
                <div class="greeting-title">
                    Xin chào <span class="customer-name-highlight">{{ $contact->name }}</span>!
                </div>
                <div class="greeting-description">
                    Cảm ơn bạn đã liên hệ với chúng tôi. Dưới đây là phản hồi chi tiết từ đội ngũ hỗ trợ của DEVGANG.
                </div>
            </div>
            
            <div class="message-section">
                <div class="message-title">Nội dung trao đổi</div>
                
                <div class="customer-message">
                    <strong>Tin nhắn của bạn:</strong><br>
                    <div class="message-content">{{ $contact->message }}</div>
                </div>
                
                <div class="admin-reply">
                    <strong>Phản hồi từ DEVGANG:</strong><br>
                    <div class="message-content">{{ $adminReply }}</div>
                </div>
            </div>
            
            <div class="contact-details">
                <div class="contact-title">Thông tin liên hệ của bạn</div>
                <div class="simple-contact-info">
                    <div class="info-row">
                        <span class="info-label">Họ tên:</span>
                        <span class="info-value">{{ $contact->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value email-link">{{ $contact->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-value">{{ $contact->phone }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày gửi:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($contact->created_at)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="action-section">
                <div class="action-title">Bạn cần hỗ trợ thêm?</div>
                <div class="action-description">
                    Nếu bạn cần hỗ trợ thêm hoặc có câu hỏi khác, đừng ngần ngại liên hệ với chúng tôi.
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