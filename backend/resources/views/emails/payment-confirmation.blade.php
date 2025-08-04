<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận thanh toán - DEVGANG</title>
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
        
        .order-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #e6f0f8;
        }
        
        .order-title {
            font-size: 18px;
            font-weight: 600;
            color: #2a6a98;
            margin-bottom: 20px;
        }
        
        .order-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #e9ecef;
        }
        
        .product-name {
            font-weight: 600;
            color: #2a6a98;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .product-details {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 6px;
        }
        
        .product-price {
            color: #81aacc;
            font-weight: 600;
            font-size: 14px;
        }
        
        .total-section {
            background: #f0f8ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #cce5ff;
            text-align: right;
        }
        
        .total-amount {
            font-size: 20px;
            font-weight: 700;
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
            .greeting-title, .action-title { font-size: 18px; }
            .order-title { font-size: 16px; }
            .total-amount { font-size: 18px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header-area">
            <div class="brand-logo">DEVGANG</div>
            <div class="header-title">Xác nhận thanh toán thành công</div>
            <div class="header-subtitle">Đơn hàng của bạn đã được xử lý</div>
        </div>
        
        <div class="main-content">
            <div class="greeting-section">
                <div class="greeting-title">
                    Xin chào <span style="font-weight: bold;">{{ $order->user->name }}</span>!
                </div>
                <div class="greeting-description">
                    Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi. Đơn hàng của bạn đã được thanh toán thành công.
                </div>
            </div>
            
            <div class="order-section">
                <div class="order-title">Chi tiết đơn hàng #{{ $order->id }}</div>
                
                @foreach($order->orderDetails as $item)
                <div class="order-item">
                    <div class="product-name">{{ $item->variant->product->name }}</div>
                    <div class="product-details">Phân loại: {{ $item->variant->color }} - {{ $item->variant->size }}</div>
                    <div class="product-details">Số lượng: {{ $item->quantity }}</div>
                    <div class="product-price">Đơn giá: {{ number_format($item->price) }} VNĐ</div>
                </div>
                @endforeach
                
                <div class="total-section">
                    <div class="total-amount">Tổng tiền: {{ number_format($order->final_price) }} VNĐ</div>
                </div>
            </div>
            
            <div class="action-section">
                <div class="action-title">Chúng tôi sẽ xử lý đơn hàng</div>
                <div class="action-description">
                    Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất. Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.
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