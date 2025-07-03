<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form
    .transform(data => ({
      ...data,
      remember: form.remember ? 'on' : '',
    }))
    .post(route('login'), {
      onFinish: () => form.reset('password'),
    });
};
</script>

<template>
  <Head title="Iniciar sesión" />

  <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-yellow-100 via-orange-50 to-white dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-6">
    <!-- Logo -->
    <div class="mb-6">
      <AuthenticationCardLogo />
    </div>

    <!-- Card -->
    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8">
      <h2 class="text-2xl font-bold text-center text-orange-800 dark:text-yellow-400 mb-6">Bienvenido a Pan de Casa</h2>

      <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
      </div>

      <form @submit.prevent="submit">
        <!-- Email -->
        <div class="mb-4">
          <InputLabel for="email" value="Correo electrónico" />
          <TextInput
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 block w-full"
            required
            autofocus
            autocomplete="username"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Password -->
        <div class="mb-4">
          <InputLabel for="password" value="Contraseña" />
          <TextInput
            id="password"
            v-model="form.password"
            type="password"
            class="mt-1 block w-full"
            required
            autocomplete="current-password"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Remember -->
        <div class="flex items-center justify-between mb-4">
          <label class="flex items-center">
            <Checkbox v-model:checked="form.remember" name="remember" />
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Recuérdame</span>
          </label>

          <Link
            v-if="canResetPassword"
            :href="route('password.request')"
            class="text-sm text-orange-600 hover:underline"
          >
            ¿Olvidaste tu contraseña?
          </Link>
        </div>

        <!-- Submit -->
        <div class="flex justify-center">
          <PrimaryButton
            class="w-full justify-center bg-orange-600 hover:bg-orange-700 focus:ring-orange-500"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Iniciar sesión
          </PrimaryButton>
        </div>
      </form>
    </div>
  </div>
</template>
