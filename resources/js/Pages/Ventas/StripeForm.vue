<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  venta: Object,
  stripeKey: String,
});

const csrf = usePage().props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
</script>

<template>
  <AppLayout>
    <div class="max-w-xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <h1 class="text-2xl font-bold mb-6">Pago con Tarjeta</h1>
      
      <div v-if="$page.props.flash?.error" class="bg-red-100 text-red-800 p-4 rounded mb-4">
        {{ $page.props.flash.error }}
      </div>
      <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div class="bg-white shadow-md rounded-lg p-6 space-y-4 border">
        <p><strong>Cliente:</strong> {{ venta.pedido.usuario.name }}</p>
        <p><strong>Total:</strong> Bs {{ Number(venta.total).toFixed(2) }}</p>

        <form :action="route('stripe.procesar')" method="POST">
          <!-- ✅ CSRF Token obligatorio -->
          <input type="hidden" name="_token" :value="csrf" />
          <input type="hidden" name="venta_id" :value="venta.id" />

          <button
            type="submit"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg"
          >
            Pagar con Tarjeta (Stripe)
          </button>
        </form>

        <button
          @click="$inertia.visit(route('ventas.index'))"
          class="w-full mt-4 border border-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-100"
        >
          Cancelar
        </button>
      </div>
    </div>
  </AppLayout>
</template>
