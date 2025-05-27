## Hướng dẫn cài đặt và chạy dự án

### Yêu cầu hệ thống
- Docker và Docker Compose đã được cài đặt
- Composer (nếu muốn chạy composer install trên host)

### Các bước cài đặt
#### 1. Truy cập thư mục backend

```bash
cd backend
```

#### 2. Cài đặt các package PHP bằng Composer

```bash
composer install
```

#### 3. Tạo file .env từ file mẫu

```bash
cp .env.example .env
```

#### 4. Build và chạy container

```bash
docker-compose up -d --build
```

#### 5. Truy cập vào container Laravel để sinh app key

```bash
docker exec -it laravel_api bash
php artisan key:generate
exit
```

#### 6. Thực hiện các lệnh PHP Laravel khác

Để chạy các lệnh Laravel bên trong container:

```bash
docker exec -it laravel_api php artisan <command>
```

Ví dụ:

```bash
docker exec -it laravel_api php artisan migrate
```

### 7. Cài thư viện jwt 
```bash
composer require tymon/jwt-auth
```
#### 8. gen key jwt
```bash
php artisan jwt:secret
```
