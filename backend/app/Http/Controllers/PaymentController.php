<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function generateVnpayUrl(Request $request)
    {
        $orderId = $request->input('order_id');
        $amount = $request->input('amount');

        if (!$orderId || !$amount) {
            return response()->json(['success' => false, 'message' => 'Thiếu order_id hoặc amount'], 400);
        }

        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = "https://localhost:8000/api/payment/vnpay-callback";

        $vnp_TxnRef = $orderId;
        $vnp_OrderInfo = "Thanh toán đơn hàng #$vnp_TxnRef";
        $vnp_Amount = $amount * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "250000",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $hashdata = $query . "&vnp_SecureHash=" . hash_hmac('sha512', $query, $vnp_HashSecret);
        $paymentUrl = $vnp_Url . "?" . $hashdata;

        return response()->json([
            'success' => true,
            'payment_url' => $paymentUrl,
        ]);
    }

    public function generateMomoUrl(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'amount' => 'required|numeric|min:1000'
        ]);

        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $endpoint = env('MOMO_URL', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $returnUrl = env('APP_URL') . "/api/payment/momo-callback";

        $random = rand(10000, 99999);
        $orderId = $request->order_id . 'MOMOPAY' . $random;
        $orderInfo = "Thanh toán đơn hàng #" . $request->order_id;
        $requestId = $partnerCode . time();
        $requestType = "payWithATM";
        $extraData = "";

        $rawSignature = "accessKey=$accessKey&amount=$request->amount&extraData=$extraData"
            . "&ipnUrl=$returnUrl&orderId=$orderId&orderInfo=$orderInfo"
            . "&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId"
            . "&requestType=$requestType";

        $signature = hash_hmac("sha256", $rawSignature, $secretKey);

        $payload = [
            "partnerCode" => $partnerCode,
            "accessKey" => $accessKey,
            "requestId" => $requestId,
            "amount" => $request->amount,
            "orderId" => $orderId,
            "orderInfo" => $orderInfo,
            "redirectUrl" => $returnUrl,
            "ipnUrl" => $returnUrl,
            "extraData" => $extraData,
            "requestType" => $requestType,
            "signature" => $signature,
            "lang" => "vi"
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($payload))
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (!empty($result['payUrl'])) {
            return response()->json([
                'success' => true,
                'payment_url' => $result['payUrl']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo URL thanh toán MoMo',
                'response' => $result
            ], 500);
        }
    }

    public function generatePaypalUrl(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'amount' => 'required|numeric|min:1000'
        ]);

        $apiUrl = env('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET');
        $returnUrl = env('APP_URL') . "/api/payment/paypal-callback";
        $cancelUrl = env('APP_URL') . "/api/payment/paypal-cancel";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$apiUrl/v1/oauth2/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Accept-Language: en_US']);
        $response = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($response, true);
        if (empty($tokenData['access_token'])) {
            return response()->json(['success' => false, 'message' => 'Không lấy được access token'], 500);
        }

        $accessToken = $tokenData['access_token'];

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $request->order_id,
                'amount' => [
                    'currency_code' => env('PAYPAL_CURRENCY', 'USD'),
                    'value' => number_format($request->amount / 24000, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => 'My Store',
                'locale' => 'en-US',
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW'
            ]
        ];

        $ch = curl_init("$apiUrl/v2/checkout/orders");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $approveUrl = collect($result['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;

        if ($approveUrl) {
            return response()->json([
                'success' => true,
                'payment_url' => $approveUrl
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy approve URL từ PayPal',
            'response' => $result
        ], 500);
    }
}
