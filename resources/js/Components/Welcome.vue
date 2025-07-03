<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div>
        <!-- Fondo blanco con soporte modo oscuro -->
        <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <ApplicationLogo class="block h-12 w-auto" />

            <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
                Welcome to your Jetstream application!
            </h1>

            <p class="mt-6 text-gray-500 dark:text-gray-300 leading-relaxed">
                Laravel Jetstream provides a beautiful, robust starting point...
            </p>
        </div>

        <!-- Contenido gris claro adaptado -->
        <div class="bg-gray-200 dark:bg-gray-900 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
            <!-- Documentation -->
            <div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 stroke-gray-400 dark:stroke-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6v6l4 2M12 3a9 9 0 110 18 9 9 0 010-18z" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                        <a href="https://laravel.com/docs">Documentation</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed">
                    Laravel has wonderful documentation covering...
                </p>

                <p class="mt-4 text-sm">
                    <a href="https://laravel.com/docs" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-400">
                        Explore the documentation
                        <svg class="ms-1 w-5 h-5 fill-indigo-500 dark:fill-indigo-400" viewBox="0 0 20 20" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M12.293 4.293a1 1 0 011.414 0L17 7.586a1 1 0 010 1.414l-3.293 3.293a1 1 0 01-1.414-1.414L14.586 9H7a1 1 0 110-2h7.586l-1.293-1.293a1 1 0 010-1.414z"
                                  clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <!-- Laracasts -->
            <div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 stroke-gray-400 dark:stroke-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                        <a href="https://laracasts.com">Laracasts</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed">
                    Laracasts offers thousands of video tutorials...
                </p>

                <p class="mt-4 text-sm">
                    <a href="https://laracasts.com" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-400">
                        Start watching Laracasts
                        <svg class="ms-1 w-5 h-5 fill-indigo-500 dark:fill-indigo-400" viewBox="0 0 20 20" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M12.293 4.293a1 1 0 011.414 0L17 7.586a1 1 0 010 1.414l-3.293 3.293a1 1 0 01-1.414-1.414L14.586 9H7a1 1 0 110-2h7.586l-1.293-1.293a1 1 0 010-1.414z"
                                  clip-rule="evenodd" />
                        </svg>
                    </a>
                </p>
            </div>

            <!-- Tailwind -->
            <div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 stroke-gray-400 dark:stroke-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                        <a href="https://tailwindcss.com/">Tailwind</a>
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed">
                    Laravel Jetstream is built with Tailwind...
                </p>
            </div>

            <!-- Authentication -->
            <div>
                <div class="flex items-center">
                    <svg class="w-6 h-6 stroke-gray-400 dark:stroke-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                        Authentication
                    </h2>
                </div>

                <p class="mt-4 text-gray-500 dark:text-gray-300 text-sm leading-relaxed">
                    Authentication and registration views are included...
                </p>
            </div>
        </div>
    </div>
</template>

