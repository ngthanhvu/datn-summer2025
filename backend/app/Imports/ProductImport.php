<?php

namespace App\Imports;

use App\Models\Products;
use App\Models\Images;
use App\Models\Categories;
use App\Models\Brands;
use App\Models\Variants;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImport implements ToModel, WithHeadingRow, WithEvents, WithValidation
{
    private $images = [];
    private $currentRow = 1;

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->extractImages($event->getSheet()->getDelegate());
            },
        ];
    }

    private function extractImages($worksheet)
    {
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            if ($drawing instanceof MemoryDrawing) {
                $imageContents = $this->getImageContents($drawing);
                $extension = $this->getImageExtension($drawing);
            } else {
                $imageContents = file_get_contents($drawing->getPath());
                $extension = $drawing->getExtension();
            }

            $imageName = uniqid() . '.' . $extension;
            $imagePath = 'products/' . $imageName;

            Storage::disk('public')->put($imagePath, $imageContents);

            $coordinates = $drawing->getCoordinates();
            $row = (int) preg_replace('/[^0-9]/', '', $coordinates);
            $this->images[$row] = $imagePath;
        }
    }

    private function getImageContents($drawing)
    {
        ob_start();
        call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
        $imageContents = ob_get_contents();
        ob_end_clean();
        return $imageContents;
    }

    private function getImageExtension($drawing)
    {
        switch ($drawing->getMimeType()) {
            case MemoryDrawing::MIMETYPE_PNG:
                return 'png';
            case MemoryDrawing::MIMETYPE_GIF:
                return 'gif';
            case MemoryDrawing::MIMETYPE_JPEG:
                return 'jpg';
            default:
                return 'png';
        }
    }

    public function model(array $row)
    {
        $this->currentRow++;

        // Tìm hoặc tạo category
        $category = Categories::firstOrCreate(
            ['name' => $row['category']],
            [
                'name' => $row['category'],
                'slug' => $this->generateUniqueCategorySlug($row['category'])
            ]
        );

        // Tìm hoặc tạo brand
        $brand = Brands::firstOrCreate(
            ['name' => $row['brand']],
            [
                'name' => $row['brand'],
                'slug' => $this->generateUniqueBrandSlug($row['brand'])
            ]
        );

        // Tính toán giá
        $price = (float) $row['price'];
        $originalPrice = isset($row['original_price']) ? (float) $row['original_price'] : $price;
        $discountPrice = isset($row['discount_price']) ? (float) $row['discount_price'] : null;

        // Tạo sản phẩm
        $product = Products::create([
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'price' => $price,
            'original_price' => $originalPrice,
            'discount_price' => $discountPrice,
            'slug' => $this->generateUniqueSlug($row['name']),
            'categories_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => isset($row['is_active']) ? (bool) $row['is_active'] : true,
        ]);

        // Xử lý variant nếu có
        $this->handleProductVariants($product, $row);

        // Xử lý hình ảnh
        $this->handleProductImages($product, $row);

        return $product;
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Products::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueCategorySlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Categories::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueBrandSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Brands::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function handleProductVariants($product, $row)
    {
        // Chỉ tạo variant nếu có thông tin color hoặc size
        if (isset($row['variant_color']) || isset($row['variant_size'])) {
            $variant = Variants::create([
                'product_id' => $product->id,
                'color' => $row['variant_color'] ?? null,
                'size' => $row['variant_size'] ?? null,
                'price' => isset($row['variant_price']) ? (float) $row['variant_price'] : $product->price,
                'sku' => $this->generateVariantSKU($product, $row['variant_color'] ?? $row['variant_size'] ?? '')
            ]);

            // Tạo inventory record nếu có quantity
            if (isset($row['variant_quantity']) && $row['variant_quantity'] > 0) {
                \App\Models\Inventory::create([
                    'variant_id' => $variant->id,
                    'quantity' => (int) $row['variant_quantity'],
                    'type' => 'initial',
                    'note' => 'Initial stock from import'
                ]);
            }
        }
    }

    private function generateVariantSKU($product, $variantIdentifier)
    {
        // Tạo SKU từ product ID và variant identifier
        $baseSKU = strtoupper(substr($product->name, 0, 3)) . '-' .
            strtoupper(substr($variantIdentifier, 0, 3)) . '-' .
            str_pad($product->id, 4, '0', STR_PAD_LEFT);

        // Kiểm tra xem SKU đã tồn tại chưa
        $counter = 1;
        $sku = $baseSKU;
        while (Variants::where('sku', $sku)->exists()) {
            $sku = $baseSKU . '-' . $counter;
            $counter++;
        }

        return $sku;
    }

    private function handleProductImages($product, $row)
    {
        $imageOrder = 1;
        $hasMainImage = false;

        // Xử lý hình ảnh từ URL
        if (isset($row['image_url']) && $row['image_url']) {
            $imageUrls = explode('|', $row['image_url']);
            foreach ($imageUrls as $imageUrl) {
                $imagePath = $this->downloadImageFromUrl(trim($imageUrl));
                if ($imagePath) {
                    Images::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_main' => !$hasMainImage,
                        'sort_order' => $imageOrder++,
                    ]);
                    $hasMainImage = true;
                }
            }
        }

        // Xử lý hình ảnh embed từ Excel
        if (isset($this->images[$this->currentRow])) {
            Images::create([
                'product_id' => $product->id,
                'image_path' => $this->images[$this->currentRow],
                'is_main' => !$hasMainImage,
                'sort_order' => $imageOrder++,
            ]);
        }
    }

    private function downloadImageFromUrl($imageUrl)
    {
        try {
            $imageContents = file_get_contents($imageUrl);
            if ($imageContents === false) {
                return null;
            }

            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$extension) {
                $extension = 'jpg';
            }

            $imageName = uniqid() . '.' . $extension;
            $imagePath = 'products/' . $imageName;

            Storage::disk('public')->put($imagePath, $imageContents);

            return $imagePath;
        } catch (\Exception $e) {
            \Log::error('Error downloading image: ' . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'variant_color' => 'nullable|string|max:50',
            'variant_size' => 'nullable|string|max:50',
            'variant_price' => 'nullable|numeric|min:0',
            'variant_quantity' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'category.required' => 'Danh mục là bắt buộc',
            'brand.required' => 'Thương hiệu là bắt buộc',
            'variant_price.numeric' => 'Giá variant phải là số',
            'variant_quantity.integer' => 'Số lượng variant phải là số nguyên'
        ];
    }
}
