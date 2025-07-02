<!-- resources/js/Pages/Pedidos/Form.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    pedido: Object,
    usuarios: Array,
    isEdit: Boolean
});

const form = useForm({
    usuario_id: props.pedido?.usuario_id ?? '',
    tipo: props.pedido?.tipo ?? '',
    estado: props.pedido?.estado ?? '',
    fecha_entrega: props.pedido?.fecha_entrega ?? '',
});

const submit = () => {
    const options = { preserveScroll: true };

    if (props.isEdit) {
        form.put(route('pedidos.update', props.pedido.id), options);
    } else {
        form.post(route('pedidos.store'), options);
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Usuario -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Usuario <span class="text-red-500">*</span>
            </label>
            <select v-model="form.usuario_id"
                    class="w-full border-gray-300 rounded-md shadow-sm"
                    :class="{ 'border-red-500': form.errors.usuario_id }">
                <option value="">Seleccione un usuario</option>
                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                    {{ usuario.name }}
                </option>
            </select>
            <p v-if="form.errors.usuario_id" class="text-sm text-red-600">{{ form.errors.usuario_id }}</p>
        </div>

        <!-- Tipo -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de pedido <span class="text-red-500">*</span>
            </label>
            <select v-model="form.tipo"
                    class="w-full border-gray-300 rounded-md shadow-sm"
                    :class="{ 'border-red-500': form.errors.tipo }">
                <option value="">Seleccione un tipo</option>
                <option value="TIENDA">TIENDA</option>
                <option value="ENVIO">ENVIO</option>
                <option value="RECOJO">RECOJO</option>
            </select>
            <p v-if="form.errors.tipo" class="text-sm text-red-600">{{ form.errors.tipo }}</p>
        </div>

        <!-- Estado -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Estado <span class="text-red-500">*</span>
            </label>
            <select v-model="form.estado"
                    class="w-full border-gray-300 rounded-md shadow-sm"
                    :class="{ 'border-red-500': form.errors.estado }">
                <option value="">Seleccione un estado</option>
                <option value="PENDIENTE">PENDIENTE</option>
                <option value="COMPLETADO">COMPLETADO</option>
                <option value="CANCELADO">CANCELADO</option>
            </select>
            <p v-if="form.errors.estado" class="text-sm text-red-600">{{ form.errors.estado }}</p>
        </div>

        <!-- Fecha -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Fecha de entrega <span class="text-red-500">*</span>
            </label>
            <input type="datetime-local" v-model="form.fecha_entrega"
                class="w-full border-gray-300 rounded-md shadow-sm"
                :class="{ 'border-red-500': form.errors.fecha_entrega }" />
            <p v-if="form.errors.fecha_entrega" class="text-sm text-red-600">{{ form.errors.fecha_entrega }}</p>
        </div>

        <!-- Botones -->
        <div class="flex space-x-2 pt-4">
            <button type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                {{ form.processing ? 'Guardando...' : isEdit ? 'Actualizar Pedido' : 'Crear Pedido' }}
            </button>
            <button type="button" @click="$inertia.visit(route('pedidos.index'))"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancelar
            </button>
        </div>
    </form>
</template>
