<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Exception;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = Categories::query();

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

        $categories = $query->paginate(10);

        if ($categories->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Danh mục trống, thêm danh mục mới để bắt đầu!',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')->where(function ($query) {
                        return $query->where('is_active', true);
                    })
                ],
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        return $query->where('is_active', true);
                    }),
                ],
                'image' => [
                    'nullable',
                    'string',
                    'regex:/^.*\.(jpg|jpeg|png|gif|bmp|webp)$/i'
                ],
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Tên danh mục không được để trống',
                'name.string' => 'Tên danh mục phải là chuỗi ký tự',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',
                'description.string' => 'Mô tả phải là chuỗi ký tự',
                'parent_id.exists' => 'Danh mục cha không tồn tại hoặc không hoạt động',
                'image.string' => 'Đường dẫn ảnh phải là chuỗi ký tự',
                'image.regex' => 'Định dạng ảnh không hợp lệ. Chấp nhận: jpg, jpeg, png, gif, bmp, webp',
                'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false'
            ]);

            $baseSlug = Str::slug($request->name);
            $slug = $baseSlug;
            $counter = 1;

            while (Categories::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $categories = new Categories();
            $categories->name = $request->name;
            $categories->slug = $slug;
            $categories->description = $request->description;
            $categories->parent_id = $request->parent_id;
            $categories->image = $request->image;
            $categories->is_active = $request->is_active ?? true;
            $categories->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm danh mục thành công!',
                'data' => $categories->load(['parent', 'children'])
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi thêm danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $categories = Categories::with(['parent', 'children', 'products'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function update(Request $request, string $id)
    {
        try {
            $categories = Categories::findOrFail($id);

            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')->where(function ($query) {
                        return $query->where('is_active', true);
                    })->ignore($id)
                ],
                'description' => 'nullable|string',
                'parent_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->where(function ($query) use ($id) {
                        return $query->where('is_active', true)
                            ->where('id', '!=', $id);
                    }),
                ],
                'image' => 'nullable|string',
                'is_active' => 'boolean'
            ], [
                'name.required' => 'Tên danh mục không được để trống',
                'name.string' => 'Tên danh mục phải là chuỗi ký tự',
                'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
                'name.unique' => 'Tên danh mục đã tồn tại',
                'description.string' => 'Mô tả phải là chuỗi ký tự',
                'parent_id.exists' => 'Danh mục cha không tồn tại hoặc không hoạt động',
                'image.string' => 'Đường dẫn ảnh phải là chuỗi ký tự',
                'image.regex' => 'Định dạng ảnh không hợp lệ. Chấp nhận: jpg, jpeg, png, gif, bmp, webp',
                'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false'
            ]);

            if ($request->parent_id && $categories->allChildren->pluck('id')->contains($request->parent_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể đặt danh mục con làm danh mục cha!'
                ], 422);
            }

            if ($request->name && $request->name !== $categories->name) {
                $baseSlug = Str::slug($request->name);
                $slug = $baseSlug;
                $counter = 1;

                while (Categories::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $categories->slug = $slug;
            }

            $categories->name = $request->name ?? $categories->name;
            $categories->description = $request->description ?? $categories->description;
            $categories->parent_id = $request->parent_id ?? $categories->parent_id;
            $categories->image = $request->image ?? $categories->image;
            $categories->is_active = $request->is_active ?? $categories->is_active;
            $categories->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật danh mục thành công!',
                'data' => $categories->load(['parent', 'children'])
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi cập nhật danh mục',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $categories = Categories::findOrFail($id);

        if ($categories->children()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa danh mục có danh mục con!'
            ], 400);
        }

        if ($categories->products()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa danh mục đang tồn tại sản phẩm!'
            ], 400);
        }

        $categories->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa danh mục thành công!'
        ]);
    }
}
