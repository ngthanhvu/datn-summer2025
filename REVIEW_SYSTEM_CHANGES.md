# Thay đổi hệ thống đánh giá sản phẩm

## Tổng quan
Đã cập nhật hệ thống đánh giá để phù hợp với logic backend, bao gồm:
- Sàng lọc từ khóa tiêu cực tự động
- Quản lý trạng thái duyệt đánh giá
- Ẩn đánh giá vi phạm khỏi frontend

## Thay đổi Backend (ProductReviewController.php)

### 1. API getByProductSlug
- Chỉ trả về đánh giá đã được duyệt (`is_approved = true`) và không bị ẩn (`is_hidden = false`)
- Đảm bảo frontend chỉ hiển thị đánh giá hợp lệ

### 2. Logic tạo đánh giá mới (store)
- Đánh giá chứa từ khóa tiêu cực: tự động ẩn (`is_hidden = true`, `is_approved = false`)
- Đánh giá bình thường: chờ duyệt (`is_approved = false`, `is_hidden = false`)

### 3. Logic cập nhật đánh giá (update)
- Admin thay đổi trạng thái thủ công
- Tự động kiểm tra từ khóa tiêu cực khi cập nhật nội dung
- Chuyển đổi trạng thái phù hợp

## Thay đổi Frontend

### 1. Trang chi tiết sản phẩm ([slug].vue)
- Lọc đánh giá chỉ hiển thị những đánh giá đã được duyệt
- Thông báo cho user về trạng thái đánh giá của họ
- Cập nhật thông báo khi gửi đánh giá

### 2. Trang quản lý đánh giá (admin/comments/index.vue)
- Cập nhật logic hiển thị trạng thái
- Thêm thông báo về hệ thống sàng lọc
- Cải thiện xử lý cập nhật trạng thái

### 3. Component CommentsList
- Cập nhật text hiển thị trạng thái
- Thêm nút thao tác phù hợp với từng trạng thái
- Cải thiện UX cho admin

## Trạng thái đánh giá

### 1. Pending (Chờ duyệt)
- `is_approved = false`
- `is_hidden = false`
- Không hiển thị trên frontend
- Admin có thể duyệt hoặc ẩn

### 2. Approved (Đã duyệt)
- `is_approved = true`
- `is_hidden = false`
- Hiển thị trên frontend
- Admin có thể bỏ duyệt hoặc ẩn

### 3. Rejected (Vi phạm/Ẩn)
- `is_approved = false`
- `is_hidden = true`
- Không hiển thị trên frontend
- Tự động áp dụng khi có từ khóa tiêu cực
- Admin có thể bỏ ẩn

## Từ khóa tiêu cực
Backend đã có sẵn danh sách từ khóa tiêu cực và tự động sàng lọc:
- Đánh giá chứa từ khóa tiêu cực sẽ tự động bị ẩn
- Khi user sửa đánh giá và loại bỏ từ khóa tiêu cực, đánh giá sẽ chuyển về trạng thái chờ duyệt

## Lưu ý
- Đánh giá mới luôn ở trạng thái chờ duyệt (trừ khi có từ khóa tiêu cực)
- Chỉ đánh giá đã duyệt mới hiển thị trên frontend
- Admin có thể quản lý tất cả trạng thái đánh giá
- Hệ thống tự động thông báo cho user về trạng thái đánh giá của họ 