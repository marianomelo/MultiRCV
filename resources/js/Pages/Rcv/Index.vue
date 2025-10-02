<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    companies: Array,
    requests: Array
});

// Generate current year and month
const currentDate = new Date();
const currentYear = currentDate.getFullYear();
const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');

// Generate years (current year and 10 years back)
const years = Array.from({ length: 11 }, (_, i) => currentYear - i);

// Generate months
const months = [
    { value: '01', label: 'Enero' },
    { value: '02', label: 'Febrero' },
    { value: '03', label: 'Marzo' },
    { value: '04', label: 'Abril' },
    { value: '05', label: 'Mayo' },
    { value: '06', label: 'Junio' },
    { value: '07', label: 'Julio' },
    { value: '08', label: 'Agosto' },
    { value: '09', label: 'Septiembre' },
    { value: '10', label: 'Octubre' },
    { value: '11', label: 'Noviembre' },
    { value: '12', label: 'Diciembre' },
];

const selectedYear = ref(String(currentYear));
const selectedMonth = ref(currentMonth);

// Computed period in YYYY-MM format
const period = computed(() => `${selectedYear.value}-${selectedMonth.value}`);

const form = useForm({
    period: period.value,
    type: 'compra',
    company_ids: [],
});

const submit = () => {
    // Update form period with current selected values
    form.period = period.value;

    form.post(route('rcv.store'), {
        onSuccess: () => {
            form.company_ids = [];
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

// Auto-refresh every 3 seconds if there are processing requests
let pollingInterval = null;

const hasProcessingRequests = computed(() => {
    return props.requests.some(req => req.status === 'processing' || req.status === 'pending');
});

const startPolling = () => {
    if (pollingInterval) return;

    pollingInterval = setInterval(() => {
        if (hasProcessingRequests.value) {
            router.reload({ only: ['requests'], preserveScroll: true });
        } else {
            stopPolling();
        }
    }, 3000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

onMounted(() => {
    if (hasProcessingRequests.value) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});

const deleteRequest = (requestId) => {
    if (confirm('¿Estás seguro de que deseas eliminar esta solicitud? Esta acción no se puede deshacer.')) {
        router.delete(route('rcv.destroy', requestId), {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh will happen automatically
            }
        });
    }
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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel for="month" value="Mes" />
                                    <select
                                        id="month"
                                        v-model="selectedMonth"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="month in months" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.period" />
                                </div>

                                <div>
                                    <InputLabel for="year" value="Año" />
                                    <select
                                        id="year"
                                        v-model="selectedYear"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="year in years" :key="year" :value="String(year)">
                                            {{ year }}
                                        </option>
                                    </select>
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
                                    <tr v-for="request in requests" :key="request.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ request.period }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 capitalize">
                                            {{ request.type }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            <span v-if="request.status === 'processing' || request.status === 'pending'">
                                                {{ request.processed_companies || 0 }}/{{ request.total_companies || request.company_ids.length }}
                                            </span>
                                            <span v-else>
                                                {{ request.company_ids.length }} empresa(s)
                                            </span>
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
                                            <div class="flex items-center justify-end gap-3">
                                                <Link
                                                    :href="route('rcv.show', request.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Ver Detalles
                                                </Link>
                                                <button
                                                    @click="deleteRequest(request.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="requests.length === 0">
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
