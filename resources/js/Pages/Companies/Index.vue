<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    companies: Array,
    userPlan: Object,
    canAddMore: Boolean,
    remainingCompanies: Number,
});
</script>

<template>
    <Head title="Empresas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Empresas
                    </h2>
                    <p v-if="userPlan" class="text-sm text-gray-600 mt-1">
                        Plan: <span class="font-semibold">{{ userPlan.name }}</span>
                        - {{ companies.length }}/{{ userPlan.company_limit }} empresas
                        <span v-if="remainingCompanies > 0" class="text-green-600">
                            ({{ remainingCompanies }} disponibles)
                        </span>
                        <span v-else class="text-red-600">
                            (límite alcanzado)
                        </span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        v-if="!canAddMore"
                        href="/plans"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500"
                    >
                        Actualizar Plan
                    </Link>
                    <Link
                        :href="route('companies.create')"
                        :class="[
                            'rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm',
                            canAddMore ? 'bg-indigo-600 hover:bg-indigo-500' : 'bg-gray-400 cursor-not-allowed'
                        ]"
                        :disabled="!canAddMore"
                    >
                        Crear Empresa
                    </Link>
                </div>
            </div>
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
                                            Nombre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            RUT
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="company in companies" :key="company.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ company.name }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ company.rut }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link
                                                :href="route('companies.edit', company.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <Link
                                                :href="route('companies.destroy', company.id)"
                                                method="delete"
                                                as="button"
                                                class="text-red-600 hover:text-red-900"
                                                preserve-scroll
                                            >
                                                Eliminar
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="companies.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay empresas registradas
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
