<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-yellow-100 via-orange-50 to-white dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex flex-col items-center justify-center px-6 py-12">
    <!-- Logo -->
    <img
      src="https://res.cloudinary.com/dtubyyb7z/image/upload/v1751534767/Pan_de_casa_logo_pd4jaa.jpg"
      alt="Pan de Casa"
      class="w-32 h-32 rounded-full shadow-lg mb-6 border-4 border-white dark:border-gray-700"
    />

    <!-- Título -->
    <h1 class="text-4xl md:text-5xl font-bold text-orange-800 dark:text-yellow-400 text-center mb-4">
      Bienvenido a Pan de Casa
    </h1>

    <!-- Subtítulo -->
    <p class="text-lg text-gray-700 dark:text-gray-300 text-center max-w-xl">
      Tu sistema inteligente de gestión para panaderías. Controla productos, ventas, promociones y más desde un solo lugar.
    </p>

    <!-- Botones -->
    <div class="mt-8 flex flex-col sm:flex-row gap-4">
      <Link
        :href="route('login')"
        class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg shadow transition"
      >
        Iniciar sesión
      </Link>

      <Link
        :href="route('register')"
        class="px-6 py-3 border border-orange-600 text-orange-700 hover:bg-orange-600 hover:text-white rounded-lg transition"
      >
        Registrarse
      </Link>
    </div>

    <!-- Pie de página -->
    <div class="mt-10 text-sm text-gray-500 dark:text-gray-400">
      © 2025 Pan de Casa. Todos los derechos reservados.
    </div>
  </div>
</template>
