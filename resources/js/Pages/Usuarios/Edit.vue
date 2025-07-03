<script setup>
import Form from './Form.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  usuario: Object
})

const form = useForm({
  name: props.usuario.name,
  email: props.usuario.email,
  password: '',
  rol: props.usuario.rol
})

const rolesDisponibles = {
  gerente: 'Gerente',
  cajero: 'Cajero',
  cliente: 'Cliente',
}

const submit = () => {
  form.put(route('usuarios.update', props.usuario.id))
}
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-700">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 dark:bg-gray-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex items-center justify-between">
            <div>
              <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-3">
                <Link :href="route('usuarios.index')" class="hover:text-gray-700">Usuarios</Link>
                <span>/</span>
                <span class="text-gray-900">Editar</span>
              </nav>
              <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar usuario</h1>
            </div>
            <Link
              :href="route('usuarios.index')"
              class="text-gray-600 hover:text-gray-900 text-sm font-medium"
            >
              Cancelar
            </Link>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
          <div class="p-8">
            <Form :form="form" :rolesDisponibles="rolesDisponibles" :isEdit="true" @submit="submit" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
