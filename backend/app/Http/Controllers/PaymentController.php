<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use App\Http\Controllers\OrdersController;

class PaymentController extends Controller
{
    public function generateVnpayUrl(Request $request)
    {
        $orderData = $request->input('order_data');
        if (!$orderData) {
            return response()->json(['success' => false, 'message' => 'Thiếu order_data'], 400);
        }
        $amount = $orderData['final_price'] ?? null;
        if (!$amount) {
            return response()->json(['success' => false, 'message' => 'Thiếu amount'], 400);
        }
        $orderId = 'TMP' . time();

        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = env('APP_URL') . "/api/payment/vnpay-callback";

        $vnp_TxnRef = $orderId;
        $vnp_OrderInfo = json_encode($orderData);
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
            'order_data' => 'required|array',
        ]);
        $orderData = $request->input('order_data');
        $amount = $orderData['final_price'] ?? null;
        if (!$amount) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu amount trong order_data'
            ], 400);
        }
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $endpoint = env('MOMO_URL', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $returnUrl = env('APP_URL') . "/api/payment/momo-callback";
        $random = rand(10000, 99999);
        $orderId = 'TMP' . time() . 'MOMOPAY' . $random;
        $orderInfo = "Thanh toán đơn hàng tạm thời";
        $requestId = $partnerCode . time();
        $requestType = "payWithCC";
        $extraData = json_encode($orderData);
        $rawSignature = "accessKey=$accessKey&amount=$amount&extraData=$extraData"
            . "&ipnUrl=$returnUrl&orderId=$orderId&orderInfo=$orderInfo"
            . "&partnerCode=$partnerCode&redirectUrl=$returnUrl&requestId=$requestId"
            . "&requestType=$requestType";
        $signature = hash_hmac("sha256", $rawSignature, $secretKey);
        $payload = [
            "partnerCode" => $partnerCode,
            "accessKey" => $accessKey,
            "requestId" => $requestId,
            "amount" => $amount,
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

    public function vnpayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHashType']);
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = '';
        foreach ($inputData as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . urlencode($key) . "=" . urlencode($value);
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash === $vnp_SecureHash) {
            $responseCode = $inputData['vnp_ResponseCode'];
            if ($responseCode === "00") {
                $orderData = json_decode($inputData['vnp_OrderInfo'] ?? '', true);
                if (!$orderData) {
                    return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thiếu dữ liệu đơn hàng'));
                }
                $userId = $orderData['user_id'] ?? null;
                if (!$userId) {
                    return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thiếu user_id'));
                }
                $order = OrdersController::createOrderFromDataWithProcessing($orderData, $userId);
                $redirectUrl = env('FRONTEND_URL') . '/status?status=success&orderId=' . $order->id . '&amount=' . $order->final_price;
                if ($order->tracking_code) {
                    $redirectUrl .= '&tracking_code=' . urlencode($order->tracking_code);
                }
                return redirect()->to($redirectUrl);
            } else {
                return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thanh toán thất bại'));
            }
        }
        return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Xác thực thanh toán thất bại'));
    }

    public function momoCallback(Request $request)
    {
        $data = $request->all();
        $secretKey = env('MOMO_SECRET_KEY');
        $accessKey = env('MOMO_ACCESS_KEY');
        if (!isset($data['orderId']) || !isset($data['resultCode'])) {
            return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Dữ liệu callback không hợp lệ'));
        }
        $orderIdParts = explode('MOMOPAY', $data['orderId']);
        $originalOrderId = $orderIdParts[0];
        $resultCode = $data['resultCode'] ?? '';
        $rawSignature = "accessKey=" . $accessKey .
            "&amount=" . $data['amount'] .
            "&extraData=" . ($data['extraData'] ?? '') .
            "&message=" . ($data['message'] ?? '') .
            "&orderId=" . $data['orderId'] .
            "&orderInfo=" . ($data['orderInfo'] ?? '') .
            "&orderType=" . ($data['orderType'] ?? '') .
            "&partnerCode=" . ($data['partnerCode'] ?? '') .
            "&payType=" . ($data['payType'] ?? '') .
            "&requestId=" . ($data['requestId'] ?? '') .
            "&responseTime=" . ($data['responseTime'] ?? '') .
            "&resultCode=" . $resultCode .
            "&transId=" . ($data['transId'] ?? '');
        $calculatedSignature = hash_hmac('sha256', $rawSignature, $secretKey);
        if ($calculatedSignature === $data['signature']) {
            if ($resultCode == '0') {
                $orderData = json_decode($data['extraData'] ?? '', true);
                if (!$orderData) {
                    return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thiếu dữ liệu đơn hàng'));
                }
                $userId = $orderData['user_id'] ?? null;
                if (!$userId) {
                    return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thiếu user_id'));
                }
                $order = OrdersController::createOrderFromDataWithProcessing($orderData, $userId);
                $redirectUrl = env('FRONTEND_URL') . '/status?status=success&orderId=' . $order->id . '&amount=' . $order->final_price;
                if ($order->tracking_code) {
                    $redirectUrl .= '&tracking_code=' . urlencode($order->tracking_code);
                }
                return redirect()->to($redirectUrl);
            } else {
                return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thanh toán thất bại'));
            }
        }
        return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Xác thực thanh toán thất bại'));
    }

    public function paypalCancel(Request $request)
    {
        session()->forget('paypal_order_data');

        return redirect()->to(env('FRONTEND_URL') . '/status?status=failure&message=' . urlencode('Thanh toán PayPal đã bị hủy'));
    }

    public function handlePayment(Request $request)
    {
        try {
            $order = Orders::findOrFail($request->order_id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'Đơn hàng đã bị hủy',
                    'status' => 'canceled'
                ], 400);
            }

            if ($order->payment_method === 'cod') {
                $order->payment_status = 'pending';
            } else if (in_array($order->payment_method, ['vnpay', 'momo', 'paypal'])) {
                $order->payment_status = 'paid';
            }

            $order->save();

            return response()->json([
                'message' => 'Cập nhật trạng thái thanh toán thành công',
                'status' => $order->payment_status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handlePaymentCallback(Request $request)
    {
        try {
            $order = Orders::findOrFail($request->order_id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'Đơn hàng đã bị hủy',
                    'status' => 'canceled'
                ], 400);
            }

            if ($request->status === 'success') {
                $order->payment_status = 'paid';
            } else {
                $order->payment_status = 'failed';
            }

            $order->save();

            return response()->json([
                'message' => 'Cập nhật trạng thái thanh toán thành công',
                'status' => $order->payment_status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function refundPayment(Request $request)
    {
        try {
            $order = Orders::findOrFail($request->order_id);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'message' => 'Đơn hàng đã bị hủy',
                    'status' => 'canceled'
                ], 400);
            }

            if ($order->payment_status !== 'paid') {
                return response()->json([
                    'message' => 'Không thể hoàn tiền cho đơn hàng chưa thanh toán',
                    'status' => $order->payment_status
                ], 400);
            }

            $order->payment_status = 'refunded';
            $order->save();

            return response()->json([
                'message' => 'Hoàn tiền thành công',
                'status' => $order->payment_status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
