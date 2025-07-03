<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
  ventas: Array,
  visitas: Number
});

const eliminar = (id) => {
  if (confirm('¿Estás seguro de eliminar esta venta?')) {
    router.delete(route('ventas.destroy', id));
  }
};
const page = usePage();
watch(
  () => page.props.flash,
  (flash) => {
    if (flash.success) {
      toast.success(flash.success);
    }
    if (flash.error) {
      toast.error(flash.error);
    }
  },
  { immediate: true }
);
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Ventas</h1>
              <p class="mt-1 text-sm text-gray-600">Gestiona las ventas realizadas en el sistema</p>
            </div>
            <div class="mt-4 sm:mt-0">
              <Link :href="route('ventas.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nueva Venta
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div v-if="ventas.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m2 4H7a2 2 0 01-2-2V7a2 2 0 012-2h4l2-2h6a2 2 0 012 2v14a2 2 0 01-2 2z"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay ventas registradas</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza registrando tu primera venta.</p>
        </div>

        <!-- Desktop Table -->
        <div v-else class="hidden lg:block bg-white shadow-sm rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha Entrega</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total (Bs)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Productos</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(venta, index) in ventas" :key="venta.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ venta.pedido?.usuario?.name ?? 'Sin nombre' }}
                </td>
                <td class="px-6 py-4 text-sm text-blue-600 font-semibold">
                  {{ venta.pedido?.tipo }}
                </td>
                <td class="px-6 py-4">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    venta.pedido?.estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' :
                    venta.pedido?.estado === 'COMPLETADO' ? 'bg-green-100 text-green-800' :
                    'bg-red-100 text-red-800'
                  ]">
                    {{ venta.pedido?.estado }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ venta.pedido?.fecha_entrega }}
                </td>
                <td class="px-6 py-4 text-sm text-green-700 font-bold">
                  {{ Number(venta.total ?? 0).toFixed(2) }} Bs
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <ul class="list-disc list-inside">
                    <li v-for="item in venta.detalles" :key="item.id">
                      {{ item.producto?.nombre }} x{{ item.cantidad }} = {{ Number(item.total ?? 0).toFixed(2) }} Bs
                    </li>
                  </ul>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <Link :href="route('ventas.edit', venta.id)" class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md transition">
                      Editar
                    </Link>
                    <button @click="eliminar(venta.id)" class="inline-flex items-center px-3 py-1 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition">
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-4">
          <div v-for="(venta, index) in ventas" :key="venta.id" class="bg-white shadow-sm rounded-lg p-4 border border-gray-200">
            <div class="text-sm text-gray-700 space-y-1">
              <p><strong>#:</strong> {{ index + 1 }}</p>
              <p><strong>Cliente:</strong> {{ venta.pedido?.usuario?.name ?? 'Sin nombre' }}</p>
              <p><strong>Tipo:</strong> {{ venta.pedido?.tipo }}</p>
              <p><strong>Estado:</strong>
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  venta.pedido?.estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' :
                  venta.pedido?.estado === 'COMPLETADO' ? 'bg-green-100 text-green-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ venta.pedido?.estado }}
                </span>
              </p>
              <p><strong>Fecha Entrega:</strong> {{ venta.pedido?.fecha_entrega }}</p>
              <p><strong>Total:</strong> {{ Number(venta.total ?? 0).toFixed(2) }} Bs Bs</p>
              <p><strong>Productos:</strong></p>
              <ul class="list-disc list-inside ml-4 text-sm text-gray-600">
                <li v-for="item in venta.detalles" :key="item.id">
                  {{ item.producto?.nombre }} x{{ item.cantidad }} = {{ Number(item.total ?? 0).toFixed(2) }} Bs
                </li>
              </ul>
            </div>
            <div class="mt-3 flex space-x-2">
              <button @click="eliminar(venta.id)" class="flex-1 inline-flex justify-center px-3 py-2 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-md">
                Eliminar
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
