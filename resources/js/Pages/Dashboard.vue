<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useDarkMode } from '@/theme'
import { defineProps, onMounted, ref } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const { isDark } = useDarkMode()

const props = defineProps({
  completadas: Number,
  pendientes: Number,
  canceladas: Number,
  montoTotal: Number,
  ventasPorDia: Array
})

const chartRef = ref(null)

onMounted(() => {
  if (chartRef.value) {
    new Chart(chartRef.value, {
      type: 'line',
      data: {
        labels: props.ventasPorDia.map(v => v.fecha),
        datasets: [{
          label: 'Ventas por Día',
          data: props.ventasPorDia.map(v => v.total),
          fill: true,
          backgroundColor: 'rgba(59, 130, 246, 0.2)',
          borderColor: 'rgba(59, 130, 246, 1)',
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            labels: {
              color: isDark.value ? '#fff' : '#000'
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: isDark.value ? '#fff' : '#000'
            }
          },
          y: {
            ticks: {
              color: isDark.value ? '#fff' : '#000'
            }
          }
        }
      }
    })
  }
})
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
        Dashboard
      </h2>
    </template> 
    <div v-if="$page.props.auth.user.is_gerente">
      <div class="py-12 space-y-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <!-- KPIs -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="p-6 bg-white dark:bg-gray-800 rounded shadow text-center">
              <h3 class="text-gray-500 dark:text-gray-300 text-sm">Completadas</h3>
              <p class="text-3xl font-bold text-green-600">{{ completadas }}</p>
            </div>
            <div class="p-6 bg-white dark:bg-gray-800 rounded shadow text-center">
              <h3 class="text-gray-500 dark:text-gray-300 text-sm">Pendientes</h3>
              <p class="text-3xl font-bold text-yellow-500">{{ pendientes }}</p>
            </div>
            <div class="p-6 bg-white dark:bg-gray-800 rounded shadow text-center">
              <h3 class="text-gray-500 dark:text-gray-300 text-sm">Canceladas</h3>
              <p class="text-3xl font-bold text-red-500">{{ canceladas }}</p>
            </div>
            <div class="p-6 bg-white dark:bg-gray-800 rounded shadow text-center">
              <h3 class="text-gray-500 dark:text-gray-300 text-sm">Monto Total (Bs)</h3>
              <p class="text-3xl font-bold text-blue-600">{{ Number(montoTotal).toFixed(2) }}</p>
            </div>
          </div>
        </div>

        <!-- Gráfico -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="p-6 bg-white dark:bg-gray-800 rounded shadow">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
              Ventas por día
            </h3>
            <canvas ref="chartRef" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Si es cajero -->
    <div v-else-if="$page.props.auth.user.is_cajero" class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg space-y-4">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            ¡Hola, {{ $page.props.auth.user.name }}!
          </h3>
          <p class="text-gray-600 dark:text-gray-300">
            Bienvenido al sistema. Aquí puedes gestionar tus ventas y consultar tu historial.
          </p>

          <!-- Acciones rápidas -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <a href="/ventas/create"
              class="block p-4 bg-blue-600 text-white font-bold rounded-lg text-center hover:bg-blue-700 transition">
              ➕ Registrar nueva venta
            </a>
            <a href="/ventas"
              class="block p-4 bg-indigo-600 text-white font-bold rounded-lg text-center hover:bg-indigo-700 transition">
              📄 Ver historial de ventas
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Otros roles o sin rol válido -->
    <div v-else class="py-12">
      <div class="max-w-3xl mx-auto px-4">
        <div class="p-6 bg-white dark:bg-gray-800 shadow rounded-lg text-center">
          <h3 class="text-lg font-bold text-red-600">Acceso restringido</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Tu rol no tiene acceso a este módulo.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>

</template>
