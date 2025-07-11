<!DOCTYPE html>
<html>
<head>
    <title>Yêu cầu hoàn hàng đã được duyệt</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; background-color: #e6f4ea; margin-bottom: 20px; }
        .content { padding: 20px; background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; }
        .order-details { margin: 20px 0; }
        .order-item { padding: 10px; border-bottom: 1px solid #dee2e6; }
        .footer { text-align: center; margin-top: 20px; padding: 20px; background-color: #f8f9fa; font-size: 12px; color: #6c757d; }
        .success { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Yêu cầu hoàn hàng đã được duyệt</h1>
        </div>
        <div class="content">
            <p>Xin chào {{ $order->user->username ?? $order->user->name ?? $order->user->email }},</p>
            <p>
                Yêu cầu hoàn hàng cho đơn hàng <strong>#{{ $order->id }}</strong> của bạn đã được 
                <span class="success">duyệt thành công</span> bởi quản trị viên.
            </p>
            <p><strong>Mã đơn hàng:</strong> #{{ $order->id }}<br>
            <strong>Trạng thái mới:</strong> Đã duyệt hoàn hàng</p>
            <div class="order-details">
                <h2>Chi tiết sản phẩm hoàn hàng:</h2>
                @foreach($order->orderDetails as $item)
                <div class="order-item">
                    @if(isset($item->variant->product->mainImage) && isset($item->variant->product->mainImage->image_path))
                    <img src="{{ $item->variant->product->mainImage->image_path }}" alt="Ảnh sản phẩm" style="max-width: 120px; margin-top: 8px;">
                    @endif
                    <p><strong>{{ $item->variant->product->name }}</strong></p>
                    <p>Phân loại: {{ $item->variant->color }} - {{ $item->variant->size }}</p>
                    <p>Số lượng: {{ $item->quantity }}</p>
                </div>
                @endforeach
            </div>
            <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi để được hỗ trợ.</p>
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 