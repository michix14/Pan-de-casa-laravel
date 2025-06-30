<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    producto: Object,
    isEdit: Boolean
});

const imagePreview = ref(null);
const fileInputRef = ref(null);

// Configuramos useForm con transform para construir manualmente FormData y enviar todos los campos
const form = useForm({
    nombre: props.producto?.nombre ?? '',
    descripcion: props.producto?.descripcion ?? '',
    precio: props.producto?.precio ?? '',
    stock: props.producto?.stock ?? '',
    imagen_url: null, // archivo de imagen o null
}, {
    transform(data) {
        const formData = new FormData();

        for (const key in data) {
            if (data[key] !== null && data[key] !== undefined) {
                if (key === 'imagen_url' && data[key] instanceof File) {
                    formData.append(key, data[key]);
                } else if (key !== 'imagen_url') {
                    formData.append(key, data[key]);
                }
            }
        }

        return formData;
    }
});

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.imagen_url = file;
        
        // Crear preview de la imagen
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.imagen_url = null;
    imagePreview.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const submit = () => {
    const options = {
        preserveScroll: true,
    };

    if (props.isEdit) {
        form.put(route('productos.update', props.producto.id), options);
    } else {
        form.post(route('productos.store'), options);
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ isEdit ? 'Editar Producto' : 'Nuevo Producto' }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ isEdit ? 'Modifica la información del producto' : 'Completa los datos para crear un nuevo producto' }}
                </p>
            </div>

            <!-- Form Card -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <form @submit.prevent="submit" class="space-y-6 p-6" enctype="multipart/form-data">
                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre del producto
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                id="nombre"
                                v-model="form.nombre" 
                                type="text"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.nombre }"
                                placeholder="Ingresa el nombre del producto"
                            />
                            <div v-if="form.errors.nombre" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <p v-if="form.errors.nombre" class="mt-2 text-sm text-red-600">{{ form.errors.nombre }}</p>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                            Descripción
                        </label>
                        <textarea 
                            id="descripcion"
                            v-model="form.descripcion" 
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.descripcion }"
                            placeholder="Describe las características del producto"
                        ></textarea>
                        <p v-if="form.errors.descripcion" class="mt-2 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                    </div>

                    <!-- Precio y Stock - Grid en desktop -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Precio -->
                        <div>
                            <label for="precio" class="block text-sm font-medium text-gray-700 mb-2">
                                Precio
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Bs.</span>
                                </div>
                                <input 
                                    id="precio"
                                    v-model="form.precio" 
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.precio }"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="form.errors.precio" class="mt-2 text-sm text-red-600">{{ form.errors.precio }}</p>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 mb-2">
                                Stock disponible
                                <span class="text-red-500">*</span>
                            </label>
                            <input 
                                id="stock"
                                v-model="form.stock" 
                                type="number"
                                min="0"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.stock }"
                                placeholder="Cantidad disponible"
                            />
                            <p v-if="form.errors.stock" class="mt-2 text-sm text-red-600">{{ form.errors.stock }}</p>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Imagen del producto
                        </label>
                        
                        <!-- Upload Area -->
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="imagen_url" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Subir archivo</span>
                                        <input 
                                            id="imagen_url" 
                                            ref="fileInputRef"
                                            type="file" 
                                            accept="image/*"
                                            class="sr-only" 
                                            @change="handleImageChange"
                                        />
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 2MB</p>
                            </div>
                        </div>
                        
                        <p v-if="form.errors.imagen_url" class="mt-2 text-sm text-red-600">{{ form.errors.imagen_url }}</p>

                        <!-- Image Previews -->
                        <div class="mt-4 space-y-4">
                            <!-- Nueva imagen seleccionada -->
                            <div v-if="imagePreview" class="relative inline-block">
                                <img :src="imagePreview" alt="Vista previa" class="h-32 w-32 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                <button 
                                    type="button"
                                    @click="removeImage"
                                    class="absolute -top-2 -right-2 h-6 w-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <p class="text-xs text-gray-500 mt-1 text-center">Nueva imagen</p>
                            </div>

                            <!-- Imagen actual (solo en edición) -->
                            <div v-if="isEdit && producto?.imagen_url && !imagePreview" class="relative inline-block">
                                <img :src="producto.imagen_url" alt="Imagen actual" class="h-32 w-32 object-cover rounded-lg border border-gray-200 shadow-sm" />
                                <div class="absolute top-1 right-1 bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">
                                    Actual
                                </div>
                                <p class="text-xs text-gray-500 mt-1 text-center">Imagen actual</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Guardando...' : (isEdit ? 'Actualizar Producto' : 'Crear Producto') }}
                        </button>
                        
                        <button 
                            type="button"
                            @click="$inertia.visit(route('productos.index'))"
                            class="flex-1 sm:flex-none inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>