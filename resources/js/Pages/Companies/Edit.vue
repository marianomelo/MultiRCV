<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    company: Object
});

const form = useForm({
    name: props.company.name,
    rut: props.company.rut,
    sii_password: '',
});

const submit = () => {
    form.put(route('companies.update', props.company.id));
};
</script>

<template>
    <Head title="Editar Empresa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Empresa
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

                            <div class="mt-6 flex items-center justify-end gap-4">
                                <a
                                    :href="route('companies.index')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                                >
                                    Cancelar
                                </a>

                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Actualizar
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
