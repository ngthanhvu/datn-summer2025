<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu {{ $movement->type === 'import' ? 'Nhập kho' : ($movement->type === 'export' ? 'Xuất kho' : 'Điều chỉnh') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; }
        .info-table td { padding: 2px 8px; }
        .product-table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        .product-table th, .product-table td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        .product-table th { background: #f3f3f3; }
        .sign-row td { text-align: center; padding-top: 30px; }
        .sign-label { font-weight: bold; }
        .italic { font-style: italic; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">HÓA ĐƠN {{ strtoupper($movement->type === 'import' ? 'nhập kho' : ($movement->type === 'export' ? 'xuất kho' : 'điều chỉnh')) }}</div>
            <div>Mã phiếu: {{ strtoupper($movement->type === 'import' ? 'NK' : 'XK') }}-{{ $movement->id }}</div>
        </div>
        <div style="text-align:right">
            <div>Ngày {{ $movement->type === 'import' ? 'nhập' : 'xuất' }}: {{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y') }}</div>
            <div>Người {{ $movement->type === 'import' ? 'nhập' : 'xuất' }}: {{ $movement->user->username ?? 'Không xác định' }}</div>
        </div>
    </div>
    <table class="info-table">
        <tr>
            <td><b>Nhà cung cấp:</b></td>
            <td>...</td>
            <td><b>Số chứng từ:</b></td>
            <td>...</td>
        </tr>
        <tr>
            <td><b>Ghi chú:</b></td>
            <td colspan="3">{{ $movement->note ?? '-' }}</td>
        </tr>
    </table>
    <table class="product-table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Mã hàng</th>
                <th>Tên hàng</th>
                <th>Đơn vị</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $movement->variant->sku }}</td>
                <td>{{ $movement->variant->product->name }}</td>
                <td>{{ $movement->variant->unit ?? '' }}</td>
                <td>{{ $movement->quantity }}</td>
                <td>{{ number_format($movement->variant->price ?? 0) }}</td>
                <td>{{ number_format(($movement->variant->price ?? 0) * $movement->quantity) }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right"><b>Tổng cộng</b></td>
                <td><b>{{ $movement->quantity }}</b></td>
                <td></td>
                <td><b>{{ number_format(($movement->variant->price ?? 0) * $movement->quantity) }}</b></td>
            </tr>
        </tbody>
    </table>
    <div style="margin-top:8px;">Bằng chữ: <span class="italic">{{ number_format(($movement->variant->price ?? 0) * $movement->quantity) }} đồng chẵn</span></div>
    <div style="margin-top:2px;">VAT: 10%</div>
    <table style="width:100%; margin-top:30px;">
        <tr class="sign-row">
            <td class="sign-label">Người lập phiếu<br><span class="italic">(Ký, ghi rõ họ tên)</span></td>
            <td class="sign-label">Người giao hàng<br><span class="italic">(Ký, ghi rõ họ tên)</span></td>
            <td class="sign-label">Thủ kho<br><span class="italic">(Ký, ghi rõ họ tên)</span></td>
        </tr>
    </table>
</body>
</html> 