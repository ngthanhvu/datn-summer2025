<template>
  <div class="tw-bg-[#F9FAFB] tw-min-h-screen tw-p-6">
    <main class="tw-max-w-7xl tw-mx-auto tw-flex tw-flex-col md:tw-flex-row tw-gap-6">
      <section class="tw-flex-1 tw-space-y-6">
        <h1 class="tw-text-gray-900 tw-text-xl tw-font-semibold">Add New Product</h1>

        <form class="tw-space-y-6" action="#" method="POST" enctype="multipart/form-data" novalidate>
          <div class="tw-bg-white tw-rounded-lg tw-p-4 tw-space-y-4 tw-shadow-sm">
            <div>
              <label for="product-title" class="tw-block tw-text-sm tw-font-medium tw-text-gray-500 tw-mb-1">Product title</label>
              <input
                type="text"
                id="product-title"
                name="product-title"
                placeholder="Type here"
                class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
              />
            </div>

            <div class="tw-grid tw-grid-cols-3 tw-gap-4 tw-text-sm tw-text-gray-500">
              <div>
                <label for="sku" class="tw-block tw-font-medium tw-mb-1">SKU</label>
                <input
                  type="text"
                  id="sku"
                  name="sku"
                  placeholder="Type here"
                  class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
                />
              </div>
              <div>
                <label for="color" class="tw-block tw-font-medium tw-mb-1">Color</label>
                <input
                  type="text"
                  id="color"
                  name="color"
                  placeholder="Type here"
                  class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
                />
              </div>
              <div>
                <label for="size" class="tw-block tw-font-medium tw-mb-1">Size</label>
                <input
                  type="text"
                  id="size"
                  name="size"
                  placeholder="Type here"
                  class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
                />
              </div>
            </div>

            <div>
              <label for="brand" class="tw-block tw-text-sm tw-font-medium tw-text-gray-500 tw-mb-1">Brand</label>
              <input
                type="text"
                id="brand"
                name="brand"
                placeholder="Type here"
                class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
              />
            </div>
          </div>

          <div>
            <label for="description" class="tw-block tw-text-sm tw-font-medium tw-text-gray-500 tw-mb-1">Description</label>
            <textarea
              id="description"
              name="description"
              rows="3"
              placeholder="Type here"
              class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 tw-resize-y focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
            ></textarea>
          </div>

          <div>
            <label for="images" class="tw-block tw-text-sm tw-font-medium tw-text-gray-500 tw-mb-1">Images</label>
            <input
              type="file"
              id="images"
              name="images"
              class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-1 tw-cursor-pointer"
            />
          </div>
        </form>
      </section>

      <aside class="tw-w-full tw-max-w-xs tw-bg-white tw-rounded-lg tw-p-4 tw-shadow-sm tw-space-y-4 tw-text-sm tw-text-gray-500">
        <div class="tw-flex tw-justify-end tw-gap-2 tw-mb-2">
          <button
            type="button"
            class="tw-text-gray-400 tw-text-sm tw-font-normal tw-border tw-border-gray-300 tw-rounded tw-px-3 tw-py-[6px] hover:tw-bg-gray-100"
          >
            Save to draft
          </button>
          <button
            type="button"
            class="tw-bg-[#047857] tw-text-white tw-text-sm tw-font-normal tw-rounded tw-px-3 tw-py-[6px] hover:tw-bg-[#065f46]"
          >
            Publish
          </button>
        </div>

        <div>
          <label for="price" class="tw-block tw-font-semibold tw-text-gray-700 tw-mb-1 tw-text-sm">Price</label>
          <input
            type="text"
            id="price"
            name="price"
            placeholder="Type here"
            class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
          />
        </div>

        <div>
          <label for="status" class="tw-block tw-font-semibold tw-text-gray-700 tw-mb-1 tw-text-sm">Status</label>
          <select
            id="status"
            name="status"
            class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 tw-cursor-pointer focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
          >
            <option selected>Published</option>
          </select>
        </div>

        <div>
          <label for="tags" class="tw-block tw-font-semibold tw-text-gray-700 tw-mb-1 tw-text-sm">Tags</label>
          <input
            type="text"
            id="tags"
            name="tags"
            placeholder="Type here"
            class="tw-w-full tw-rounded-md tw-border tw-border-gray-200 tw-bg-gray-100 tw-text-gray-500 tw-text-sm tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-ring-1 focus:tw-ring-indigo-500 focus:tw-border-indigo-500"
          />
          <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mt-1 tw-text-sm tw-text-[#047857] tw-font-semibold">
            <span class="tw-flex tw-items-center tw-gap-1 tw-cursor-pointer tw-select-none">
              <span>×</span> Tech
            </span>
            <span class="tw-flex tw-items-center tw-gap-1 tw-cursor-pointer tw-select-none">
              <span>×</span> Mobile
            </span>
            <span class="tw-flex tw-items-center tw-gap-1 tw-cursor-pointer tw-select-none">
              <span>×</span> Apple
            </span>
          </div>
        </div>

        <div>
          <p class="tw-font-semibold tw-text-gray-700 tw-mb-1 tw-text-sm">Categories</p>
          <div class="tw-flex tw-flex-col tw-gap-1 tw-text-sm tw-text-gray-600">
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="shirt" class="tw-cursor-pointer" />
              Shirt
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="t-shirt" class="tw-cursor-pointer" />
              T-Shirt
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="sneakers" class="tw-cursor-pointer" />
              Sneakers
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="joggers" class="tw-cursor-pointer" />
              Joggers
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="vests" class="tw-cursor-pointer" />
              Vests
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="knitwear" class="tw-cursor-pointer" />
              Knitwear
            </label>
            <label class="tw-flex tw-items-center tw-gap-2 tw-cursor-pointer">
              <input type="checkbox" name="category" value="shorts" class="tw-cursor-pointer" />
              Shorts
            </label>
          </div>
        </div>
      </aside>
    </main>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin',
})
</script>

<style>
body {
  font-family: 'Inter', sans-serif;
}
</style>