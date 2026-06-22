<template>
    <div class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">

            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center
                    justify-center mx-auto mb-4 shadow-lg shadow-blue-600/30">
                    <TruckIcon class="w-7 h-7 text-white" />
                </div>
                <h1 class="font-black text-[22px] text-white m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Delivery Central
                </h1>
                <p class="text-[13px] text-gray-400 m-0 mt-1">Panel de administración</p>
            </div>

            <form @submit.prevent="handleLogin" class="bg-white rounded-3xl shadow-2xl p-7 flex flex-col gap-4">

                <div>
                    <label class="block text-[10.5px] font-black uppercase tracking-widest
                        text-gray-400 mb-1.5">
                        Correo electrónico
                    </label>
                    <input v-model="email" type="email" required placeholder="tu@correo.com" class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100
                   bg-gray-50 text-[14px] text-gray-900 outline-none
                   focus:border-blue-500 focus:bg-white
                   transition-all duration-200" />
                </div>

                <div>
                    <label class="block text-[10.5px] font-black uppercase tracking-widest
                        text-gray-400 mb-1.5">
                        Contraseña
                    </label>
                    <input v-model="password" type="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl border-2 border-gray-100
                   bg-gray-50 text-[14px] text-gray-900 outline-none
                   focus:border-blue-500 focus:bg-white
                   transition-all duration-200" />
                </div>

                <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 -translate-y-1"
                    leave-to-class="opacity-0">
                    <div v-if="errorMsg" class="px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                   text-[12.5px] text-red-600 flex items-center gap-2">
                        <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
                        {{ errorMsg }}
                    </div>
                </Transition>

                <button type="submit" :disabled="auth.loading" class="w-full py-3.5 rounded-2xl font-black text-[14px] text-white
                 border-none cursor-pointer transition-all duration-200
                 bg-blue-600 shadow-lg shadow-blue-600/30
                 hover:bg-blue-700 active:scale-[0.98]
                 disabled:opacity-50 disabled:cursor-not-allowed
                 flex items-center justify-center gap-2 mt-1">
                    <span v-if="auth.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                   rounded-full animate-spin" />
                    {{ auth.loading ? 'Ingresando...' : 'Iniciar sesión' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { TruckIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')

async function handleLogin() {
    errorMsg.value = ''
    const result = await auth.login(email.value, password.value)
    if (result.ok) {
        router.push('/')
    } else {
        errorMsg.value = result.message ?? 'Error al iniciar sesión'
    }
}
</script>