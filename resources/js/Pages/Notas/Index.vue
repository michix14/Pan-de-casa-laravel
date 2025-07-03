<script setup>
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { toast } from "vue3-toastify";

defineProps({
    notas: Array,
    visitas: Number,
});

const eliminarNota = (id) => {
    if (confirm("¿Estás seguro de que deseas eliminar esta nota?")) {
        router.delete(`/notas/${id}`, {
            onSuccess: () => toast.success("Nota eliminada correctamente"),
            onError: () => toast.error("Ocurrió un error al eliminar la nota"),
        });
    }
};
</script>

<template>
    <AppLayout :visitas="visitas">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border-b dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Notas</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                Registros de entrada y salida de productos
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <Link
                                href="/notas/create"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Crear Nota
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div v-if="notas.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6 4a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay notas registradas</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Puedes empezar registrando una nueva nota de entrada o salida.
                    </p>
                </div>

                <div v-else class="overflow-hidden rounded-lg shadow bg-white dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="nota in notas" :key="nota.id_nota" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ nota.id_nota }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ nota.fecha_realizado }}</td>
                                <td class="px-6 py-4 text-sm font-semibold capitalize"
                                    :class="{
                                        'text-green-600': nota.tipo_nota === 'entrada',
                                        'text-red-600': nota.tipo_nota === 'salida',
                                    }">
                                    {{ nota.tipo_nota }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    Bs {{ parseFloat(nota.total || 0).toFixed(2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <Link
                                        :href="`/notas/${nota.id_nota}/edit`"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="eliminarNota(nota.id_nota)"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

