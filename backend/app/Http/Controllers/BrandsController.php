<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Exception;
use App\Models\Brands;
class BrandsController extends Controller
{

    public function index(Request $request)
    {
        $query = Brands::query();

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('root')) {
            $query->root();
        }

        if ($request->has('with_children')) {
            $query->with('children');
        }

        if ($request->has('with_all_children')) {
            $query->with('allChildren');
        }


        $brandss = $query->paginate(10);
        if ($brandss ->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Danh sách thương hiệu trống, thêm thương hiệu mới để bắt đầu!',
                'data' => []
            ], 200);
        }
        return response()->json($brandss);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('Brandss', 'name')->where(function ($query) {
                        return $query->where('is_active', true);
                    })
                ],
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    Rule::exists('Brandss', 'id')->where(function ($query) {
                        return $query->where('is_active', true);
                    }),
                ],
                'image' => [
                    'nullable',
                    'string',
                    'regex:/^.*\.(jpg|jpeg|png|gif|bmp|webp)$/i'
                ],
                'is_active' => 'boolean'
            ],[
                'name.required' => 'Tên thương hiệu không được để trống',
                'name.string' => 'Tên thương hiệu phải là chuỗi ký tự',
                'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự',
                'name.unique' => 'Tên thương hiệu đã tồn tại',
                'description.string' => 'Mô tả phải là chuỗi ký tự',
                'parent_id.exists' => 'Thương hiệu cha không tồn tại hoặc không hoạt động',
                'image.string' => 'Đường dẫn ảnh phải là chuỗi ký tự',
                'image.regex' => 'Định dạng ảnh không hợp lệ. Chấp nhận: jpg, jpeg, png, gif, bmp, webp',
                'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false'
            ]);

            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;

            while (Brands::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $brands = new Brands();
            $brands->name = $request->name;
            $brands->slug = $slug;
            $brands->description = $request->description;
            $brands->parent_id = $request->parent_id;
            $brands->image = $request->image;
            $brands->is_active = $request->is_active ?? true;
            $brands->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm thương hiệu thành công!',
                'data' => $brands->load(['parent', 'children'])
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi thêm thương hiệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $brands = Brands::with(['parent', 'children', 'products'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $brands
        ]);
    }



    public function update(Request $request, string $id)
    {
        try {
            $brands = Brands::findOrFail($id);
            
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('Brandss', 'name')->where(function ($query) {
                        return $query->where('is_active', true);
                    })->ignore($id)
                ],
                'slug' => 'required|string|max:255|unique:Brandss,slug,' . $id,
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    Rule::exists('Brandss', 'id')->where(function ($query) use ($id) {
                        return $query->where('is_active', true)
                            ->where('id', '!=', $id);
                    }),
                ],
                'image' => [
                    'nullable',
                    'string',
                    'regex:/^.*\.(jpg|jpeg|png|gif|bmp|webp)$/i'
                ],
                'is_active' => 'boolean',
            ],[
                'name.required' => 'Tên thương hiệu không được để trống',
                'name.string' => 'Tên thương hiệu phải là chuỗi ký tự',
                'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự',
                'name.unique' => 'Tên thương hiệu đã tồn tại',
                'slug.required' => 'Slug không được để trống',
                'slug.string' => 'Slug phải là chuỗi ký tự',
                'slug.max' => 'Slug không được vượt quá 255 ký tự',
                'slug.unique' => 'Slug đã tồn tại',
                'description.string' => 'Mô tả phải là chuỗi ký tự',
                'parent_id.exists' => 'Thương hiệu cha không tồn tại hoặc không hoạt động',
                'image.string' => 'Đường dẫn ảnh phải là chuỗi ký tự',
                'image.regex' => 'Định dạng ảnh không hợp lệ. Chấp nhận: jpg, jpeg, png, gif, bmp, webp',
                'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false'
            ]);

            if ($request->parent_id && $brands->allChildren->pluck('id')->contains($request->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể đặt thương hiệu con làm thương hiệu cha!'
                ], 422);
            }

            $brands->update([
                'name' => $request->name ?? $brands->name,
                'slug' => $request->name ? Str::slug($request->name) : $brands->slug,
                'description' => $request->description ?? $brands->description,
                'parent_id' => $request->parent_id ?? $brands->parent_id,
                'image' => $request->image ?? $brands->image,
                'is_active' => $request->is_active ?? $brands->is_active
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thương hiệu thành công!',
                'data' => $brands->load(['parent', 'children'])
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi cập nhật thương hiệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        $brands = Brands::findOrFail($id);
        if ($brands->children()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa thương hiệu có thương hiệu con!'
            ], 400);
        }
        $brands->delete();

        if ($brands->products()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa thương hiệu đang tồn tại sản phẩm!'
            ], 400);
        }

        if ($brands->parent_id) {
            $brands->parent->children()->where('id', $brands->id)->update(['parent_id' => null]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa thương hiệu thành công!',
        ]);
    }
}
