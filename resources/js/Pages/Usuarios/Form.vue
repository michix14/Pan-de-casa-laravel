<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  form: Object,
  rolesDisponibles: Object,
  isEdit: Boolean,
})

const emit = defineEmits(['submit'])
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          {{ isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}
        </h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
          {{ isEdit ? 'Modifica los datos del usuario' : 'Completa los campos para crear un usuario' }}
        </p>
      </div>

      <!-- Form Card -->
      <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
        <form @submit.prevent="emit('submit')" class="space-y-6 p-6">
          <!-- Nombre -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
              Nombre completo <span class="text-red-500">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.name }"
              placeholder="Nombre del usuario"
            />
            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
              Correo electrónico <span class="text-red-500">*</span>
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.email }"
              placeholder="ejemplo@correo.com"
            />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <!-- Contraseña -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
              Contraseña <span v-if="isEdit">(opcional)</span> <span v-else class="text-red-500">*</span>
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.password }"
              placeholder="Mínimo 8 caracteres"
            />
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <!-- Rol -->
          <div>
            <label for="rol" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
              Rol del usuario <span class="text-red-500">*</span>
            </label>
            <select
              id="rol"
              v-model="form.rol"
              class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.rol }"
            >
              <option disabled value="">Seleccione un rol</option>
              <option v-for="(label, value) in rolesDisponibles" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <p v-if="form.errors.rol" class="mt-2 text-sm text-red-600">{{ form.errors.rol }}</p>
          </div>

          <!-- Botones -->
          <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200 dark:border-gray-600">
            <button
              type="submit"
              :disabled="form.processing"
              class="flex-1 sm:flex-none inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors duration-200"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ form.processing ? 'Guardando...' : (isEdit ? 'Actualizar Usuario' : 'Crear Usuario') }}
            </button>

            <button
              type="button"
              @click="$inertia.visit(route('usuarios.index'))"
              class="flex-1 sm:flex-none inline-flex justify-center items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
