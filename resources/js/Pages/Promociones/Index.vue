<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { onMounted } from "vue";
import { toast } from "vue3-toastify";

defineProps({
    promociones: Array,
    visitas: Number,
});

const page = usePage();

onMounted(() => {
    const flashSuccess = page.props.flash?.success;
    if (flashSuccess) {
        toast.success(flashSuccess);
    }
});

const eliminarPromocion = (id) => {
    if (confirm("¿Deseas eliminar esta promoción?")) {
        router.delete(`/promociones/${id}`, {
            preserveScroll: true,
            onSuccess: () => toast.success("Promoción eliminada correctamente"),
            onError: () => toast.error("Ocurrió un error al eliminar"),
        });
    }
};
</script>

<template>
    <AppLayout :visitas="visitas">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <!-- Header -->
            <div
                class="bg-white dark:bg-gray-800 shadow-sm border-b dark:border-gray-700"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h1
                                class="text-3xl font-bold text-gray-900 dark:text-white"
                            >
                                Promociones
                            </h1>
                            <p
                                class="mt-1 text-sm text-gray-600 dark:text-gray-300"
                            >
                                Gestión de promociones temporales
                            </p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <Link
                                href="/promociones/create"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow"
                            >
                                + Crear Promoción
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div v-if="promociones.length === 0" class="text-center py-12">
                    <h3
                        class="text-lg font-semibold text-gray-700 dark:text-gray-300"
                    >
                        No hay promociones registradas
                    </h3>
                </div>

                <div
                    v-else
                    class="overflow-hidden bg-white dark:bg-gray-800 rounded shadow"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase"
                                >
                                    Título
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase"
                                >
                                    Vigencia
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase"
                                >
                                    Productos
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-100 dark:divide-gray-700"
                        >
                            <tr
                                v-for="promo in promociones"
                                :key="promo.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                <td
                                    class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white"
                                >
                                    {{ promo.nombre }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    {{ promo.fecha_inicio }} al
                                    {{ promo.fecha_fin }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm font-semibold"
                                    :class="
                                        promo.activa
                                            ? 'text-green-600'
                                            : 'text-gray-400 dark:text-gray-500'
                                    "
                                >
                                    {{ promo.activa ? "Activa" : "Inactiva" }}
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    <ul class="list-disc pl-4">
                                        <li
                                            v-for="detalle in promo.detalles"
                                            :key="detalle.id"
                                        >
                                            {{ detalle.producto.nombre }}: Bs
                                            {{ detalle.precio_promocional }}
                                        </li>
                                    </ul>
                                </td>
                                <td
                                    class="px-6 py-4 text-sm text-right space-x-2"
                                >
                                    <Link
                                        :href="`/promociones/${promo.id}/edit`"
                                        class="text-blue-600 hover:text-blue-800 dark:hover:text-blue-400"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="eliminarPromocion(promo.id)"
                                        class="text-red-600 hover:text-red-800 dark:hover:text-red-400"
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
