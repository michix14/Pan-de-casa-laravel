<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Test Callback PagoFácil</h1>
              <p class="text-sm text-gray-600">Herramienta para probar la funcionalidad del callback</p>
            </div>
          </div>
        </div>

        <!-- Formulario de prueba -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-6">Simular Callback de PagoFácil</h2>
          
          <form @submit.prevent="enviarCallback" class="space-y-6">
            <!-- PedidoID -->
            <div>
              <label for="pedido_id" class="block text-sm font-medium text-gray-700">
                PedidoID (Referencia del pago)
              </label>
              <input
                id="pedido_id"
                v-model="form.PedidoID"
                type="text"
                placeholder="ej: venta-9-1751920294"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              />
              <p class="mt-1 text-xs text-gray-500">
                Formato: venta-{id}-{timestamp}. 
                <button type="button" @click="generarPedidoIDEjemplo" class="text-blue-600 hover:text-blue-800 underline">
                  Generar ejemplo
                </button>
                | 
                <button type="button" @click="cargarPagosExistentes" class="text-green-600 hover:text-green-800 underline">
                  Ver pagos existentes
                </button>
              </p>
            </div>

            <!-- Fecha -->
            <div>
              <label for="fecha" class="block text-sm font-medium text-gray-700">
                Fecha del pago
              </label>
              <input
                id="fecha"
                v-model="form.Fecha"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              />
            </div>

            <!-- Hora -->
            <div>
              <label for="hora" class="block text-sm font-medium text-gray-700">
                Hora del pago
              </label>
              <input
                id="hora"
                v-model="form.Hora"
                type="time"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              />
            </div>

            <!-- Método de pago -->
            <div>
              <label for="metodo_pago" class="block text-sm font-medium text-gray-700">
                Método de Pago
              </label>
              <select
                id="metodo_pago"
                v-model="form.MetodoPago"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              >
                <option value="">Selecciona un método</option>
                <option value="QR">QR</option>
                <option value="TigoMoney">Tigo Money</option>
                <option value="Banco">Banco</option>
              </select>
            </div>

            <!-- Estado -->
            <div>
              <label for="estado" class="block text-sm font-medium text-gray-700">
                Estado del Pago
              </label>
              <select
                id="estado"
                v-model="form.Estado"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
              >
                <option value="">Selecciona un estado</option>
                <option value="completado">Completado</option>
                <option value="pagado">Pagado</option>
                <option value="2">Estado 2 (Completado)</option>
                <option value="rechazado">Rechazado</option>
                <option value="cancelado">Cancelado</option>
                <option value="3">Estado 3 (Rechazado)</option>
                <option value="pendiente">Pendiente</option>
                <option value="1">Estado 1 (Pendiente)</option>
              </select>
            </div>

            <!-- Botón enviar -->
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="enviando"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="enviando" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Enviando...
                </span>
                <span v-else>Enviar Callback</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Resultado -->
        <div v-if="resultado" class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Resultado del Callback</h3>
          
          <div class="space-y-4">
            <!-- Estado de la respuesta -->
            <div class="flex items-center space-x-2">
              <div v-if="resultado.success" class="w-4 h-4 bg-green-500 rounded-full"></div>
              <div v-else class="w-4 h-4 bg-red-500 rounded-full"></div>
              <span :class="resultado.success ? 'text-green-600' : 'text-red-600'" class="font-medium">
                {{ resultado.success ? 'Exitoso' : 'Error' }}
              </span>
            </div>

            <!-- Respuesta del servidor -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Respuesta del Servidor:</h4>
              <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ JSON.stringify(resultado.data, null, 2) }}</pre>
            </div>

            <!-- Mensaje -->
            <div v-if="resultado.message">
              <p :class="resultado.success ? 'text-green-600' : 'text-red-600'" class="text-sm">
                {{ resultado.message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Lista de pagos existentes -->
        <div v-if="pagosExistentes" class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Pagos Existentes en la Base de Datos</h3>
          
          <div class="mb-4 text-sm text-gray-600">
            <p><strong>Total de pagos:</strong> {{ pagosExistentes.total_pagos }}</p>
            <p><strong>Pendientes:</strong> {{ pagosExistentes.pagos_pendientes }}</p>
            <p><strong>Completados:</strong> {{ pagosExistentes.pagos_completados }}</p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Venta ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Referencia Externa</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="pago in pagosExistentes.pagos" :key="pago.id" class="hover:bg-gray-50">
                  <td class="px-3 py-2 text-sm text-gray-900">{{ pago.id }}</td>
                  <td class="px-3 py-2 text-sm text-gray-900">{{ pago.venta_id }}</td>
                  <td class="px-3 py-2 text-sm font-mono text-gray-600">{{ pago.referencia_externa }}</td>
                  <td class="px-3 py-2 text-sm">
                    <span :class="{
                      'bg-yellow-100 text-yellow-800': pago.estado === 'pendiente',
                      'bg-green-100 text-green-800': pago.estado === 'completado',
                      'bg-red-100 text-red-800': pago.estado === 'rechazado'
                    }" class="px-2 py-1 rounded-full text-xs font-medium">
                      {{ pago.estado }}
                    </span>
                  </td>
                  <td class="px-3 py-2 text-sm text-gray-900">Bs {{ pago.monto }}</td>
                  <td class="px-3 py-2 text-sm">
                    <button 
                      @click="usarReferencia(pago.referencia_externa)"
                      class="text-blue-600 hover:text-blue-800 text-xs underline"
                    >
                      Usar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import axios from 'axios';

defineProps({
  visitas: {
    type: Number,
    default: 0
  }
});

const enviando = ref(false);
const resultado = ref(null);
const pagosExistentes = ref(null);

const form = ref({
  PedidoID: '',
  Fecha: new Date().toISOString().split('T')[0],
  Hora: new Date().toTimeString().split(' ')[0].substring(0, 5),
  MetodoPago: 'QR',
  Estado: 'completado'
});

const generarPedidoIDEjemplo = () => {
  const timestamp = Math.floor(Date.now() / 1000);
  const ventaId = Math.floor(Math.random() * 100) + 1; // ID aleatorio entre 1 y 100
  form.value.PedidoID = `venta-${ventaId}-${timestamp}`;
};

const cargarPagosExistentes = async () => {
  try {
    const response = await axios.get('/pagofacil/debug-pagos');
    pagosExistentes.value = response.data;
  } catch (error) {
    console.error('Error cargando pagos:', error);
  }
};

const usarReferencia = (referencia) => {
  form.value.PedidoID = referencia;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const enviarCallback = async () => {
  enviando.value = true;
  resultado.value = null;

  try {
    const response = await axios.post('/pagofacil/callback', form.value);
    
    resultado.value = {
      success: true,
      data: response.data,
      message: 'Callback enviado exitosamente'
    };
  } catch (error) {
    console.error('Error enviando callback:', error);
    
    resultado.value = {
      success: false,
      data: error.response?.data || error.message,
      message: 'Error al enviar callback'
    };
  } finally {
    enviando.value = false;
  }
};
</script>
