# Cấu hình Environment Variables

## GHN API Configuration

### Backend (.env)
```env
# GHN API Configuration
GHN_API_TOKEN=your_ghn_api_token_here
GHN_SHOP_ID=5929573
GHN_BASE_URL=https://online-gateway.ghn.vn/shiip/public-api/v2
```

### Frontend (.env)
```env
# API Base URL
VITE_API_BASE_URL=http://localhost:8000
```

## Cách lấy GHN API Token và Shop ID

1. **Đăng ký tài khoản** tại [GHN Developer Portal](https://dev-online.ghn.vn/)
2. **Tạo ứng dụng mới** trong dashboard
3. **Lấy API Token** từ dashboard
4. **Lấy Shop ID** từ thông tin shop của bạn
5. **Thêm vào file `.env`** của backend

## Cách hệ thống hoạt động

### Backend
- **Shop ID** được lấy từ `GHN_SHOP_ID` trong file `.env`
- **Thông tin shop** (district_id, ward_code, address) được lấy động từ GHN API
- Không hardcode bất kỳ thông tin shop nào

### Frontend  
- **Shop location** được lấy từ API `/api/shipping/config`
- **Tính phí vận chuyển** sử dụng thông tin shop thực tế từ API
- Có fallback nếu không lấy được thông tin từ API

## Lưu ý bảo mật
- Không commit file `.env` lên git
- Thêm `.env` vào `.gitignore`
- Sử dụng environment variables thay vì hardcode sensitive data
- Rotate API tokens định kỳ

## Test API
Sau khi cấu hình, test API bằng cách:

```bash
# Test lấy thông tin shop
curl -X GET "http://localhost:8000/api/shipping/config" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Test tính phí vận chuyển
curl -X POST "http://localhost:8000/api/shipping/calculate-cart-fee" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "to_district_id": 1820,
    "to_ward_code": "030712",
    "cart_items": [
      {
        "product_id": 1,
        "quantity": 2
      }
    ]
  }'
```

## Troubleshooting

### Nếu không lấy được thông tin shop
1. Kiểm tra `GHN_API_TOKEN` có đúng không
2. Kiểm tra `GHN_SHOP_ID` có tồn tại không
3. Kiểm tra kết nối internet
4. Xem logs trong Laravel để debug

### Nếu tính phí vận chuyển bị lỗi
1. Kiểm tra thông tin shop có đầy đủ không
2. Kiểm tra district_id và ward_code có đúng không
3. Kiểm tra GHN API có hoạt động không 