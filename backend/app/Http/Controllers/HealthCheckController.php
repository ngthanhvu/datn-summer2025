<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    public function index()
    {
        $status = [
            'status' => 'ok',
            'time'   => now()->toDateTimeString(),
            'app'    => [
                'name'    => config('app.name'),
                'env'     => config('app.env'),
                'version' => config('app.version', '1.0.0'),
                'debug'   => config('app.debug'),
            ],
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache'    => $this->checkCache(),
                'queue'    => $this->checkQueue(),
                'redis'    => $this->checkRedis(),
                'disk'     => $this->checkDisk(),
                'mail' => $this->checkMail(),
            ],
            'php'      => ['version' => PHP_VERSION],
            'laravel'  => ['version' => app()->version()],
        ];

        foreach ($status['checks'] as $check) {
            if (is_array($check) && ($check['status'] ?? 'ok') !== 'ok') {
                $status['status'] = 'error';
                break;
            }
        }

        return response()->json($status, $status['status'] === 'ok' ? 200 : 500);
    }

    public function view()
    {
        $response = $this->index();
        $data = $response->getData(true); // chuyển JSON thành array

        return view('health', ['data' => $data]);
    }

    private function checkDatabase()
    {
        $start = microtime(true);
        try {
            DB::connection()->getPdo();
            $time = round((microtime(true) - $start) * 1000, 2);
            return ['status' => 'ok', 'ping' => $time . ' ms', 'value' => 'connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache()
    {
        $start = microtime(true);
        try {
            Cache::put('health_check', 'ok', 5);
            $value = Cache::get('health_check');
            $time = round((microtime(true) - $start) * 1000, 2);
            return ['status' => $value === 'ok' ? 'ok' : 'error', 'ping' => $time . ' ms', 'value' => $value];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkQueue()
    {
        $start = microtime(true);
        try {
            $size = Queue::size();
            $time = round((microtime(true) - $start) * 1000, 2);
            return ['status' => 'ok', 'size' => $size, 'ping' => $time . ' ms', 'value' => "$size jobs pending"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkRedis()
    {
        $start = microtime(true);

        try {
            // thử set/get 1 key tạm
            $key = 'health_check:' . uniqid();
            Redis::set($key, 'ok');
            $value = Redis::get($key);
            Redis::del($key);

            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $value === 'ok' ? 'ok' : 'error',
                'ping'   => $time . ' ms',
                'value'  => $value === 'ok' ? 'connected' : 'unexpected response: ' . $value,
            ];
        } catch (\Exception $e) {
            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'status'  => 'error',
                'ping'    => $time . ' ms',
                'value'   => 'disconnected',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function checkDisk()
    {
        $start = microtime(true);
        try {
            $free = disk_free_space("/");
            $total = disk_total_space("/");
            $usedPercent = round((1 - $free / $total) * 100);
            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $usedPercent > 90 ? 'warning' : 'ok',
                // 'free'   => round($free / 1024 / 1024) . ' MB',
                // 'total'  => round($total / 1024 / 1024) . ' MB',
                'ping'   => $time . ' ms',
                'value'  => $usedPercent . '% used'
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkMail()
    {
        $start = microtime(true);

        try {
            $mailer = app('mailer');

            // Nếu dùng log driver thì chỉ ghi log, không gửi thật
            $mailer->raw('Health check mail test', function ($message) {
                $message->to('dummy@example.com')->subject('Health Check');
            });

            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'ok',
                'ping'   => $time . ' ms',
                'value'  => config('mail.default') . ' driver'
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
