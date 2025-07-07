<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  venta: {
    type: Object,
    required: true,
    default: () => ({})
  },
  visitas: {
    type: Number,
    default: 0
  }
});

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatearMoneda = (monto) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(monto);
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
              <h1 class="text-3xl font-bold text-gray-900">Detalles de Venta #{{ venta.id }}</h1>
              <p class="mt-1 text-sm text-gray-600">
                Fecha: {{ formatearFecha(venta.fecha) }}
              </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
              <!-- Botón de Pago Fácil si está pendiente -->
              <Link 
                v-if="venta.pedido?.estado === 'PENDIENTE'" 
                :href="route('pagofacil.index', { venta_id: venta.id })" 
                class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg shadow-sm transition"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Pagar con Pago Fácil
              </Link>
              
              <Link 
                :href="route('ventas.index')" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
              >
                Volver
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Información Principal -->
          <div class="lg:col-span-2">
            <!-- Información del Pedido -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Información del Pedido</h2>
              <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ venta.pedido?.usuario?.name || 'Sin nombre' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Email</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ venta.pedido?.usuario?.email || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tipo de Pedido</dt>
                  <dd class="mt-1 text-sm text-blue-600 font-semibold">{{ venta.pedido?.tipo }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Estado</dt>
                  <dd class="mt-1">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      venta.pedido?.estado === 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' :
                      venta.pedido?.estado === 'COMPLETADO' ? 'bg-green-100 text-green-800' :
                      'bg-red-100 text-red-800'
                    ]">
                      {{ venta.pedido?.estado }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Fecha de Entrega</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatearFecha(venta.pedido?.fecha_entrega) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Total</dt>
                  <dd class="mt-1 text-lg font-bold text-green-600">{{ formatearMoneda(venta.total) }}</dd>
                </div>
              </dl>
            </div>

            <!-- Productos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Productos</h2>
              <div class="overflow-hidden">
                <table class="min-w-full">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                      <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                      <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="detalle in venta.detalles" :key="detalle.id">
                      <td class="px-4 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ detalle.producto?.nombre }}</div>
                        <div class="text-sm text-gray-500">{{ detalle.producto?.descripcion }}</div>
                      </td>
                      <td class="px-4 py-4 text-center text-sm text-gray-900">{{ detalle.cantidad }}</td>
                      <td class="px-4 py-4 text-right text-sm text-gray-900">{{ formatearMoneda(detalle.precio_unitario) }}</td>
                      <td class="px-4 py-4 text-right text-sm font-semibold text-gray-900">{{ formatearMoneda(detalle.total) }}</td>
                    </tr>
                  </tbody>
                  <tfoot class="bg-gray-50">
                    <tr>
                      <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">Total:</td>
                      <td class="px-4 py-3 text-right text-lg font-bold text-green-600">{{ formatearMoneda(venta.total) }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <!-- Estado del Pago -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Estado del Pago</h2>
              
              <!-- Si hay pagos registrados -->
              <div v-if="venta.pagos && venta.pagos.length > 0" class="space-y-3">
                <div v-for="pago in venta.pagos" :key="pago.id" class="border rounded-lg p-3">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">{{ pago.metodo_pago }}</span>
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      pago.estado === 'completado' ? 'bg-green-100 text-green-800' :
                      pago.estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    ]">
                      {{ pago.estado }}
                    </span>
                  </div>
                  <div class="text-sm text-gray-600">
                    <p>Monto: {{ formatearMoneda(pago.monto) }}</p>
                    <p v-if="pago.fecha_pago">
                      Pagado: {{ formatearFecha(pago.fecha_pago) }}
                    </p>
                    <p v-if="pago.referencia_externa">
                      Ref: {{ pago.referencia_externa }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Si no hay pagos -->
              <div v-else class="text-center py-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin pagos registrados</h3>
                <p class="mt-1 text-sm text-gray-500">Esta venta aún no tiene pagos asociados.</p>
              </div>
            </div>

            <!-- Opciones de Pago -->
            <div v-if="venta.pedido?.estado === 'PENDIENTE'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Opciones de Pago</h2>
              <div class="space-y-3">
                <!-- Pago Fácil -->
                <Link 
                  :href="route('pagofacil.index', { venta_id: venta.id })" 
                  class="w-full flex items-center justify-center px-4 py-3 border border-orange-300 rounded-lg text-orange-700 bg-orange-50 hover:bg-orange-100 transition"
                >
                  <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/>
                  </svg>
                  Pago Fácil (QR/Tigo Money)
                </Link>

                <!-- Stripe -->
                <Link 
                  :href="route('stripe.form', venta.id)" 
                  class="w-full flex items-center justify-center px-4 py-3 border border-blue-300 rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 transition"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  Tarjeta de Crédito (Stripe)
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
