<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    request: Object
});

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800',
        'success': 'bg-green-100 text-green-800',
        'error': 'bg-red-100 text-red-800',
        'warning': 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'Pendiente',
        'processing': 'Procesando',
        'completed': 'Completado',
        'failed': 'Fallido',
        'success': 'Éxito',
        'error': 'Error',
        'warning': 'Sin Datos',
    };
    return texts[status] || status;
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-CL').format(num || 0);
};

const formatFieldName = (fieldName) => {
    return fieldName
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Get all field keys from the first record of each result
const getTableHeaders = (result) => {
    if (!result.data || !result.data.datos || result.data.datos.length === 0) {
        return [];
    }

    const firstRecord = result.data.datos[0];
    return Object.keys(firstRecord).filter(key => key !== '__parsed_extra');
};

// Auto-refresh if processing
let pollingInterval = null;

const isProcessing = computed(() => {
    return props.request.status === 'processing' || props.request.status === 'pending';
});

const startPolling = () => {
    if (pollingInterval) return;

    pollingInterval = setInterval(() => {
        if (isProcessing.value) {
            router.reload({ preserveScroll: true });
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
    if (isProcessing.value) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head title="Detalle de Solicitud RCV" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detalle de Solicitud RCV
                </h2>
                <Link
                    :href="route('rcv.index')"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    ← Volver al listado
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Request Info -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Información General</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Período</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ request.period }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                                <dd class="mt-1 text-sm text-gray-900 capitalize">{{ request.type }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1">
                                    <span
                                        :class="getStatusBadgeClass(request.status)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
                                        {{ getStatusText(request.status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Fecha de Solicitud</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ new Date(request.requested_at).toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="request.completed_at">
                                <dt class="text-sm font-medium text-gray-500">Fecha de Finalización</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ new Date(request.completed_at).toLocaleString() }}
                                </dd>
                            </div>
                            <div v-if="request.error_message">
                                <dt class="text-sm font-medium text-gray-500">Error</dt>
                                <dd class="mt-1 text-sm text-red-600">{{ request.error_message }}</dd>
                            </div>
                            <div v-if="request.status === 'processing' || request.status === 'pending'">
                                <dt class="text-sm font-medium text-gray-500">Progreso</dt>
                                <dd class="mt-1">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="{ width: ((request.processed_companies || 0) / (request.total_companies || 1) * 100) + '%' }"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ request.processed_companies || 0 }}/{{ request.total_companies || 0 }}
                                        </span>
                                    </div>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Results per Company -->
                <div v-if="request.response_data && request.response_data.length > 0" class="space-y-6">
                    <div
                        v-for="(result, index) in request.response_data"
                        :key="index"
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ result.company_name }}</h3>
                                    <p class="text-sm text-gray-500">RUT: {{ result.rut }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span
                                        :class="getStatusBadgeClass(result.status)"
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    >
                                        {{ getStatusText(result.status) }}
                                    </span>
                                    <a
                                        v-if="result.status === 'success'"
                                        :href="route('rcv.export', [request.id, result.company_id])"
                                        class="inline-flex items-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Descargar Excel
                                    </a>
                                </div>
                            </div>

                            <!-- Success Data Table -->
                            <div v-if="result.status === 'success' && result.data && result.data.datos" class="mt-4">
                                <div class="mb-3 flex items-center gap-3">
                                    <span class="text-sm text-gray-600">
                                        Total de registros: <span class="font-semibold">{{ result.data.total_registros }}</span>
                                    </span>
                                    <span
                                        v-if="result.data.datos.length > 0 && result.data.datos[0].tipo_documento"
                                        class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800"
                                    >
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                        </svg>
                                        Total Agrupado
                                    </span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    v-for="header in getTableHeaders(result)"
                                                    :key="header"
                                                    class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500 whitespace-nowrap"
                                                >
                                                    {{ formatFieldName(header) }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr v-for="item in result.data.datos" :key="item.nro" class="hover:bg-gray-50">
                                                <td
                                                    v-for="header in getTableHeaders(result)"
                                                    :key="`${item.nro}-${header}`"
                                                    class="whitespace-nowrap px-3 py-2 text-sm text-gray-900"
                                                    :class="{ 'text-right': header.includes('monto') || header.includes('iva') || header.includes('valor') || header.includes('tasa') }"
                                                >
                                                    <template v-if="header.includes('monto') || header.includes('iva') || header.includes('valor') || header.includes('neto') || header.includes('exento') || header.includes('total') || header.includes('credito')">
                                                        {{ formatNumber(item[header]) }}
                                                    </template>
                                                    <template v-else>
                                                        {{ item[header] || '-' }}
                                                    </template>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Warning Data (No Data Available) -->
                            <div v-if="result.status === 'warning' && result.warning" class="mt-4">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">{{ result.warning }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error Data -->
                            <div v-if="result.status === 'error' && result.error" class="mt-4">
                                <h5 class="text-sm font-medium text-red-700 mb-2">Error:</h5>
                                <div class="bg-red-50 p-4 rounded text-sm text-red-800">
                                    {{ result.error }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
