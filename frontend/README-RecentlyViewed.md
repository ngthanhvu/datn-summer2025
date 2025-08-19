# Tính năng Sản phẩm đã xem gần đây

## Tổng quan
Tính năng "Sản phẩm đã xem gần đây" giúp người dùng dễ dàng quay lại những sản phẩm họ đã xem trước đó, tăng trải nghiệm mua sắm và khả năng chuyển đổi.

## Cách hoạt động

### 1. Tự động lưu sản phẩm đã xem
- Khi người dùng xem chi tiết sản phẩm, sản phẩm sẽ tự động được thêm vào danh sách đã xem
- Dữ liệu được lưu trong localStorage của trình duyệt
- Tối đa 12 sản phẩm được lưu trữ

### 2. Hiển thị trên trang chủ
- Component `RecentlyViewed.vue` hiển thị trong trang chủ (`/`)
- Chỉ hiển thị khi có sản phẩm đã xem
- Sắp xếp theo thời gian xem gần nhất

### 3. Quản lý danh sách
- Người dùng có thể xóa từng sản phẩm khỏi danh sách
- Có thể xóa tất cả sản phẩm đã xem
- Tự động cập nhật thời gian xem khi xem lại sản phẩm

## Cấu trúc file

### Components
- `src/components/home/RecentlyViewed.vue` - Component hiển thị sản phẩm đã xem

### Composables
- `src/composable/useRecentlyViewed.js` - Logic quản lý sản phẩm đã xem

### Pages
- `src/pages/detail.vue` - Tự động thêm sản phẩm vào danh sách đã xem
- `src/pages/index.vue` - Hiển thị component RecentlyViewed

## Cách sử dụng

### 1. Thêm sản phẩm vào danh sách đã xem
```javascript
import { useRecentlyViewed } from '../composable/useRecentlyViewed';

const { addToRecentlyViewed } = useRecentlyViewed();

// Thêm sản phẩm khi người dùng xem
addToRecentlyViewed(product);
```

### 2. Kiểm tra sản phẩm đã xem
```javascript
import { useRecentlyViewed } from '../composable/useRecentlyViewed';

const { isRecentlyViewed } = useRecentlyViewed();

// Kiểm tra xem sản phẩm có trong danh sách không
const isViewed = isRecentlyViewed(productId);
```

### 3. Lấy danh sách sản phẩm đã xem
```javascript
import { useRecentlyViewed } from '../composable/useRecentlyViewed';

const { getRecentlyViewed } = useRecentlyViewed();

// Lấy tất cả sản phẩm đã xem
const viewedProducts = getRecentlyViewed();
```

## Tính năng

### 1. Hiển thị thông tin sản phẩm
- Tên sản phẩm
- Hình ảnh
- Giá (hiện tại và gốc)
- Đánh giá và số lượng review
- Thời gian xem gần nhất

### 2. Tương tác
- Xem chi tiết sản phẩm
- Thêm vào giỏ hàng
- Xóa khỏi danh sách đã xem
- Xóa tất cả sản phẩm đã xem

### 3. Responsive Design
- Grid layout thích ứng với mọi kích thước màn hình
- Mobile-first approach
- Smooth animations và transitions

## Dữ liệu lưu trữ

### Cấu trúc dữ liệu
```javascript
{
    id: number,                    // ID sản phẩm
    name: string,                  // Tên sản phẩm
    slug: string,                  // Slug sản phẩm
    price: number,                 // Giá hiện tại
    original_price: number,        // Giá gốc
    image: string,                 // URL hình ảnh
    rating: number,                // Điểm đánh giá
    review_count: number,          // Số lượng review
    viewedAt: string              // Thời gian xem (ISO string)
}
```

### LocalStorage
- Key: `recentlyViewedProducts`
- Dữ liệu được tự động lưu và tải
- Xử lý lỗi gracefully

## Tùy chỉnh

### 1. Thay đổi số lượng tối đa
```javascript
// Trong useRecentlyViewed.js
const MAX_RECENTLY_VIEWED = 20; // Thay đổi từ 12 thành 20
```

### 2. Thay đổi thời gian hiển thị
```javascript
// Trong RecentlyViewed.vue, method formatViewTime
const diffInDays = Math.floor(diffInHours / 24);
if (diffInDays < 30) return `${diffInDays} ngày trước`; // Thay đổi từ 7 thành 30
```

### 3. Thay đổi vị trí hiển thị
```vue
<!-- Trong index.vue, di chuyển component -->
<RecentlyViewed />
<BestSellers />
<NewProducts />
```

## Lưu ý

### 1. Performance
- Dữ liệu được lưu trong localStorage (client-side)
- Không gọi API để lưu/tải sản phẩm đã xem
- Tự động giới hạn số lượng sản phẩm

### 2. Privacy
- Dữ liệu chỉ lưu trên thiết bị của người dùng
- Không gửi lên server
- Tự động xóa khi xóa cache trình duyệt

### 3. Browser Support
- Hỗ trợ tất cả trình duyệt hiện đại
- Fallback cho trường hợp localStorage không khả dụng
- Error handling cho các trường hợp lỗi

## Troubleshooting

### 1. Sản phẩm không hiển thị
- Kiểm tra localStorage có được bật không
- Kiểm tra console có lỗi JavaScript không
- Xác nhận sản phẩm đã được thêm vào danh sách

### 2. Dữ liệu bị mất
- Kiểm tra localStorage có bị xóa không
- Kiểm tra có lỗi JavaScript khi lưu dữ liệu không
- Xác nhận dữ liệu được lưu đúng format

### 3. Performance issues
- Giảm số lượng sản phẩm tối đa
- Tối ưu hóa hình ảnh
- Sử dụng lazy loading cho hình ảnh

## Tương lai

### 1. Tính năng có thể thêm
- Đồng bộ với server (nếu có user account)
- Phân tích hành vi người dùng
- Gợi ý sản phẩm tương tự
- Export/Import danh sách

### 2. Cải tiến UI/UX
- Dark mode support
- Customizable layout
- Advanced filtering
- Search trong danh sách đã xem
