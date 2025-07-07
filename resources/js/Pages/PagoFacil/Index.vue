<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  venta: {
    type: Object,
    required: false,
    default: null
  },
  visitas: {
    type: Number,
    default: 0
  }
});

const procesandoPago = ref(false);
const qrGenerado = ref(null);
const estadoPago = ref('inicial'); // inicial, generando, esperando, completado, error
const mensajeError = ref('');
const transactionId = ref(null);
const nroPago = ref(null);
const consultandoEstado = ref(false);

const form = useForm({
  venta_id: props.venta?.id || null,
  metodo_pago: 'qr',
  telefono: '',
  ci_nit: ''
});

const metodoPagoSeleccionado = computed(() => form.metodo_pago);

const generarPago = async () => {
  if (!form.venta_id || !props.venta) {
    mensajeError.value = 'Debe seleccionar una venta válida';
    return;
  }

  procesandoPago.value = true;
  estadoPago.value = 'generando';
  mensajeError.value = '';

  try {
    const response = await fetch(route('pagofacil.generar-qr'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(form.data())
    });

    const data = await response.json();

    if (data.success) {
      if (form.metodo_pago === 'qr' && data.qr_image) {
        qrGenerado.value = data.qr_image;
        estadoPago.value = 'esperando';
      } else {
        estadoPago.value = 'esperando';
      }
      
      transactionId.value = data.transaction_id || null;
      nroPago.value = data.nro_pago || null;
      
      // Iniciar polling para verificar el estado del pago
      if (transactionId.value) {
        iniciarConsultaEstado();
      }
    } else {
      estadoPago.value = 'error';
      mensajeError.value = data.message || 'Error al generar el pago';
    }
  } catch (error) {
    console.error('Error:', error);
    estadoPago.value = 'error';
    mensajeError.value = 'Error de conexión. Intente nuevamente.';
  } finally {
    procesandoPago.value = false;
  }
};

const iniciarConsultaEstado = () => {
  const interval = setInterval(async () => {
    if (!transactionId.value || estadoPago.value === 'completado') {
      clearInterval(interval);
      return;
    }

    await consultarEstadoPago();
  }, 5000); // Consultar cada 5 segundos

  // Limpiar el interval después de 10 minutos
  setTimeout(() => clearInterval(interval), 600000);
};

const consultarEstadoPago = async () => {
  if (consultandoEstado.value) return;
  
  consultandoEstado.value = true;

  try {
    const response = await fetch(route('pagofacil.consultar-estado'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        transaction_id: transactionId.value
      })
    });

    const data = await response.json();

    if (data.success) {
      const estado = data.estado;
      
      if (estado === 2) { // Pago completado
        estadoPago.value = 'completado';
        // Redirigir a la página de confirmación
        setTimeout(() => {
          router.visit(route('pagofacil.return', { status: 'success', nro_pago: nroPago.value }));
        }, 2000);
      } else if (estado === 3) { // Pago rechazado
        estadoPago.value = 'error';
        mensajeError.value = 'El pago fue rechazado';
      }
    }
  } catch (error) {
    console.error('Error al consultar estado:', error);
  } finally {
    consultandoEstado.value = false;
  }
};

const reiniciarPago = () => {
  estadoPago.value = 'inicial';
  qrGenerado.value = null;
  mensajeError.value = '';
  transactionId.value = null;
  nroPago.value = null;
  form.reset();
  form.venta_id = props.venta?.id || null;
};

