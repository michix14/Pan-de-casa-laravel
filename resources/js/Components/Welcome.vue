<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { onMounted, ref } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const props = defineProps({
  reporteVentas: Object,
  ventasPorDia: Array
});

const chartRef = ref(null);

onMounted(() => {
  const fechas = props.ventasPorDia.map(item => item.fecha);
  const totales = props.ventasPorDia.map(item => item.total);

  new Chart(chartRef.value, {
    type: 'line',
    data: {
      labels: fechas,
      datasets: [{
        label: 'Total vendido por día',
        data: totales,
        borderColor: 'rgb(255, 99, 132)',
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Monto Bs' }
        },
        x: {
          title: { display: true, text: 'Fecha' }
        }
      }
    }
  });
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-yellow-50 via-orange-100 to-white dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 p-6 lg:p-12">
    <!-- Cabecera -->
    <div class="max-w-4xl mx-auto text-center">
      <ApplicationLogo class="mx-auto h-16 w-auto mb-6" />

      <h1 class="text-4xl font-bold text-orange-800 dark:text-yellow-400">
        ¡Bienvenido👨‍🍳!
      </h1>
      <p class="mt-2 text-gray-700 dark:text-gray-300 text-lg">
        Este es tu panel de gestión para <strong>Pan de Casa</strong>. Controla productos, pedidos y más.
      </p>
    </div>

    <!-- Accesos rápidos -->
    <div class="max-w-6xl mx-auto mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Productos -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-orange-700 dark:text-yellow-300 mb-2">Productos</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">
          Agrega, edita y gestiona tus productos en stock.
        </p>
        <router-link
          :to="route('productos.index')"
          class="mt-4 inline-block text-orange-600 dark:text-yellow-400 hover:underline"
        >
          Ver productos →
        </router-link>
      </div>

      <!-- Pedidos -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-orange-700 dark:text-yellow-300 mb-2">Pedidos</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">
          Revisa y gestiona los pedidos realizados.
        </p>
        <router-link
          :to="route('pedidos.index')"
          class="mt-4 inline-block text-orange-600 dark:text-yellow-400 hover:underline"
        >
          Ver pedidos →
        </router-link>
      </div>

      <!-- Ventas -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-orange-700 dark:text-yellow-300 mb-2">Ventas</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">
          Visualiza el historial de ventas y exporta reportes.
        </p>
        <router-link
          :to="route('ventas.index')"
          class="mt-4 inline-block text-orange-600 dark:text-yellow-400 hover:underline"
        >
          Ver ventas →
        </router-link>
      </div>
    </div>

    <div class="max-w-6xl mx-auto mt-16">
      <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Ventas de los últimos días</h2>
      <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
        <canvas ref="chartRef" height="100"></canvas>
      </div>
    </div>
  </div>
</template>
