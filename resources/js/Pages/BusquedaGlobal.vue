<template>
  <div>
    <h1 class="text-xl font-bold">Resultados para "{{ query }}"</h1>

    <div v-if="usuarios.length">
      <h2 class="mt-4 font-semibold text-blue-700">Usuarios</h2>
      <ul>
        <li v-for="user in usuarios" :key="user.id">
          {{ user.name }} - {{ user.email }}
        </li>
      </ul>
    </div>

    <div v-if="productos.length">
      <h2 class="mt-4 font-semibold text-green-700">Productos</h2>
      <ul>
        <li v-for="prod in productos" :key="prod.id">
          {{ prod.nombre }} - {{ prod.descripcion }}
        </li>
      </ul>
    </div>

    <div v-if="ventas.length">
      <h2 class="mt-4 font-semibold text-red-700">Ventas</h2>
      <ul>
        <li v-for="venta in ventas" :key="venta.id">
          {{ venta.tipo }} - {{ venta.estado }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps(['usuarios', 'productos', 'ventas', 'query']);
</script>
