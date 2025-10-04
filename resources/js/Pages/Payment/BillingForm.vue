<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    plan: Object,
});

const form = useForm({
    billing_rut: '',
    billing_name: '',
    billing_type: 'empresa',
    document_type: 'factura',
    billing_address: '',
});

const submit = () => {
    form.post(route('payment.process', props.plan.id));
};

const formatRut = () => {
    let value = form.billing_rut.replace(/[^0-9kK]/g, '');
    if (value.length > 1) {
        const body = value.slice(0, -1);
        const dv = value.slice(-1).toUpperCase();
        value = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + dv;
    }
    form.billing_rut = value;
};
</script>

<template>
    <Head title="Información de Facturación" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Información de Facturación
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Plan Info -->
                        <div class="mb-6 rounded-lg bg-blue-50 p-4">
                            <h3 class="text-lg font-semibold text-blue-900">Plan {{ plan.name }}</h3>
                            <p class="text-2xl font-bold text-blue-600">${{ plan.price.toLocaleString('es-CL') }}</p>
                            <p class="text-sm text-blue-700">{{ plan.description }}</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- RUT -->
                            <div>
                                <InputLabel for="billing_rut" value="RUT *" />
                                <TextInput
                                    id="billing_rut"
                                    v-model="form.billing_rut"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    @input="formatRut"
                                    placeholder="12.345.678-9"
                                />
                                <p class="mt-1 text-sm text-gray-500">Formato: 12.345.678-9</p>
                            </div>

                            <!-- Nombre / Razón Social -->
                            <div>
                                <InputLabel for="billing_name" value="Nombre / Razón Social *" />
                                <TextInput
                                    id="billing_name"
                                    v-model="form.billing_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                            </div>

                            <!-- Tipo -->
                            <div>
                                <InputLabel for="billing_type" value="Tipo *" />
                                <select
                                    id="billing_type"
                                    v-model="form.billing_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="empresa">Empresa</option>
                                    <option value="persona_natural">Persona Natural</option>
                                </select>
                            </div>

                            <!-- Tipo de Documento -->
                            <div>
                                <InputLabel for="document_type" value="Tipo de Documento *" />
                                <select
                                    id="document_type"
                                    v-model="form.document_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="factura">Factura</option>
                                    <option value="boleta">Boleta</option>
                                </select>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <InputLabel for="billing_address" value="Dirección *" />
                                <textarea
                                    id="billing_address"
                                    v-model="form.billing_address"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    rows="3"
                                    required
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-between">
                                <a
                                    :href="route('plans.index')"
                                    class="text-sm text-gray-600 hover:text-gray-900"
                                >
                                    ← Volver a Planes
                                </a>
                                <PrimaryButton :disabled="form.processing">
                                    Continuar al Pago
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
