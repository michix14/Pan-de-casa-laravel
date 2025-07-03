<script setup>
import { onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

defineProps({ usuarios: Array, visitas: Number })

const page = usePage()

onMounted(() => {
  if (page.props.flash.success) {
    toast.success(page.props.flash.success, { autoClose: 3000 })
  }
})

const eliminar = (id) => {
  if (confirm('¿Estás seguro de eliminar este usuario?')) {
    router.delete(route('usuarios.destroy', id))
  }
}
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="min-h-screen bg-gray-50 dark:bg-gray-800">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 shadow-sm border-b dark:border-gray-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Usuarios</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Gestiona los usuarios del sistema</p>
            </div>
            <div class="mt-4 sm:mt-0">
              <Link
                :href="route('usuarios.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Usuario
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div v-if="usuarios.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay usuarios</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comienza creando tu primer usuario.</p>
        </div>

        <!-- Desktop Table -->
        <div v-else class="hidden lg:block bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Correo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
              <tr
                v-for="usuario in usuarios"
                :key="usuario.id"
                class="hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ usuario.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ usuario.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="usuario.is_gerente" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">Gerente</span>
                  <span v-if="usuario.is_cajero" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold ml-1">Cajero</span>
                  <span v-if="usuario.is_cliente" class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold ml-1">Cliente</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <Link
                      :href="route('usuarios.edit', usuario.id)"
                      class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md transition-colors duration-150"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Editar
                    </Link>
                    <button
                      @click="eliminar(usuario.id)"
                      class="inline-flex items-center px-3 py-1 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition-colors duration-150"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/>
                      </svg>
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
          <div
            v-for="usuario in usuarios"
            :key="usuario.id"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden"
          >
            <div class="p-4">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ usuario.name }}</h3>
              <p class="text-sm text-gray-500 dark:text-gray-300 truncate">{{ usuario.email }}</p>
              <div class="mt-2">
                <span v-if="usuario.is_gerente" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">Gerente</span>
                <span v-if="usuario.is_cajero" class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold ml-1">Cajero</span>
                <span v-if="usuario.is_cliente" class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold ml-1">Cliente</span>
              </div>
              <div class="mt-4 flex space-x-2">
                <Link
                  :href="route('usuarios.edit', usuario.id)"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-md transition-colors duration-150"
                >
                  Editar
                </Link>
                <button
                  @click="eliminar(usuario.id)"
                  class="flex-1 inline-flex justify-center items-center px-3 py-2 text-sm bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition-colors duration-150"
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
