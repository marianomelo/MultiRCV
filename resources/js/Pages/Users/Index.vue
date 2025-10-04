<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    users: Array,
    plans: Array
});

const getRoleBadgeClass = (role) => {
    return role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
};

const getRoleText = (role) => {
    return role === 'super_admin' ? 'Super Admin' : 'Usuario';
};

const getApprovalBadgeClass = (isApproved) => {
    return isApproved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
};

const getApprovalText = (isApproved) => {
    return isApproved ? 'Aprobado' : 'Pendiente';
};

const approveUser = (userId) => {
    const form = useForm({});
    form.post(route('users.approve', userId), {
        preserveScroll: true,
    });
};

const changePlan = (userId, planId) => {
    const form = useForm({
        plan_id: planId
    });
    form.post(route('users.change-plan', userId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Usuarios
                </h2>
                <Link
                    :href="route('users.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    Crear Usuario
                </Link>
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
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Teléfono
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Rol
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Contador
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Plan
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ user.name }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ user.email }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm text-gray-900">
                                                {{ user.phone || '-' }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="getRoleBadgeClass(user.role)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ getRoleText(user.role) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span
                                                :class="getApprovalBadgeClass(user.is_approved)"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ getApprovalText(user.is_approved) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="text-sm text-gray-900">
                                                {{ user.is_accountant ? 'Sí' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <select
                                                :value="user.plan_id"
                                                @change="changePlan(user.id, $event.target.value)"
                                                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option :value="null">Sin plan</option>
                                                <option
                                                    v-for="plan in plans"
                                                    :key="plan.id"
                                                    :value="plan.id"
                                                >
                                                    {{ plan.name }} (${{ plan.price.toLocaleString('es-CL') }})
                                                </option>
                                            </select>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button
                                                v-if="!user.is_approved"
                                                @click="approveUser(user.id)"
                                                class="text-green-600 hover:text-green-900 mr-4"
                                            >
                                                Aprobar
                                            </button>
                                            <Link
                                                :href="route('users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-4"
                                            >
                                                Editar
                                            </Link>
                                            <Link
                                                :href="route('users.destroy', user.id)"
                                                method="delete"
                                                as="button"
                                                class="text-red-600 hover:text-red-900"
                                                preserve-scroll
                                            >
                                                Eliminar
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="users.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay usuarios registrados
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
