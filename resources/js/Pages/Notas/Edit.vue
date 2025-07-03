<script setup>
import { ref, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { toast } from "vue3-toastify";

const props = defineProps({
    nota: Object,
    productos: Array,
});

const form = useForm({
    fecha_realizado: props.nota.fecha_realizado,
    tipo_nota: props.nota.tipo_nota,
    detalles: props.nota.detalles.map((d) => ({
        id_producto: d.id_producto,
        cantidad: d.cantidad,
    })),
});

const agregarDetalle = () => {
    const productosSeleccionados = form.detalles.map((d) => d.id_producto);
    const productoDisponible = props.productos.find(
        (p) => !productosSeleccionados.includes(p.id)
    );
    if (productoDisponible) {
        form.detalles.push({
            id_producto: productoDisponible.id,
            cantidad: 1,
        });
    } else {
        toast.warning("Ya has agregado todos los productos disponibles.");
    }
};

const eliminarDetalle = (index) => {
    form.detalles.splice(index, 1);
};

const productosDisponibles = (indexActual) => {
    return props.productos.filter((p) => {
        return (
            form.detalles[indexActual].id_producto === p.id ||
            !form.detalles.some(
                (d, i) => i !== indexActual && d.id_producto === p.id
            )
        );
    });
};

const submit = () => {
    router.put(route("notas.update", props.nota.id_nota), form, {
        onSuccess: () => {
            toast.success("Nota actualizada correctamente");
        },
        onError: () => {
            toast.error("Error al actualizar la nota");
        },
    });
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto py-10">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                Editar Nota
            </h1>

            <form @submit.prevent="submit" class="space-y-6 bg-white p-6 shadow rounded">
                <div>
                    <label class="block font-medium text-sm text-gray-700">Fecha:</label>
                    <input
                        type="date"
                        v-model="form.fecha_realizado"
                        class="form-input mt-1 block w-full"
                    />
                    <div v-if="form.errors.fecha_realizado" class="text-sm text-red-600 mt-1">
                        {{ form.errors.fecha_realizado }}
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Tipo de Nota:</label>
                    <select
                        v-model="form.tipo_nota"
                        class="form-select mt-1 block w-full"
                    >
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                    <div v-if="form.errors.tipo_nota" class="text-sm text-red-600 mt-1">
                        {{ form.errors.tipo_nota }}
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Detalles:</label>
                    <div
                        v-for="(detalle, index) in form.detalles"
                        :key="index"
                        class="flex items-center gap-4 mt-2"
                    >
                        <select
                            v-model="detalle.id_producto"
                            class="form-select w-full"
                        >
                            <option
                                v-for="producto in productosDisponibles(index)"
                                :key="producto.id"
                                :value="producto.id"
                            >
                                {{ producto.nombre }}
                            </option>
                        </select>
                        <input
                            type="number"
                            min="1"
                            v-model="detalle.cantidad"
                            class="form-input w-24"
                        />
                        <button type="button" @click="eliminarDetalle(index)" class="text-red-500 hover:underline">
                            Eliminar
                        </button>
                    </div>
                    <div v-if="form.errors.detalles" class="text-sm text-red-600 mt-1">
                        {{ form.errors.detalles }}
                    </div>
                </div>

                <button
                    type="button"
                    @click="agregarDetalle"
                    class="bg-gray-200 text-sm px-3 py-1 rounded hover:bg-gray-300"
                >
                    + Agregar detalle
                </button>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                        :disabled="form.processing"
                    >
                        Actualizar Nota
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
