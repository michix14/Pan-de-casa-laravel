<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Registrarse" />

  <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-yellow-50 via-orange-100 to-white dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-12 px-6">
    <!-- Logo -->
    <div class="mb-6">
      <AuthenticationCardLogo />
    </div>

    <!-- Card -->
    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8">
      <h2 class="text-2xl font-bold text-center text-orange-800 dark:text-yellow-400 mb-6">
        Crea tu cuenta en Pan de Casa
      </h2>

      <form @submit.prevent="submit">
        <!-- Nombre -->
        <div class="mb-4">
          <InputLabel for="name" value="Nombre completo" />
          <TextInput
            id="name"
            v-model="form.name"
            type="text"
            class="mt-1 block w-full"
            required
            autofocus
            autocomplete="name"
          />
          <InputError class="mt-2" :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="mb-4">
          <InputLabel for="email" value="Correo electrónico" />
          <TextInput
            id="email"
            v-model="form.email"
            type="email"
            class="mt-1 block w-full"
            required
            autocomplete="username"
          />
          <InputError class="mt-2" :message="form.errors.email" />
        </div>

        <!-- Contraseña -->
        <div class="mb-4">
          <InputLabel for="password" value="Contraseña" />
          <TextInput
            id="password"
            v-model="form.password"
            type="password"
            class="mt-1 block w-full"
            required
            autocomplete="new-password"
          />
          <InputError class="mt-2" :message="form.errors.password" />
        </div>

        <!-- Confirmar contraseña -->
        <div class="mb-4">
          <InputLabel for="password_confirmation" value="Confirmar contraseña" />
          <TextInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="mt-1 block w-full"
            required
            autocomplete="new-password"
          />
          <InputError class="mt-2" :message="form.errors.password_confirmation" />
        </div>

        <!-- Términos y condiciones -->
        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mb-4">
          <label class="flex items-start text-sm text-gray-600 dark:text-gray-300">
            <Checkbox id="terms" v-model:checked="form.terms" class="mt-1" required />
            <span class="ml-2">
              Acepto los
              <Link :href="route('terms.show')" target="_blank" class="underline text-orange-700 dark:text-yellow-300 hover:text-orange-900">Términos</Link>
              y la
              <Link :href="route('policy.show')" target="_blank" class="underline text-orange-700 dark:text-yellow-300 hover:text-orange-900">Política de Privacidad</Link>
            </span>
          </label>
          <InputError class="mt-2" :message="form.errors.terms" />
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-between mt-6">
          <Link :href="route('login')" class="text-sm text-gray-600 dark:text-gray-300 hover:underline">
            ¿Ya tienes una cuenta?
          </Link>

          <PrimaryButton
            class="bg-orange-600 hover:bg-orange-700 focus:ring-orange-500"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Registrarse
          </PrimaryButton>
        </div>
      </form>
    </div>
  </div>
</template>
