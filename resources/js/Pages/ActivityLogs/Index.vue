<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

defineProps({
    activities: Object,
    filters: Object
});

const formatDate = (date) => {
    return format(new Date(date), "dd 'de' MMMM 'de' yyyy, HH:mm:ss", { locale: es });
};

const getActionBadgeClass = (action) => {
    const classes = {
        'login': 'bg-green-100 text-green-800',
        'logout': 'bg-gray-100 text-gray-800',
        'create': 'bg-blue-100 text-blue-800',
        'update': 'bg-yellow-100 text-yellow-800',
        'delete': 'bg-red-100 text-red-800',
        'export': 'bg-purple-100 text-purple-800',
    };
    return classes[action] || 'bg-gray-100 text-gray-800';
};

const getActionText = (action) => {
    const texts = {
        'login': 'Inicio de Sesión',
        'logout': 'Cierre de Sesión',
        'create': 'Crear',
        'update': 'Actualizar',
        'delete': 'Eliminar',
        'export': 'Exportar',
    };
    return texts[action] || action;
};
</script>

<template>
    <Head title="Registro de Actividad" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Registro de Actividad de Usuarios
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
                                            Usuario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Acción
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Descripción
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            IP
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Fecha
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="activity in activities.data" :key="activity.id">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ activity.user?.name || 'Usuario eliminado' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            <span :class="getActionBadgeClass(activity.action)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                                {{ getActionText(activity.action) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ activity.description || '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ activity.ip_address || '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                            {{ formatDate(activity.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="activities.links.length > 3" class="mt-6 flex items-center justify-between">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <a
                                    v-if="activities.prev_page_url"
                                    :href="activities.prev_page_url"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Anterior
                                </a>
                                <a
                                    v-if="activities.next_page_url"
                                    :href="activities.next_page_url"
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Siguiente
                                </a>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{ activities.from }}</span>
                                        a
                                        <span class="font-medium">{{ activities.to }}</span>
                                        de
                                        <span class="font-medium">{{ activities.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <template v-for="(link, index) in activities.links" :key="index">
                                            <a
                                                v-if="link.url"
                                                :href="link.url"
                                                v-html="link.label"
                                                :class="[
                                                    link.active
                                                        ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                                                        : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                                                ]"
                                            />
                                            <span
                                                v-else
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300"
                                            />
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
