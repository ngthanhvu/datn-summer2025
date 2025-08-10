<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\User;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Lấy thống kê tổng quan cho dashboard
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $monthlyRevenue = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'delivered'])
                ->sum('final_price');

            $monthlyOrders = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->count();

            $totalCustomers = User::where('role', 'user')->count();

            $totalProducts = Products::where('is_active', true)->count();

            $ordersByStatus = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            $dailyRevenue = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'delivered'])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(final_price) as revenue'),
                    DB::raw('COUNT(*) as orders_count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $topProducts = DB::table('orders_details')
                ->join('orders', 'orders_details.order_id', '=', 'orders.id')
                ->join('variants', 'orders_details.variant_id', '=', 'variants.id')
                ->join('products', 'variants.product_id', '=', 'products.id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->whereIn('orders.status', ['completed', 'delivered'])
                ->select(
                    'products.name',
                    'products.id',
                    DB::raw('SUM(orders_details.quantity) as total_sold'),
                    DB::raw('SUM(orders_details.quantity * orders_details.price) as total_revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_revenue' => $monthlyRevenue,
                    'monthly_orders' => $monthlyOrders,
                    'total_customers' => $totalCustomers,
                    'total_products' => $totalProducts,
                    'orders_by_status' => $ordersByStatus,
                    'daily_revenue' => $dailyRevenue,
                    'top_products' => $topProducts,
                    'period' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'month' => $month,
                        'year' => $year
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê doanh thu trong tháng
     */
    public function getMonthlyRevenue(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $revenue = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'delivered'])
                ->sum('final_price');

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => $revenue,
                    'period' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'month' => $month,
                        'year' => $year
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê doanh thu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê doanh thu theo năm (cho ApexCharts)
     */
    public function getYearlyRevenue(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', Carbon::now()->year);

            $startDate = Carbon::create($year, 1, 1)->startOfYear();
            $endDate = Carbon::create($year, 12, 31)->endOfYear();

            $monthlyRevenue = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'delivered'])
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(final_price) as revenue'),
                    DB::raw('COUNT(*) as orders_count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $chartData = [];
            $monthNames = [
                1 => 'Tháng 1',
                2 => 'Tháng 2',
                3 => 'Tháng 3',
                4 => 'Tháng 4',
                5 => 'Tháng 5',
                6 => 'Tháng 6',
                7 => 'Tháng 7',
                8 => 'Tháng 8',
                9 => 'Tháng 9',
                10 => 'Tháng 10',
                11 => 'Tháng 11',
                12 => 'Tháng 12'
            ];

            for ($month = 1; $month <= 12; $month++) {
                $monthData = $monthlyRevenue->where('month', $month)->first();
                $chartData[] = [
                    'month' => $month,
                    'month_name' => $monthNames[$month],
                    'revenue' => $monthData ? $monthData->revenue : 0,
                    'orders_count' => $monthData ? $monthData->orders_count : 0
                ];
            }

            $totalYearlyRevenue = $monthlyRevenue->sum('revenue');
            $totalYearlyOrders = $monthlyRevenue->sum('orders_count');

            $lastYear = $year - 1;
            $lastYearRevenue = Orders::whereYear('created_at', $lastYear)
                ->whereIn('status', ['completed', 'delivered'])
                ->sum('final_price');

            $growthRate = $lastYearRevenue > 0
                ? (($totalYearlyRevenue - $lastYearRevenue) / $lastYearRevenue) * 100
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $year,
                    'total_revenue' => $totalYearlyRevenue,
                    'total_orders' => $totalYearlyOrders,
                    'last_year_revenue' => $lastYearRevenue,
                    'growth_rate' => round($growthRate, 2),
                    'monthly_data' => $chartData,
                    'apex_chart_data' => [
                        'categories' => array_column($chartData, 'month_name'),
                        'series' => [
                            [
                                'name' => 'Doanh thu',
                                'data' => array_column($chartData, 'revenue')
                            ],
                            [
                                'name' => 'Đơn hàng',
                                'data' => array_column($chartData, 'orders_count')
                            ]
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê doanh thu năm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê đơn hàng trong tháng
     */
    public function getMonthlyOrders(Request $request): JsonResponse
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $orders = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->count();

            $ordersByStatus = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $orders,
                    'orders_by_status' => $ordersByStatus,
                    'period' => [
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'month' => $month,
                        'year' => $year
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê đơn hàng theo trạng thái chi tiết
     */
    public function getOrdersByStatus(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period');

            if ($period) {
                $endDate = Carbon::now();
                $startDate = Carbon::now()->subDays($period);
            } else {
                $month = $request->get('month', Carbon::now()->month);
                $year = $request->get('year', Carbon::now()->year);
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            }

            $ordersByStatus = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('count(*) as count'), DB::raw('SUM(CASE WHEN status IN ("completed", "delivered") THEN final_price ELSE 0 END) as total_revenue'))
                ->groupBy('status')
                ->get();

            $statusConfig = [
                'pending' => ['name' => 'Chờ xử lý', 'color' => '#FFA500'],
                'processing' => ['name' => 'Đang xử lý', 'color' => '#3498DB'],
                'shipping' => ['name' => 'Đang giao hàng', 'color' => '#9B59B6'],
                'delivered' => ['name' => 'Đã giao hàng', 'color' => '#27AE60'],
                'completed' => ['name' => 'Hoàn thành', 'color' => '#2ECC71'],
                'cancelled' => ['name' => 'Đã hủy', 'color' => '#E74C3C'],
                'returned' => ['name' => 'Đã trả hàng', 'color' => '#95A5A6']
            ];

            $chartData = [];
            $totalOrders = 0;
            $totalRevenue = 0;

            foreach ($ordersByStatus as $order) {
                $statusName = $statusConfig[$order->status]['name'] ?? $order->status;
                $statusColor = $statusConfig[$order->status]['color'] ?? '#95A5A6';

                $chartData[] = [
                    'status' => $order->status,
                    'status_name' => $statusName,
                    'count' => $order->count,
                    'revenue' => $order->total_revenue,
                    'color' => $statusColor
                ];

                $totalOrders += $order->count;
                $totalRevenue += $order->total_revenue;
            }

            $apexChartData = [
                'series' => array_column($chartData, 'count'),
                'labels' => array_column($chartData, 'status_name'),
                'colors' => array_column($chartData, 'color')
            ];

            $dailyOrders = Orders::whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders'),
                    DB::raw('SUM(CASE WHEN status = "shipping" THEN 1 ELSE 0 END) as shipping_orders'),
                    DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $periodInfo = $period ? [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'period_days' => $period
            ] : [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'month' => $month,
                'year' => $year
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'orders_by_status' => $chartData,
                    'apex_chart_data' => $apexChartData,
                    'daily_orders' => $dailyOrders,
                    'period' => $periodInfo
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê đơn hàng theo trạng thái',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê khách hàng
     */
    public function getCustomersStats(): JsonResponse
    {
        try {
            $totalCustomers = User::where('role', 'user')->count();

            $newCustomersThisMonth = User::where('role', 'user')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $topCustomers = Orders::select(
                'users.id',
                'users.username',
                'users.email',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.final_price) as total_spent')
            )
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('users.id', 'users.username', 'users.email')
                ->orderByDesc('total_spent')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_customers' => $totalCustomers,
                    'new_customers_this_month' => $newCustomersThisMonth,
                    'top_customers' => $topCustomers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê khách hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê sản phẩm
     */
    public function getProductsStats(): JsonResponse
    {
        try {
            $totalProducts = Products::where('is_active', true)->count();

            $productsByCategory = Products::select(
                'categories.name as category_name',
                DB::raw('COUNT(products.id) as product_count')
            )
                ->join('categories', 'products.categories_id', '=', 'categories.id')
                ->where('products.is_active', true)
                ->groupBy('categories.id', 'categories.name')
                ->get();

            $productsByBrand = Products::select(
                'brands.name as brand_name',
                DB::raw('COUNT(products.id) as product_count')
            )
                ->join('brands', 'products.brand_id', '=', 'brands.id')
                ->where('products.is_active', true)
                ->groupBy('brands.id', 'brands.name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_products' => $totalProducts,
                    'products_by_category' => $productsByCategory,
                    'products_by_brand' => $productsByBrand
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê sản phẩm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy đơn hàng gần đây
     */
    public function getRecentOrders(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);

            $recentOrders = Orders::select(
                'orders.id',
                'orders.tracking_code',
                'orders.final_price',
                'orders.status',
                'orders.created_at',
                'users.username as customer_name',
                'users.email as customer_email',
                DB::raw('(SELECT COUNT(*) FROM orders_details WHERE orders_details.order_id = orders.id) as items_count')
            )
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->orderBy('orders.created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'tracking_code' => $order->tracking_code,
                        'customer' => $order->customer_name ?: $order->customer_email ?: 'Khách hàng',
                        'items' => $order->items_count,
                        'total' => $order->final_price,
                        'status' => $this->getStatusName($order->status),
                        'date' => $order->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentOrders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy đơn hàng gần đây',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy top sản phẩm bán chạy
     */
    public function getTopSelling(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $topProducts = Products::where('is_active', true)
                ->with(['mainImage', 'variants'])
                ->orderByDesc('sold_count')
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    $variant = $product->variants->first();
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sold_count' => $product->sold_count,
                        'image' => $product->mainImage ? asset('storage/' . $product->mainImage->image_path) : null,
                        'color' => $variant ? $variant->color : null,
                        'price' => $variant ? $variant->price : $product->price,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $topProducts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy sản phẩm bán chạy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy tên trạng thái đơn hàng
     */
    private function getStatusName($status)
    {
        $statusNames = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipping' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã trả hàng'
        ];

        return $statusNames[$status] ?? $status;
    }
}