const formatearMoneda = (monto) => {
  if (typeof monto !== 'number' || isNaN(monto)) {
    return 'Bs 0.00';
  }
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB'
  }).format(monto);
};
</script>

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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Pago Fácil</h1>
              <p class="text-sm text-gray-600">Completa tu pago de forma segura</p>
            </div>
          </div>
        </div>

        <!-- Información de la venta -->
        <div v-if="venta && venta.id" class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Resumen del Pedido</h2>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Número de Venta:</span>
              <span class="font-medium">#{{ venta.id }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Total a Pagar:</span>
              <span class="text-lg font-bold text-green-600">{{ formatearMoneda(venta.total || 0) }}</span>
            </div>
            <div class="border-t pt-3">
              <h3 class="text-sm font-medium text-gray-900 mb-2">Productos:</h3>
              <div class="space-y-1">
                <div v-for="detalle in venta.detalles || []" :key="detalle.id" 
                     class="flex justify-between text-sm">
                  <span>{{ detalle.producto?.nombre || 'Producto' }} x{{ detalle.cantidad }}</span>
                  <span>{{ formatearMoneda(detalle.cantidad * detalle.precio_unitario) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error: No hay venta -->
        <div v-else class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error</h3>
              <div class="mt-2 text-sm text-red-700">
                <p>No se pudo cargar la información de la venta. Por favor, intenta nuevamente desde la lista de ventas.</p>
              </div>
              <div class="mt-4">
                <button
                  @click="router.visit(route('ventas.index'))"
                  class="bg-red-100 px-3 py-2 rounded-md text-sm font-medium text-red-800 hover:bg-red-200"
                >
                  Volver a Ventas
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulario de pago -->
        <div v-if="venta && venta.id" class="bg-white rounded-lg shadow-sm p-6">
          <!-- Estado inicial -->
          <div v-if="estadoPago === 'inicial'">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Selecciona el método de pago</h2>
            
            <form @submit.prevent="generarPago" class="space-y-6">
              <!-- Método de pago -->
              <div>
                <label class="text-base font-medium text-gray-900">Método de Pago</label>
                <div class="mt-4 space-y-4">
                  <div class="flex items-center">
                    <input
                      id="qr"
                      v-model="form.metodo_pago"
                      name="metodo_pago"
                      type="radio"
                      value="qr"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    />
                    <label for="qr" class="ml-3 block text-sm font-medium text-gray-700">
                      <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z"/>
                        </svg>
                        <span>Código QR</span>
                      </div>
                      <p class="text-xs text-gray-500 ml-9">Escanea el código QR con tu app bancaria</p>
                    </label>
                  </div>
                  
                  <div class="flex items-center">
                    <input
                      id="tigo_money"
                      v-model="form.metodo_pago"
                      name="metodo_pago"
                      type="radio"
                      value="tigo_money"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    />
                    <label for="tigo_money" class="ml-3 block text-sm font-medium text-gray-700">
                      <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M19.23 15.26l-2.54-.29a1 1 0 00-1.13.72l-.47 1.87a.75.75 0 01-1.36.05l-1.82-3.87a.75.75 0 01.11-.9l2.91-2.91a.75.75 0 01.9-.11l3.87 1.82a.75.75 0 01-.05 1.36l-1.87.47a1 1 0 00-.72 1.13l.29 2.54a.75.75 0 01-1.36.65l-5.94-2.95a.75.75 0 01-.23-1.25l6.32-6.32a.75.75 0 011.25.23l2.95 5.94a.75.75 0 01-.65 1.36z"/>
                        </svg>
                        <span>Tigo Money</span>
                      </div>
                      <p class="text-xs text-gray-500 ml-9">Pago directo desde tu billetera Tigo Money</p>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Campos adicionales para Tigo Money -->
              <div v-if="metodoPagoSeleccionado === 'tigo_money'" class="space-y-4">
                <div>
                  <label for="telefono" class="block text-sm font-medium text-gray-700">
                    Número de teléfono Tigo Money
                  </label>
                  <input
                    id="telefono"
                    name="telefono"
                    v-model="form.telefono"
                    type="tel"
                    placeholder="7XXXXXXX"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    required
                  />
                </div>
              </div>

              <!-- CI/NIT -->
              <div>
                <label for="ci_nit" class="block text-sm font-medium text-gray-700">
                  CI/NIT (opcional)
                </label>
                <input
                  id="ci_nit"
                  name="ci_nit"
                  v-model="form.ci_nit"
                  type="text"
                  placeholder="Ingresa tu CI o NIT"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>

              <!-- Botón de generar pago -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="procesandoPago || !venta"
                  class="w-full sm:w-auto bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="procesandoPago" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Generando...
                  </span>
                  <span v-else>Generar Pago</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Estado generando -->
          <div v-else-if="estadoPago === 'generando'" class="text-center py-8">
            <div class="animate-spin mx-auto mb-4 w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full"></div>
            <h3 class="text-lg font-medium text-gray-900">Generando pago...</h3>
            <p class="text-sm text-gray-600">Por favor espera un momento</p>
          </div>

          <!-- Estado esperando pago -->
          <div v-else-if="estadoPago === 'esperando'" class="text-center py-8">
            <!-- QR Code -->
            <div v-if="qrGenerado" class="mb-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Escanea el código QR</h3>
              <div class="flex justify-center mb-4">
                <img :src="qrGenerado" alt="Código QR" class="border-2 border-gray-200 rounded-lg">
              </div>
              <p class="text-sm text-gray-600">Usa tu app bancaria para escanear este código</p>
            </div>

            <!-- Tigo Money -->
            <div v-else class="mb-6">
              <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19.23 15.26l-2.54-.29a1 1 0 00-1.13.72l-.47 1.87a.75.75 0 01-1.36.05l-1.82-3.87a.75.75 0 01.11-.9l2.91-2.91a.75.75 0 01.9-.11l3.87 1.82a.75.75 0 01-.05 1.36l-1.87.47a1 1 0 00-.72 1.13l.29 2.54a.75.75 0 01-1.36.65l-5.94-2.95a.75.75 0 01-.23-1.25l6.32-6.32a.75.75 0 011.25.23l2.95 5.94a.75.75 0 01-.65 1.36z"/>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">Confirma el pago en tu teléfono</h3>
              <p class="text-sm text-gray-600">Revisa tu teléfono para confirmar el pago con Tigo Money</p>
            </div>

            <!-- Información del pago -->
            <div v-if="venta && venta.total" class="bg-gray-50 rounded-lg p-4 mb-6">
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm text-gray-600">Monto:</span>
                <span class="font-semibold">{{ formatearMoneda(venta.total) }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Referencia:</span>
                <span class="font-mono text-sm">{{ nroPago || 'Generando...' }}</span>
              </div>
            </div>

            <!-- Estado de verificación -->
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-600 mb-4">
              <div v-if="consultandoEstado" class="animate-spin w-4 h-4 border-2 border-gray-300 border-t-blue-600 rounded-full"></div>
              <span>{{ consultandoEstado ? 'Verificando pago...' : 'Esperando confirmación de pago' }}</span>
            </div>

            <!-- Botón para cancelar -->
            <button
              @click="reiniciarPago"
              class="text-gray-600 hover:text-gray-800 text-sm font-medium"
            >
              Cancelar y elegir otro método
            </button>
          </div>

          <!-- Estado completado -->
          <div v-else-if="estadoPago === 'completado'" class="text-center py-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">¡Pago completado!</h3>
            <p class="text-sm text-gray-600">Tu pago ha sido procesado exitosamente</p>
          </div>

          <!-- Estado de error -->
          <div v-else-if="estadoPago === 'error'" class="text-center py-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Error en el pago</h3>
            <p class="text-sm text-red-600 mb-4">{{ mensajeError }}</p>
            <button
              @click="reiniciarPago"
              class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700"
            >
              Intentar nuevamente
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
