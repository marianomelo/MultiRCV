<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bienvenido - MultiRCV" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4">
        <div class="max-w-4xl w-full">
            <!-- Logo and Title -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold text-gray-900 mb-4">
                    MultiRCV
                </h1>
                <p class="text-xl text-gray-600">
                    Sistema de gestión de Registros de Compra y Venta
                </p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="grid md:grid-cols-2">
                    <!-- Left Side - Info -->
                    <div class="bg-indigo-600 p-12 flex flex-col justify-center">
                        <div class="text-white">
                            <h2 class="text-3xl font-bold mb-6">
                                Bienvenido a MultiRCV
                            </h2>
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-lg">Gestiona múltiples empresas desde un solo lugar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-lg">Consulta registros de compra y venta del SII</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-lg">Exporta datos a Excel con un solo clic</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-lg">Interfaz simple y fácil de usar</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right Side - Actions -->
                    <div class="p-12 flex flex-col justify-center">
                        <div v-if="$page.props.auth.user" class="space-y-6">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-6">
                                Ya has iniciado sesión
                            </h3>
                            <Link
                                :href="route('dashboard')"
                                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl"
                            >
                                Ir al Dashboard
                            </Link>
                        </div>

                        <div v-else class="space-y-6">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-6">
                                Comienza ahora
                            </h3>

                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 shadow-lg hover:shadow-xl"
                            >
                                Iniciar Sesión
                            </Link>

                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-2 bg-white text-gray-500">o</span>
                                </div>
                            </div>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="block w-full text-center bg-white hover:bg-gray-50 text-indigo-600 font-semibold py-4 px-6 rounded-lg border-2 border-indigo-600 transition duration-200"
                            >
                                Crear Cuenta Nueva
                            </Link>

                            <p class="text-sm text-gray-500 text-center mt-4">
                                ¿Eres contador? Regístrate para gestionar múltiples empresas
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-gray-600">
                <p class="text-sm">
                    © {{ new Date().getFullYear() }} MultiRCV - Sistema de Gestión de RCV
                </p>
            </div>
        </div>
    </div>
</template>
