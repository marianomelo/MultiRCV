<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = useForm({
    name: '',
    rut: '',
    sii_password: '',
});

const validating = ref(false);
const validationStatus = ref(null); // null, 'success', 'error'
const validationMessage = ref('');

const validateCredentials = async () => {
    if (!form.rut || !form.sii_password) {
        validationMessage.value = 'Por favor ingresa el RUT y la contraseña antes de validar';
        validationStatus.value = 'error';
        return;
    }

    validating.value = true;
    validationStatus.value = null;
    validationMessage.value = '';

    try {
        const response = await axios.post('https://api-rcv.hostingsistemas.cl/api/validar-credenciales', {
            rut: form.rut,
            contrasena: form.sii_password,
        }, {
            headers: {
                'x-api-key': '123456789',
            },
        });

        if (response.data.success) {
            validationStatus.value = 'success';
            validationMessage.value = 'Credenciales válidas ✓';
        } else {
            validationStatus.value = 'error';
            validationMessage.value = response.data.message || 'Credenciales inválidas';
        }
    } catch (error) {
        validationStatus.value = 'error';
        if (error.response?.data?.message) {
            validationMessage.value = error.response.data.message;
        } else {
            validationMessage.value = 'Error al validar credenciales. Intenta nuevamente.';
        }
    } finally {
        validating.value = false;
    }
};

const submit = () => {
    if (validationStatus.value !== 'success') {
        alert('Por favor valida las credenciales antes de crear la empresa');
        return;
    }
    form.post(route('companies.store'));
};
</script>

<template>
    <Head title="Crear Empresa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Crear Empresa
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Nombre de la Empresa" />

                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="organization"
                                />

                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="rut" value="RUT" />

                                <TextInput
                                    id="rut"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.rut"
                                    required
                                    placeholder="12345678-9"
                                />

                                <InputError class="mt-2" :message="form.errors.rut" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="sii_password" value="Contraseña del SII" />

                                <TextInput
                                    id="sii_password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.sii_password"
                                    required
                                />

                                <InputError class="mt-2" :message="form.errors.sii_password" />
                            </div>

                            <!-- Validation Button -->
                            <div class="mt-6">
                                <button
                                    type="button"
                                    @click="validateCredentials"
                                    :disabled="validating"
                                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                >
                                    <svg v-if="validating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span v-if="validating">Validando...</span>
                                    <span v-else>Validar Credenciales</span>
                                </button>

                                <!-- Validation Message -->
                                <div v-if="validationMessage" class="mt-3">
                                    <div
                                        v-if="validationStatus === 'success'"
                                        class="rounded-md bg-green-50 p-3 border border-green-200"
                                    >
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-green-800">{{ validationMessage }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="validationStatus === 'error'"
                                        class="rounded-md bg-red-50 p-3 border border-red-200"
                                    >
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-red-800">{{ validationMessage }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-4">
                                <a
                                    :href="route('companies.index')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                                >
                                    Cancelar
                                </a>

                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Crear
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
