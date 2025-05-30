<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Chào mừng bạn đến với [Tên dịch vụ]</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f7ff;
            margin: 0;
            padding: 0;
            color: #2d3748;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(135deg, #6b46c1 0%, #805ad5 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .steps {
            margin: 25px 0;
        }
        .step {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
        }
        .step-number {
            background: #6b46c1;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #6b46c1 0%, #805ad5 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 25px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #718096;
            background: #f5f7ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Chào mừng bạn đến với DEVGANG</h1>
            <p>Tài khoản của bạn đã được tạo thành công</p>
        </div>
        
        <div class="content">
            <p>Xin chào <span style="font-weight: bold;">{{ $user->username }}</span>,</p>
            <p>Cảm ơn bạn đã đăng ký tài khoản tại DEVGANG. Để bắt đầu sử dụng dịch vụ, vui lòng xác nhận email của bạn.</p>
            
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div>Bạn đã đăng ký thành công với email: <strong>{{ $user->email }}</strong></div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div>Cảm ơn bạn đã đăng ký tài khoản</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div>Bắt đầu khám phá các tính năng của chúng tôi</div>
                </div>
            </div>
            
            <center><a href="{{ env('FRONTEND_URL') }}" class="button">MUA SẮM NGAY</a></center>
            
            <p>Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email hoặc <a href="#">liên hệ hỗ trợ</a>.</p>
        </div>
        
        <div class="footer">
            <p>© 2025 DEVGANG. All rights reserved.</p>
            <p>Bạn nhận được email này vì đã đăng ký trên website của chúng tôi.</p>
        </div>
    </div>
</body>
</html>