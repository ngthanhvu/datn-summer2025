<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Products;

class ProductImportController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Product Import API ready',
            'supported_formats' => ['xlsx', 'xls'],
            'max_file_size' => '10MB',
            'total_products' => Products::count()
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $initialCount = Products::count();

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $import = new ProductImport();

            Excel::import($import, $file);

            DB::commit();

            $finalCount = Products::count();
            $importedCount = $finalCount - $initialCount;

            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được import thành công!',
                'imported_count' => $importedCount,
                'total_products' => $finalCount
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi validation trong file Excel',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi import: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'name',
            'description',
            'price',
            'original_price',
            'discount_price',
            'category',
            'brand',
            'is_active',
            'variant_color',
            'variant_size',
            'variant_price',
            'variant_quantity',
            'image_url'
        ];

        $sampleData = [
            [
                'iPhone 14 Pro',
                'Điện thoại thông minh cao cấp từ Apple',
                25000000,
                27000000,
                null,
                'Điện thoại',
                'Apple',
                1,
                'Đen',
                '128GB',
                25000000,
                10,
                'https://placehold.co/100?text=so1|https://placehold.co/100?text=so2'
            ],
            [
                'Samsung Galaxy S23',
                'Flagship Android phone',
                20000000,
                22000000,
                19000000,
                'Điện thoại',
                'Samsung',
                1,
                'Xanh',
                '256GB',
                20000000,
                5,
                'https://placehold.co/100?text=so3'
            ]
        ];

        return Excel::download(new class($headers, $sampleData) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $headers;
            private $data;

            public function __construct($headers, $data)
            {
                $this->headers = $headers;
                $this->data = $data;
            }

            public function array(): array
            {
                return array_merge([$this->headers], $this->data);
            }
        }, 'product_import_template.xlsx');
    }

    public function getImportHistory()
    {
        $recentProducts = Products::with(['categories', 'brand', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_products' => $recentProducts,
            'total_count' => Products::count()
        ]);
    }
}
