<template>
  <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
    <div class="tw-flex tw-gap-4 tw-mb-4">
      <select class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
        <option>All category</option>
      </select>
      <input type="text" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56" placeholder="dd/mm/yyyy" />
      <select class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
        <option>Status</option>
      </select>
    </div>
    <div class="tw-overflow-x-auto">
      <table class="tw-w-full tw-text-left tw-bg-white">
        <thead>
          <tr class="tw-border-b tw-bg-gray-50">
            <th class="tw-px-4 tw-py-3"><input type="checkbox" /></th>
            <th class="tw-px-4 tw-py-3"> </th>
            <th class="tw-px-4 tw-py-3">Name</th>
            <th class="tw-px-4 tw-py-3">Price</th>
            <th class="tw-px-4 tw-py-3">Status</th>
            <th class="tw-px-4 tw-py-3">Date</th>
            <th class="tw-px-4 tw-py-3"> </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(product, idx) in products" :key="idx" class="tw-border-b hover:tw-bg-gray-50">
            <td class="tw-px-4 tw-py-3"><input type="checkbox" /></td>
            <td class="tw-px-4 tw-py-3">
              <img :src="product.image" class="tw-w-14 tw-h-14 tw-object-cover tw-rounded" />
            </td>
            <td class="tw-px-4 tw-py-3 tw-font-medium">{{ product.name }}</td>
            <td class="tw-px-4 tw-py-3 tw-text-blue-900">${{ product.price }}</td>
            <td class="tw-px-4 tw-py-3">
              <span :class="badgeClass(product.status)">{{ product.statusLabel }}</span>
            </td>
            <td class="tw-px-4 tw-py-3">{{ product.date }}</td>
            <td class="tw-px-4 tw-py-3 tw-flex tw-gap-2">
              <button class="tw-bg-teal-600 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-teal-700">Edit</button>
              <button
                class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const products = ref([
  {
    image: 'https://placehold.co/60x60',
    name: 'T-shirt for men medium size',
    price: '34.50',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Helionic Hooded Down Jacket',
    price: '990.99',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Lace mini dress with faux leather',
    price: '76.99',
    status: 'archived',
    statusLabel: 'Archived',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: "Fanmis Men's Travel Bag",
    price: '18.00',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Jeans Shorts for Men',
    price: '76.99',
    status: 'disabled',
    statusLabel: 'Disabled',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Wallet made of leather brown color',
    price: '18.00',
    status: 'archived',
    statusLabel: 'Archived',
    date: '02.11.2022',
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Modern Armchair for home interiors',
    price: '76.99',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
  },
])

function badgeClass(status) {
  switch (status) {
    case 'active':
      return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs';
    case 'archived':
      return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs';
    case 'disabled':
      return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs';
    default:
      return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs';
  }
}
</script>

<style scoped></style>