<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\Products;
use App\Models\Categories;
use App\Models\Images;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Product Import API ready',
            'supported_formats' => ['xlsx', 'xls'],
            'max_file_size' => '10MB',
            'total_products' => Products::count(),
            'required_fields' => ['name', 'description', 'price', 'category', 'image_url']
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
            'category',
            'image_url'
        ];

        $sampleData = [
            [
                'iPhone 14 Pro',
                'Điện thoại thông minh cao cấp từ Apple với chip A16 Bionic',
                25000000,
                'Điện thoại',
                'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400'
            ],
            [
                'Samsung Galaxy S23',
                'Flagship Android phone với camera 200MP và chip Snapdragon 8 Gen 2',
                20000000,
                'Điện thoại',
                'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=400'
            ],
            [
                'MacBook Pro M2',
                'Laptop cao cấp với chip M2, màn hình Retina 14 inch',
                35000000,
                'Laptop',
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400'
            ],
            [
                'Nike Air Max 270',
                'Giày thể thao với đế Air Max thoải mái, phù hợp cho mọi hoạt động',
                3500000,
                'Giày dép',
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400'
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
        $recentProducts = Products::with(['categories', 'images'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'recent_products' => $recentProducts,
            'total_count' => Products::count()
        ]);
    }
}
