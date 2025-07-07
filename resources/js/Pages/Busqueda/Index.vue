<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  resultados: Object,
  busqueda: String,
  visitas: Number
});

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const getTotalResultados = () => {
  const usuarios = props.resultados.usuarios?.length || 0;
  const productos = props.resultados.productos?.length || 0;
  const ventas = props.resultados.ventas?.length || 0;
  return usuarios + productos + ventas;
};
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col">
            <h1 class="text-3xl font-bold text-gray-900">Resultados de Búsqueda</h1>
            <p v-if="busqueda" class="mt-1 text-sm text-gray-600">
              Se encontraron {{ getTotalResultados() }} resultados para "<span class="font-medium">{{ busqueda }}</span>"
            </p>
            <p v-else class="mt-1 text-sm text-gray-600">
              Ingresa un término de búsqueda para ver resultados
            </p>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Sin término de búsqueda -->
        <div v-if="!busqueda" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Búsqueda Global</h3>
          <p class="mt-1 text-sm text-gray-500">Utiliza el buscador para encontrar usuarios, productos o ventas.</p>
        </div>

        <!-- No hay resultados con búsqueda -->
        <div v-else-if="getTotalResultados() === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
          <p class="mt-1 text-sm text-gray-500">Intenta con otros términos de búsqueda.</p>
        </div>

        <!-- Resultados -->
        <div v-else class="space-y-8">
          <!-- Usuarios -->
          <div v-if="resultados.usuarios && resultados.usuarios.length > 0" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-2.096a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Usuarios ({{ resultados.usuarios.length }})
              </h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div v-for="usuario in resultados.usuarios" :key="usuario.id" class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">{{ usuario.name.charAt(0).toUpperCase() }}</span>
                      </div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ usuario.name }}</p>
                      <p class="text-sm text-gray-500">{{ usuario.email }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ usuario.role || 'Usuario' }}
                    </span>
                    <Link :href="route('usuarios.show', usuario.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      Ver detalles
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Productos -->
          <div v-if="resultados.productos && resultados.productos.length > 0" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Productos ({{ resultados.productos.length }})
              </h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div v-for="producto in resultados.productos" :key="producto.id" class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-medium text-gray-900">{{ producto.nombre }}</p>
                      <p class="text-lg font-semibold text-green-600">Bs. {{ producto.precio }}</p>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ producto.descripcion }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                      <span class="text-xs text-gray-500">Stock: {{ producto.stock }}</span>
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        producto.disponible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]">
                        {{ producto.disponible ? 'Disponible' : 'No disponible' }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <Link :href="route('productos.show', producto.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      Ver detalles
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Ventas -->
          <div v-if="resultados.ventas && resultados.ventas.length > 0" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                Ventas ({{ resultados.ventas.length }})
              </h2>
            </div>
            <div class="divide-y divide-gray-200">
              <div v-for="venta in resultados.ventas" :key="venta.id" class="px-6 py-4 hover:bg-gray-50">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-medium text-gray-900">Venta #{{ venta.id }}</p>
                      <p class="text-lg font-semibold text-purple-600">Bs. {{ venta.total }}</p>
                    </div>
                    <div class="flex items-center space-x-4 mt-2">
                      <span class="text-xs text-gray-500">
                        {{ formatearFecha(venta.created_at) }}
                      </span>
                      <span v-if="venta.pedido" :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        venta.pedido.estado === 'completado' ? 'bg-green-100 text-green-800' : 
                        venta.pedido.estado === 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-gray-100 text-gray-800'
                      ]">
                        {{ venta.pedido.estado }}
                      </span>
                      <span v-if="venta.pedido" class="text-xs text-gray-500">
                        Tipo: {{ venta.pedido.tipo }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <Link :href="route('ventas.show', venta.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      Ver detalles
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
