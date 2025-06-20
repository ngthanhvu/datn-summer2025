<!DOCTYPE html>
<html>
<head>
    <title>Xác nhận thanh toán</title>
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
        }
        .header {
            text-align: center;
            padding: 20px 0;
            background-color: #f8f9fa;
            margin-bottom: 20px;
        }
        .content {
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .order-details {
            margin: 20px 0;
        }
        .order-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Xác nhận thanh toán thành công</h1>
        </div>
        
        <div class="content">
            <p>Xin chào {{ $order->user->name }},</p>
            
            <p>Cảm ơn bạn đã đặt hàng tại cửa hàng của chúng tôi. Đơn hàng của bạn đã được thanh toán thành công.</p>
            
            <div class="order-details">
                <h2>Chi tiết đơn hàng #{{ $order->id }}</h2>
                
                @foreach($order->orderDetails as $item)
                <div class="order-item">
                    <p><strong>{{ $item->variant->product->name }}</strong></p>
                    <p>Phân loại: {{ $item->variant->color }} - {{ $item->variant->size }}</p>
                    <p>Số lượng: {{ $item->quantity }}</p>
                    <p>Đơn giá: {{ number_format($item->price) }} VNĐ</p>
                </div>
                @endforeach
                
                <div class="total">
                    <p>Tổng tiền: {{ number_format($order->final_price) }} VNĐ</p>
                </div>
            </div>
            
            <p>Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p>
            
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} My Store. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 