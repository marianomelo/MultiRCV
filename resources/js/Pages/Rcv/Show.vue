<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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
    };
    return texts[status] || status;
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-CL').format(num || 0);
};
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
                                <div class="mb-3 text-sm text-gray-600">
                                    Total de registros: <span class="font-semibold">{{ result.data.total_registros }}</span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Nro</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipo Doc</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">RUT Proveedor</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Razón Social</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Folio</th>
                                                <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Fecha Docto</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto Neto</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto IVA</th>
                                                <th class="px-3 py-2 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Monto Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            <tr v-for="item in result.data.datos" :key="item.nro" class="hover:bg-gray-50">
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">{{ item.nro }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">{{ item.tipo_doc }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">{{ item.rut_proveedor }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900 max-w-xs truncate">{{ item.razon_social }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">{{ item.folio }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900">{{ item.fecha_docto }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900 text-right">{{ formatNumber(item.monto_neto) }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-900 text-right">{{ formatNumber(item.monto_iva_recuperable) }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm font-medium text-gray-900 text-right">{{ formatNumber(item.monto_total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
