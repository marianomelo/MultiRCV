<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const isOpen = ref(false);

const form = useForm({
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('ticket.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            isOpen.value = false;
        },
    });
};
</script>

<template>
    <!-- Floating Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <!-- Widget Modal -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="isOpen"
                class="mb-4 bg-white rounded-lg shadow-2xl w-96 border border-gray-200"
            >
                <div class="bg-red-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold text-lg">Soporte Técnico</h3>
                        <p class="text-xs text-red-100">Envíanos tu consulta</p>
                    </div>
                    <button
                        @click="isOpen = false"
                        class="text-white hover:text-red-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-4 space-y-4">
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                            Asunto
                        </label>
                        <input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Describe brevemente tu problema"
                        />
                        <div v-if="form.errors.subject" class="text-sm text-red-600 mt-1">
                            {{ form.errors.subject }}
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                            Mensaje
                        </label>
                        <textarea
                            id="message"
                            v-model="form.message"
                            rows="5"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Explica tu consulta o problema en detalle..."
                        ></textarea>
                        <div v-if="form.errors.message" class="text-sm text-red-600 mt-1">
                            {{ form.errors.message }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 disabled:opacity-50"
                    >
                        <span v-if="!form.processing">Enviar Ticket</span>
                        <span v-else>Enviando...</span>
                    </button>

                    <p class="text-xs text-gray-500 text-center">
                        Te responderemos a tu correo electrónico registrado
                    </p>
                </form>
            </div>
        </transition>

        <!-- Toggle Button -->
        <button
            @click="isOpen = !isOpen"
            class="bg-red-600 hover:bg-red-700 text-white rounded-full p-4 shadow-lg transition duration-200 hover:scale-110"
            :class="{ 'rotate-45': isOpen }"
        >
            <svg
                v-if="!isOpen"
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <svg
                v-else
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</template>
