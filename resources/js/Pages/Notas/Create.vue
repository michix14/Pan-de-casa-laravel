<script setup>
import { reactive, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const { productos } = defineProps({
    productos: Array,
});

const form = reactive({
    fecha_realizado: "",
    tipo_nota: "entrada",
    detalles: [
        {
            id_producto: "",
            cantidad: 1,
        },
    ],
});

// Evita agregar fila si ya están todos los productos
const agregarFila = () => {
    const productosSeleccionados = form.detalles.map((d) => d.id_producto);
    const disponibles = productos.filter(
        (p) => !productosSeleccionados.includes(p.id)
    );

    if (disponibles.length === 0) {
        alert("Ya se agregaron todos los productos.");
        return;
    }

    form.detalles.push({
        id_producto: "",
        cantidad: 1,
    });
};

const eliminarFila = (index) => {
    form.detalles.splice(index, 1);
};

// Cálculo de subtotal
const calcularSubtotal = (detalle) => {
    const producto = productos.find(
        (p) => p.id === parseInt(detalle.id_producto)
    );
    if (!producto) return 0;
    return Number(producto.precio) * detalle.cantidad;
};

const total = computed(() => {
    return form.detalles.reduce(
        (acc, detalle) => acc + calcularSubtotal(detalle),
        0
    );
});

const enviarFormulario = () => {
    router.post("/notas", {
        fecha_realizado: form.fecha_realizado,
        tipo_nota: form.tipo_nota,
        detalles: form.detalles,
    });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto p-6">
            <form
                @submit.prevent="enviarFormulario"
                class="space-y-6 bg-white shadow p-6 rounded-lg border border-gray-200"
            >
                <h1 class="text-3xl font-bold mb-4">Crear Nota</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Fecha</label
                        >
                        <input
                            type="date"
                            v-model="form.fecha_realizado"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Tipo de nota</label
                        >
                        <select
                            v-model="form.tipo_nota"
                            required
                            class="w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">
                        Detalle de productos
                    </h2>

                    <div
                        v-if="form.detalles.length === 0"
                        class="text-gray-500 text-sm mb-4"
                    >
                        No hay productos agregados. Presiona “Agregar Producto”.
                    </div>

                    <table
                        v-if="form.detalles.length > 0"
                        class="w-full border text-sm"
                    >
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="p-2">Producto</th>
                                <th class="p-2">Cantidad</th>
                                <th class="p-2">Subtotal</th>
                                <th class="p-2 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(detalle, index) in form.detalles"
                                :key="index"
                            >
                                <td class="p-2">
                                    <select
                                        v-model="detalle.id_producto"
                                        required
                                        class="w-full border-gray-300 rounded-md"
                                    >
                                        <option value="" disabled>
                                            Seleccione
                                        </option>
                                        <option
                                            v-for="p in productos"
                                            :key="p.id"
                                            :value="p.id"
                                            :disabled="
                                                form.detalles.some(
                                                    (d, i) =>
                                                        i !== index &&
                                                        d.id_producto === p.id
                                                )
                                            "
                                        >
                                            {{ p.nombre }} (Bs
                                            {{ Number(p.precio).toFixed(2) }})
                                        </option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input
                                        type="number"
                                        v-model.number="detalle.cantidad"
                                        min="1"
                                        required
                                        class="w-full border-gray-300 rounded-md"
                                    />
                                </td>
                                <td class="p-2">
                                    Bs
                                    {{ calcularSubtotal(detalle).toFixed(2) }}
                                </td>
                                <td class="p-2 text-center">
                                    <button
                                        type="button"
                                        @click="eliminarFila(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button
                        type="button"
                        @click="agregarFila"
                        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                    >
                        Agregar Producto
                    </button>
                </div>

                <div class="text-right">
                    <span class="text-lg font-bold mr-4">
                        Total: Bs {{ total.toFixed(2) }}
                    </span>
                    <button
                        type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow"
                    >
                        Guardar Nota
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
