<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Helpers\EnvHelper;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Cache::tags(['settings'])->remember('settings_all', 86400, function () {
            return Setting::all()->pluck('value', 'key');
        });

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        try {
            $data = $request->all();

            foreach ($data as $key => $value) {
                Setting::setValue($key, is_array($value) ? json_encode($value) : $value);
            }

            Cache::tags(['settings'])->flush();

            $envFields = [
                'smtpHost'        => 'MAIL_HOST',
                'smtpPort'        => 'MAIL_PORT',
                'smtpUser'        => 'MAIL_USERNAME',
                'smtpPass'        => 'MAIL_PASSWORD',
                'emailFrom'       => 'MAIL_FROM_ADDRESS',
                'vnpayTmnCode'    => 'VNPAY_TMN_CODE',
                'vnpayHashSecret' => 'VNPAY_HASH_SECRET',
                'vnpayUrl'        => 'VNPAY_URL',
                'momoPartnerCode' => 'MOMO_PARTNER_CODE',
                'momoAccessKey'   => 'MOMO_ACCESS_KEY',
                'momoSecretKey'   => 'MOMO_SECRET_KEY',
                'GHN_BASE_URL'   => 'GHN_BASE_URL',
                'GHN_API_TOKEN'  => 'GHN_API_TOKEN',
                'GHN_SHOP_ID'    => 'GHN_SHOP_ID',
            ];

            $envData = [];
            foreach ($envFields as $dbKey => $envKey) {
                if (isset($data[$dbKey])) {
                    $envData[$envKey] = $data[$dbKey];
                }
            }

            if ($envData) {
                EnvHelper::setEnvValue($envData);
            }

            return response()->json(['message' => 'Cập nhật cài đặt thành công']);
        } catch (\Exception $e) {
            \Log::error('Error updating settings: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi cập nhật cài đặt'], 500);
        }
    }
}
