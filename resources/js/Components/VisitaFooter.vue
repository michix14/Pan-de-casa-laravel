<script setup>

defineProps({
    visitas: {
        type: Number,
        default: 0
    }
});

</script>


<template>
    <footer class="flex justify-end m-2 p-3">
        <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
        <p class="text-sm text-gray-600">Cantidad de visitas: {{ visitas }}</p>
    </footer>
</template>
