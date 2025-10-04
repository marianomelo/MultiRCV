<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    plans: Array,
    currentPlan: Object,
});

const changePlan = (planId) => {
    const plan = props.plans.find(p => p.id === planId);

    if (!confirm('¿Estás seguro de que deseas cambiar a este plan?')) {
        return;
    }

    // If plan is free, use POST to change directly
    if (plan.price === 0) {
        router.post(route('plans.change', planId));
    } else {
        // If plan requires payment, redirect to payment form (GET)
        router.get(route('payment.create', planId));
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
    }).format(price);
};
</script>

<template>
    <Head title="Planes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Planes y Precios
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h3 class="text-3xl font-bold text-gray-900">Elige el plan perfecto para tu negocio</h3>
                    <p class="mt-2 text-gray-600">Administra tus empresas de forma eficiente</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        :class="[
                            'relative rounded-lg border-2 p-6 shadow-lg transition-all',
                            currentPlan?.id === plan.id
                                ? 'border-indigo-600 bg-indigo-50'
                                : 'border-gray-200 bg-white hover:border-indigo-300'
                        ]"
                    >
                        <div
                            v-if="currentPlan?.id === plan.id"
                            class="absolute right-4 top-4 rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white"
                        >
                            Plan Actual
                        </div>

                        <div class="mb-4">
                            <h4 class="text-2xl font-bold text-gray-900">{{ plan.name }}</h4>
                            <p class="mt-1 text-sm text-gray-600">{{ plan.description }}</p>
                        </div>

                        <div class="mb-6">
                            <span class="text-4xl font-extrabold text-gray-900">
                                {{ formatPrice(plan.price) }}
                            </span>
                            <span class="text-gray-600">/mes</span>
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Hasta <strong>{{ plan.company_limit }}</strong> empresa{{ plan.company_limit > 1 ? 's' : '' }}</span>
                            </div>
                            <div class="mt-2 flex items-center text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Consultas ilimitadas</span>
                            </div>
                            <div class="mt-2 flex items-center text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Exportación a Excel</span>
                            </div>
                            <div class="mt-2 flex items-center text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Soporte técnico</span>
                            </div>
                        </div>

                        <button
                            v-if="currentPlan?.id !== plan.id"
                            @click="changePlan(plan.id)"
                            :class="[
                                'w-full rounded-md px-4 py-2 text-sm font-semibold shadow-sm transition-colors',
                                plan.price > (currentPlan?.price || 0)
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-500'
                                    : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                            ]"
                        >
                            {{ plan.price > (currentPlan?.price || 0) ? 'Actualizar' : 'Cambiar' }} a {{ plan.name }}
                        </button>
                        <div
                            v-else
                            class="w-full rounded-md bg-gray-300 px-4 py-2 text-center text-sm font-semibold text-gray-600"
                        >
                            Plan Actual
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
