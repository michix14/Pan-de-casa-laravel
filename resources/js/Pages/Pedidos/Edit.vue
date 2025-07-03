<!-- resources/js/Pages/Pedidos/Edit.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Form from './Form.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  pedido: Object,
  usuarios: Array
});
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-3">
                <Link :href="route('pedidos.index')" class="hover:text-gray-700">Pedidos</Link>
                <span>/</span>
                <span class="text-gray-900">Editar</span>
              </nav>
              <h1 class="text-2xl font-semibold text-gray-900">Editar pedido</h1>
            </div>
            <Link :href="route('pedidos.index')" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
              Cancelar
            </Link>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <Form :pedido="pedido" :usuarios="usuarios" :isEdit="true" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
