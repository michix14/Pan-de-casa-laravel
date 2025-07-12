<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  pedido: Object,
  pagos: Array,
  visitas: Number
});

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(amount);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getEstadoClass = (estado) => {
  switch (estado) {
    case 'completado':
      return 'bg-green-100 text-green-800';
    case 'pendiente':
      return 'bg-yellow-100 text-yellow-800';
    case 'rechazado':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getMetodoPagoIcon = (metodo) => {
  switch (metodo) {
    case 'pagofacil':
      return '💳';
    case 'efectivo':
      return '💵';
    case 'transferencia':
      return '🏦';
    default:
      return '💰';
  }
};
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <div class="flex items-center space-x-3">
                <Link :href="route('pedidos.index')" class="text-gray-500 hover:text-gray-700">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                  </svg>
                </Link>
                <h1 class="text-3xl font-bold text-gray-900">Pagos del Pedido #{{ pedido.id }}</h1>
              </div>
              <p class="mt-1 text-sm text-gray-600">
                Usuario: {{ pedido.usuario?.name || 'Sin nombre' }} | 
                Estado: {{ pedido.estado }} | 
                Tipo: {{ pedido.tipo }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Resumen del pedido -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-8">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Información del Pedido</h2>
          </div>
          <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500">ID del Pedido</p>
              <p class="text-lg font-semibold text-gray-900">#{{ pedido.id }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Fecha de Entrega</p>
              <p class="text-lg font-semibold text-gray-900">{{ pedido.fecha_entrega }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Total de Pagos</p>
              <p class="text-lg font-semibold text-gray-900">{{ pagos.length }}</p>
            </div>
          </div>
        </div>

        <!-- Lista de pagos -->
        <div v-if="pagos.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay pagos registrados</h3>
          <p class="mt-1 text-sm text-gray-500">Este pedido aún no tiene pagos asociados.</p>
        </div>

        <div v-else class="bg-white shadow-sm rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Historial de Pagos</h2>
          </div>
          
          <!-- Desktop Table -->
          <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ID Pago
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Método
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Monto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Referencia
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="pago in pagos" :key="pago.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ pago.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <div class="flex items-center space-x-2">
                      <span>{{ getMetodoPagoIcon(pago.metodo_pago) }}</span>
                      <span class="capitalize">{{ pago.metodo_pago }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ formatCurrency(pago.monto) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      getEstadoClass(pago.estado)
                    ]">
                      {{ pago.estado }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ formatDate(pago.fecha_pago || pago.fecha) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <span v-if="pago.referencia_externa" class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">
                      {{ pago.referencia_externa }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Cards -->
          <div class="lg:hidden space-y-4 p-4">
            <div v-for="pago in pagos" :key="pago.id" class="border border-gray-200 rounded-lg p-4">
              <div class="flex justify-between items-start mb-3">
                <div>
                  <p class="text-sm font-medium text-gray-900">Pago #{{ pago.id }}</p>
                  <p class="text-lg font-bold text-gray-900">{{ formatCurrency(pago.monto) }}</p>
                </div>
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  getEstadoClass(pago.estado)
                ]">
                  {{ pago.estado }}
                </span>
              </div>
              
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Método:</span>
                  <div class="flex items-center space-x-1">
                    <span>{{ getMetodoPagoIcon(pago.metodo_pago) }}</span>
                    <span class="capitalize">{{ pago.metodo_pago }}</span>
                  </div>
                </div>
                
                <div class="flex justify-between">
                  <span class="text-gray-500">Fecha:</span>
                  <span>{{ formatDate(pago.fecha_pago || pago.fecha) }}</span>
                </div>
                
                <div v-if="pago.referencia_externa" class="flex justify-between">
                  <span class="text-gray-500">Referencia:</span>
                  <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ pago.referencia_externa }}</span>
                </div>
                
                <div v-if="pago.transaction_id" class="flex justify-between">
                  <span class="text-gray-500">ID Transacción:</span>
                  <span class="font-mono text-xs">{{ pago.transaction_id }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen de totales -->
        <div v-if="pagos.length > 0" class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Resumen de Pagos</h2>
          </div>
          <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500">Total Pagado</p>
              <p class="text-2xl font-bold text-green-600">
                {{ formatCurrency(pagos.filter(p => p.estado === 'completado').reduce((sum, p) => sum + parseFloat(p.monto), 0)) }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Pagos Pendientes</p>
              <p class="text-2xl font-bold text-yellow-600">
                {{ formatCurrency(pagos.filter(p => p.estado === 'pendiente').reduce((sum, p) => sum + parseFloat(p.monto), 0)) }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Total General</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ formatCurrency(pagos.reduce((sum, p) => sum + parseFloat(p.monto), 0)) }}
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
