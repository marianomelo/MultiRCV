<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Bienvenido a MultiRCV</h3>
                    <p class="text-gray-600">Sistema de gestión y consulta de RCV del SII</p>
                </div>

                <!-- Instrucciones de uso -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                        <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ¿Cómo funciona el sistema?
                    </h4>
                    <div class="space-y-4 text-sm text-gray-700">
                        <div class="flex">
                            <div class="flex-shrink-0 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-semibold mr-3">1</div>
                            <div>
                                <strong class="text-gray-900">Registra tus empresas:</strong>
                                <p class="mt-1">Ve a la sección "Empresas" y añade las empresas que deseas consultar. Necesitarás el RUT de la empresa y las credenciales del SII (usuario y clave).</p>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-semibold mr-3">2</div>
                            <div>
                                <strong class="text-gray-900">Realiza solicitudes RCV:</strong>
                                <p class="mt-1">En "Solicitudes RCV", selecciona las empresas que deseas consultar y el período (mes y año). El sistema procesará las solicitudes en paralelo.</p>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-semibold mr-3">3</div>
                            <div>
                                <strong class="text-gray-900">Descarga los resultados:</strong>
                                <p class="mt-1">Una vez procesada la solicitud, podrás visualizar los datos en pantalla y exportarlos a Excel. Los resultados incluyen todos los campos disponibles de la API del SII.</p>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="flex-shrink-0 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-semibold mr-3">4</div>
                            <div>
                                <strong class="text-gray-900">Gestiona tu plan:</strong>
                                <p class="mt-1">Según tu plan, podrás gestionar un número determinado de empresas. Actualiza tu plan en cualquier momento desde la sección "Planes".</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200">
                        <p class="text-xs text-gray-600">
                            <strong>Nota:</strong> Las credenciales del SII se almacenan encriptadas y solo se utilizan para realizar las consultas autorizadas.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Empresas Card -->
                    <Link
                        :href="route('companies.index')"
                        class="block overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Empresas</h3>
                                    <p class="text-sm text-gray-600">Gestiona las empresas registradas</p>
                                </div>
                            </div>
                        </div>
                    </Link>

                    <!-- RCV Card -->
                    <Link
                        :href="route('rcv.index')"
                        class="block overflow-hidden bg-white shadow-sm sm:rounded-lg hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">Solicitudes RCV</h3>
                                    <p class="text-sm text-gray-600">Consulta y descarga datos del SII</p>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
