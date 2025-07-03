<script setup>
import { reactive, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  productos: Array,
  usuarios: Array,
  visitas: Number
});

const form = useForm({
  usuario_id: '',
  tipo: '',
  estado: 'PENDIENTE',
  fecha_entrega: '',
  metodo_pago: '',
  detalles: []
});

const agregarProducto = () => {
  form.detalles.push({ producto_id: '', cantidad: 1 });
};

const eliminarProducto = (index) => {
  form.detalles.splice(index, 1);
};

const totalVenta = computed(() => {
  return form.detalles.reduce((total, p) => {
    const prod = props.productos.find(pr => pr.id == p.producto_id);
    return prod ? total + (prod.precio * p.cantidad) : total;
  }, 0);
});
</script>

<template>
  <AppLayout :visitas="visitas">
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Registrar Nueva Venta</h1>

      <form @submit.prevent="form.post(route('ventas.store'))" class="bg-white shadow-sm rounded-lg p-6 space-y-6 border">

        <!-- Usuario -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
          <select v-model="form.usuario_id" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione un usuario</option>
            <option v-for="user in usuarios" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
          <div v-if="form.errors.usuario_id" class="text-red-600 text-sm mt-1">{{ form.errors.usuario_id }}</div>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pedido</label>
          <select v-model="form.tipo" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200">
            <option value="">Seleccione</option>
            <option value="TIENDA">TIENDA</option>
            <option value="ENVIO">ENVIO</option>
            <option value="RECOJO">RECOJO</option>
          </select>
          <div v-if="form.errors.tipo" class="text-red-600 text-sm mt-1">{{ form.errors.tipo }}</div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
          <select v-model="form.estado" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="PENDIENTE">PENDIENTE</option>
            <option value="COMPLETADO">COMPLETADO</option>
            <option value="CANCELADO">CANCELADO</option>
          </select>
          <div v-if="form.errors.estado" class="text-red-600 text-sm mt-1">{{ form.errors.estado }}</div>
        </div>

        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Entrega</label>
          <input type="date" v-model="form.fecha_entrega" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-blue-200" />
          <div v-if="form.errors.fecha_entrega" class="text-red-600 text-sm mt-1">{{ form.errors.fecha_entrega }}</div>
        </div>

        <!-- Método de Pago -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pago</label>
          <select v-model="form.metodo_pago" class="w-full border-gray-300 rounded-lg shadow-sm">
            <option value="">Seleccione</option>
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA (Stripe)</option>
          </select>
          <div v-if="form.errors.metodo_pago" class="text-red-600 text-sm mt-1">{{ form.errors.metodo_pago }}</div>
        </div>

        <!-- Productos -->
        <div>
          <h2 class="text-lg font-semibold mb-2">Productos</h2>
          <div v-for="(p, index) in form.detalles" :key="index" class="grid grid-cols-12 gap-2 mb-3 items-center">
            <div class="col-span-7">
              <select v-model="p.producto_id" class="w-full border-gray-300 rounded-lg shadow-sm">
                <option value="">Seleccione un producto</option>
                <option v-for="producto in productos" :key="producto.id" :value="producto.id">
                  {{ producto.nombre }} - Bs {{ producto.precio }}
                </option>
              </select>
            </div>
            <div class="col-span-3">
              <input type="number" v-model="p.cantidad" min="1" class="w-full border-gray-300 rounded-lg shadow-sm" />
            </div>
            <div class="col-span-2 text-right">
              <button type="button" @click="eliminarProducto(index)" class="text-red-600 hover:text-red-800 text-sm font-medium">Eliminar</button>
            </div>
          </div>
          <button type="button" @click="agregarProducto" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm">
            + Agregar Producto
          </button>
          <div v-if="form.errors.detalles" class="text-red-600 text-sm mt-2">{{ form.errors.detalles }}</div>
        </div>

        <!-- Total -->
        <div class="text-lg font-semibold text-gray-800">
          Total: <span class="text-green-700">Bs {{ totalVenta.toFixed(2) }}</span>
        </div>

        <!-- Botones -->
        <div class="flex justify-start space-x-4">
          <button type="submit" class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm">
            Registrar Venta
          </button>
          <button type="button" @click="router.visit(route('ventas.index'))" class="inline-flex items-center px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
