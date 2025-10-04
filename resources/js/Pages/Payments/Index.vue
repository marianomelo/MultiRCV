<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    payments: Array,
});

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800',
        'error': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        'pending': 'Pendiente',
        'paid': 'Pagado',
        'rejected': 'Rechazado',
        'cancelled': 'Cancelado',
        'error': 'Error',
    };
    return texts[status] || status;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-CL', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
    }).format(price);
};
</script>

<template>
    <Head title="Transacciones" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Transacciones de Pagos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Usuario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Plan
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Monto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Facturación
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Flow Order
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="payment in payments" :key="payment.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                #{{ payment.id }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ payment.user?.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ payment.user?.email }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ payment.plan?.name }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ formatPrice(payment.amount) }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="getStatusBadgeClass(payment.status)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ getStatusText(payment.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                <div v-if="payment.billing_name">
                                                    <strong>{{ payment.billing_name }}</strong>
                                                </div>
                                                <div v-if="payment.billing_rut" class="text-gray-600">
                                                    RUT: {{ payment.billing_rut }}
                                                </div>
                                                <div v-if="payment.document_type" class="text-gray-600">
                                                    {{ payment.document_type === 'factura' ? 'Factura' : 'Boleta' }}
                                                    {{ payment.billing_type === 'empresa' ? '(Empresa)' : '(Persona Natural)' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(payment.created_at) }}
                                            </div>
                                            <div v-if="payment.paid_at" class="text-sm text-green-600">
                                                Pagado: {{ formatDate(payment.paid_at) }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-500">
                                                {{ payment.flow_order || '-' }}
                                            </div>
                                            <div v-if="payment.commerce_order" class="text-xs text-gray-400">
                                                {{ payment.commerce_order }}
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="payments.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay transacciones registradas
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
