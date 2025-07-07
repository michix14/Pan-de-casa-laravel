<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  status: String,
  nroPago: String,
  visitas: Number
});

const getStatusInfo = () => {
  switch (props.status) {
    case 'success':
      return {
        icon: 'success',
        title: '¡Pago Exitoso!',
        message: 'Tu pago ha sido procesado correctamente.',
        bgColor: 'bg-green-100',
        iconColor: 'text-green-600',
        titleColor: 'text-green-900'
      };
    case 'pending':
      return {
        icon: 'pending',
        title: 'Pago Pendiente',
        message: 'Tu pago está siendo procesado. Te notificaremos cuando esté completado.',
        bgColor: 'bg-yellow-100',
        iconColor: 'text-yellow-600',
        titleColor: 'text-yellow-900'
      };
    case 'failed':
      return {
        icon: 'error',
        title: 'Pago Fallido',
        message: 'Hubo un problema al procesar tu pago. Por favor, intenta nuevamente.',
        bgColor: 'bg-red-100',
        iconColor: 'text-red-600',
        titleColor: 'text-red-900'
      };
    default:
      return {
        icon: 'info',
        title: 'Estado del Pago',
        message: 'Revisa el estado de tu transacción.',
        bgColor: 'bg-blue-100',
        iconColor: 'text-blue-600',
        titleColor: 'text-blue-900'
      };
  }
};

const statusInfo = getStatusInfo();
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">
        <div class="text-center">
          <!-- Icono de estado -->
          <div :class="[statusInfo.bgColor, 'mx-auto flex items-center justify-center h-20 w-20 rounded-full']">
            <!-- Icono de éxito -->
            <svg v-if="statusInfo.icon === 'success'" :class="[statusInfo.iconColor, 'h-10 w-10']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            
            <!-- Icono de pendiente -->
            <svg v-else-if="statusInfo.icon === 'pending'" :class="[statusInfo.iconColor, 'h-10 w-10']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            
            <!-- Icono de error -->
            <svg v-else-if="statusInfo.icon === 'error'" :class="[statusInfo.iconColor, 'h-10 w-10']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            
            <!-- Icono de información -->
            <svg v-else :class="[statusInfo.iconColor, 'h-10 w-10']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>

          <!-- Título y mensaje -->
          <h2 :class="[statusInfo.titleColor, 'mt-6 text-3xl font-extrabold']">
            {{ statusInfo.title }}
          </h2>
          <p class="mt-2 text-sm text-gray-600">
            {{ statusInfo.message }}
          </p>

          <!-- Información de la transacción -->
          <div v-if="nroPago" class="mt-6 bg-white shadow rounded-lg p-4">
            <div class="text-sm text-gray-500">
              <p class="font-medium">Número de referencia:</p>
              <p class="font-mono text-gray-900 mt-1">{{ nroPago }}</p>
            </div>
          </div>

          <!-- Fecha y hora -->
          <div class="mt-4 text-xs text-gray-500">
            <p>Fecha: {{ new Date().toLocaleDateString('es-BO', { 
              year: 'numeric', 
              month: 'long', 
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            }) }}</p>
          </div>
        </div>

        <!-- Acciones -->
        <div class="mt-8 space-y-3">
          <!-- Botón principal según el estado -->
          <div v-if="status === 'success'" class="space-y-3">
            <Link 
              :href="route('ventas.index')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              Ver mis compras
            </Link>
            <Link 
              :href="route('dashboard')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Ir al dashboard
            </Link>
          </div>

          <div v-else-if="status === 'pending'" class="space-y-3">
            <button 
              onclick="window.location.reload()"
              class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              Actualizar estado
            </button>
            <Link 
              :href="route('dashboard')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Ir al dashboard
            </Link>
          </div>

          <div v-else-if="status === 'failed'" class="space-y-3">
            <Link 
              :href="route('pagofacil.index')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Intentar nuevamente
            </Link>
            <Link 
              :href="route('dashboard')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Ir al dashboard
            </Link>
          </div>

          <div v-else class="space-y-3">
            <Link 
              :href="route('dashboard')" 
              class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Ir al dashboard
            </Link>
          </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 text-center">
          <p class="text-xs text-gray-500">
            Si tienes problemas con tu pago, 
            <Link href="/contacto" class="font-medium text-blue-600 hover:text-blue-500">
              contáctanos
            </Link>
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
