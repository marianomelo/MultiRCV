<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    companies: Array,
    requests: Object
});

const form = useForm({
    period: '',
    type: 'compra',
    company_ids: [],
});

const submit = () => {
    form.post(route('rcv.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'Pendiente',
        'processing': 'Procesando',
        'completed': 'Completado',
        'failed': 'Fallido',
    };
    return texts[status] || status;
};
</script>

<template>
    <Head title="Solicitudes RCV" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Solicitudes de RCV
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Form to create new request -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Nueva Solicitud</h3>

                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="period" value="Período (YYYY-MM)" />
                                    <TextInput
                                        id="period"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.period"
                                        required
                                        placeholder="2021-01"
                                    />
                                    <InputError class="mt-2" :message="form.errors.period" />
                                </div>

                                <div>
                                    <InputLabel for="type" value="Tipo de Consulta" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="compra">Compra</option>
                                        <option value="venta">Venta</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <InputLabel value="Seleccionar Empresas" />
                                <div class="mt-2 space-y-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3">
                                    <label
                                        v-for="company in companies"
                                        :key="company.id"
                                        class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="company.id"
                                            v-model="form.company_ids"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <span class="text-sm">{{ company.name }} ({{ company.rut }})</span>
                                    </label>
                                    <div v-if="companies.length === 0" class="text-sm text-gray-500 text-center py-4">
                                        No hay empresas registradas.
                                        <Link :href="route('companies.create')" class="text-indigo-600 hover:text-indigo-800">
                                            Crear empresa
                                        </Link>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.company_ids" />
                            </div>

                            <div class="mt-6 flex justify-end">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Solicitar RCV
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List of requests -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Historial de Solicitudes</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Período
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tipo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Empresas
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="request in requests.data" :key="request.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ request.period }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 capitalize">
                                            {{ request.type }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ request.company_ids.length }} empresa(s)
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="getStatusBadgeClass(request.status)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ getStatusText(request.status) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ new Date(request.created_at).toLocaleString() }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('rcv.show', request.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver Detalles
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="requests.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay solicitudes registradas
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
