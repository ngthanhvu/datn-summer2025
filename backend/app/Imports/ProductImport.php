<?php

namespace App\Imports;

use App\Models\Products;
use App\Models\Images;
use App\Models\Categories;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $category = Categories::firstOrCreate(
            ['name' => $row['category']],
            [
                'name' => $row['category'],
                'slug' => $this->generateUniqueCategorySlug($row['category'])
            ]
        );

        $product = Products::create([
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'price' => (float) $row['price'],
            'discount_price' => (float) $row['price'],
            'slug' => $this->generateUniqueSlug($row['name']),
            'categories_id' => $category->id,
            'brand_id' => null,
            'is_active' => true,
        ]);

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

    private function handleProductImages($product, $row)
    {
        if (isset($row['image_url']) && $row['image_url']) {
            $imageUrls = explode('|', $row['image_url']);
            $hasMainImage = false;

            foreach ($imageUrls as $imageUrl) {
                $imagePath = $this->downloadImageFromUrl(trim($imageUrl));
                if ($imagePath) {
                    Images::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_main' => !$hasMainImage,
                    ]);
                    $hasMainImage = true;
                }
            }
        }
    }

    private function downloadImageFromUrl($imageUrl)
    {
        try {
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                \Log::warning('Invalid image URL: ' . $imageUrl);
                return null;
            }

            $parsedUrl = parse_url($imageUrl);
            if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
                \Log::warning('Unsupported protocol for image URL: ' . $imageUrl);
                return null;
            }

            $imageContents = file_get_contents($imageUrl);
            if ($imageContents === false) {
                \Log::warning('Failed to download image from URL: ' . $imageUrl);
                return null;
            }

            if (strlen($imageContents) > 10 * 1024 * 1024) {
                \Log::warning('Image file too large: ' . $imageUrl . ' (' . strlen($imageContents) . ' bytes)');
                return null;
            }

            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$extension) {
                $extension = 'jpg';
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                $extension = 'jpg';
            }

            $imageName = uniqid() . '.' . $extension;
            $imagePath = 'products/' . $imageName;

            Storage::disk('public')->put($imagePath, $imageContents);

            return $imagePath;
        } catch (\Exception $e) {
            \Log::error('Error downloading image from URL: ' . $imageUrl . ' - ' . $e->getMessage());
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'image_url' => 'nullable|string'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'name.string' => 'Tên sản phẩm phải là chuỗi',
            'name.max' => 'Tên sản phẩm không được quá 255 ký tự',
            'description.string' => 'Mô tả phải là chuỗi',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'price.min' => 'Giá sản phẩm phải lớn hơn 0',
            'category.required' => 'Danh mục là bắt buộc',
            'category.string' => 'Danh mục phải là chuỗi',
            'category.max' => 'Danh mục không được quá 255 ký tự',
            'image_url.string' => 'URL hình ảnh phải là chuỗi'
        ];
    }
}
