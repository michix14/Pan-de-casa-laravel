<template>
  <div>
    <h1>Resultados para "{{ query }}"</h1>

    <div v-if="usuarios.length">
      <h2>Usuarios</h2>
      <ul>
        <li v-for="u in usuarios" :key="u.id">{{ u.name }} - {{ u.email }}</li>
      </ul>
    </div>

    <div v-if="productos.length">
      <h2>Productos</h2>
      <ul>
        <li v-for="p in productos" :key="p.id">{{ p.nombre }}</li>
      </ul>
    </div>

    <div v-if="ventas.length">
      <h2>Ventas</h2>
      <ul>
        <li v-for="v in ventas" :key="v.id">Código: {{ v.codigo }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
defineProps({
  query: String,
  usuarios: Array,
  productos: Array,
  ventas: Array
})
</script>
