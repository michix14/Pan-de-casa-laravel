<script setup>
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { toast } from 'vue3-toastify';

const props = defineProps({
    promocion: Object,
    productos: Array,
});

const form = useForm({
    nombre: props.promocion.nombre,
    fecha_inicio: props.promocion.fecha_inicio,
    fecha_fin: props.promocion.fecha_fin,
    productos: props.promocion.detalles.map(d => ({
        producto_id: d.producto_id,
        precio_promocional: d.precio_promocional,
    })),
});

const agregarProducto = () => {
    form.productos.push({ producto_id: '', precio_promocional: '' });
};

const eliminarProducto = (index) => {
    form.productos.splice(index, 1);
};

const submit = () => {
    router.put(route('promociones.update', props.promocion.id), form, {
        onSuccess: () => toast.success('Promoción actualizada correctamente'),
        onError: () => toast.error('Error al actualizar la promoción'),
    });
};

const productosSeleccionados = computed(() =>
    form.productos.map(d => d.producto_id).filter(Boolean)
);

const obtenerProducto = (id) => {
    return props.productos.find(p => p.id === id);
};
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow mt-10">
            <h1 class="text-2xl font-bold mb-4 text-gray-800">Editar Promoción</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Título</label>
                    <input v-model="form.nombre" type="text" class="w-full mt-1 border-gray-300 rounded shadow-sm" />
                    <span v-if="form.errors.nombre" class="text-red-600 text-sm">{{ form.errors.nombre }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                        <input v-model="form.fecha_inicio" type="date" class="w-full mt-1 border-gray-300 rounded" />
                        <span v-if="form.errors.fecha_inicio" class="text-red-600 text-sm">{{ form.errors.fecha_inicio }}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                        <input v-model="form.fecha_fin" type="date" class="w-full mt-1 border-gray-300 rounded" />
                        <span v-if="form.errors.fecha_fin" class="text-red-600 text-sm">{{ form.errors.fecha_fin }}</span>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold mb-2">Productos en Promoción</h2>
                    <div
                        v-for="(detalle, index) in form.productos"
                        :key="index"
                        class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-center mb-3"
                    >
                        <select v-model="detalle.producto_id" class="border-gray-300 rounded">
                            <option value="" disabled>Selecciona un producto</option>
                            <option
                                v-for="producto in props.productos"
                                :key="producto.id"
                                :value="producto.id"
                                :disabled="productosSeleccionados.includes(producto.id) && producto.id !== detalle.producto_id"
                            >
                                {{ producto.nombre }}
                            </option>
                        </select>

                        <span class="text-sm text-gray-600">
                            Precio actual:
                            <strong v-if="detalle.producto_id">
                                {{ obtenerProducto(detalle.producto_id)?.precio }} Bs
                            </strong>
                        </span>

                        <input
                            v-model="detalle.precio_promocional"
                            type="number"
                            min="0"
                            step="0.01"
                            class="border-gray-300 rounded"
                            placeholder="Precio promocional"
                        />

                        <button
                            type="button"
                            @click="eliminarProducto(index)"
                            class="text-red-600 hover:underline"
                        >
                            Eliminar
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="agregarProducto"
                        class="text-blue-600 hover:underline text-sm"
                    >
                        + Agregar otro producto
                    </button>

                    <span v-if="form.errors['productos']" class="text-red-600 text-sm block mt-2">
                        {{ form.errors['productos'] }}
                    </span>
                    <span v-if="form.errors['productos.0.producto_id']" class="text-red-600 text-sm block mt-1">
                        {{ form.errors['productos.0.producto_id'] }}
                    </span>
                    <span v-if="form.errors['productos.0.precio_promocional']" class="text-red-600 text-sm block mt-1">
                        {{ form.errors['productos.0.precio_promocional'] }}
                    </span>
                </div>

                <div>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 shadow"
                    >
                        Actualizar Promoción
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
