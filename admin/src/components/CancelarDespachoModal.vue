<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="modelValue" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm
                       flex items-center justify-center p-4" @click.self="close">

                <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6 sm:p-7">

                    <div
                        class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-500/10 mx-auto mb-4 flex items-center justify-center">
                        <ExclamationTriangleIcon class="w-7 h-7 text-red-600 dark:text-red-400" />
                    </div>

                    <h3 class="font-black text-[18px] text-center m-0 mb-2 text-gray-900 dark:text-gray-100">
                        ¿Cancelar despacho?
                    </h3>
                    <p class="text-[13.5px] text-center m-0 mb-5 leading-relaxed text-gray-500 dark:text-gray-400">
                        {{ message }}
                    </p>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label
                            class="text-[11px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            Motivo de la cancelación
                        </label>
                        <select v-model="motivoSeleccionado" class="w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-100 dark:border-gray-700
                       bg-gray-50 dark:bg-gray-800 text-[13.5px] text-gray-800 dark:text-gray-200 outline-none
                       focus:border-red-400 focus:bg-white dark:focus:bg-gray-900 transition-all duration-150">
                            <option value="" disabled>Selecciona un motivo</option>
                            <option v-for="m in MOTIVOS" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>

                    <Transition enter-active-class="transition-all duration-150"
                        enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                        <div v-if="motivoSeleccionado === 'Otro'" class="flex flex-col gap-1.5 mb-4">
                            <label
                                class="text-[11px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Especifica el motivo
                            </label>
                            <textarea v-model="motivoOtro" rows="2" maxlength="255"
                                placeholder="Describe brevemente el motivo..." class="w-full px-3.5 py-2.5 rounded-xl border-2 border-gray-100 dark:border-gray-700
                           bg-gray-50 dark:bg-gray-800 text-[13.5px] text-gray-800 dark:text-gray-200 outline-none resize-none
                           focus:border-red-400 focus:bg-white dark:focus:bg-gray-900 transition-all duration-150" />
                        </div>
                    </Transition>

                    <div class="flex gap-3 mt-2">
                        <button @click="close" :disabled="loading" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700
                       text-gray-600 dark:text-gray-300 font-semibold text-[13.5px] cursor-pointer
                       bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600
                       transition-all duration-150 disabled:opacity-50">
                            No, mantener
                        </button>
                        <button @click="confirmar" :disabled="loading || !motivoValido" class="flex-1 py-3 rounded-2xl font-bold text-[13.5px] text-white bg-red-600 hover:bg-red-700
                       border-none cursor-pointer transition-all duration-150 flex items-center justify-center gap-2
                       disabled:opacity-40 disabled:cursor-not-allowed">
                            <span v-if="loading"
                                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            {{ loading ? 'Cancelando...' : 'Sí, cancelar despacho' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid'

const props = defineProps<{
    modelValue: boolean
    message: string
    loading?: boolean
}>()

const emit = defineEmits<{
    'update:modelValue': [value: boolean]
    confirm: [motivo: string]
}>()

const MOTIVOS = [
    'Cliente canceló',
    'Restaurante sin stock',
    'Motorizado no disponible',
    'Dirección incorrecta',
    'Pedido duplicado',
    'Cliente no responde',
    'Problema con el pago',
    'Otro',
]

const motivoSeleccionado = ref('')
const motivoOtro = ref('')

const motivoValido = computed(() => {
    if (!motivoSeleccionado.value) return false
    if (motivoSeleccionado.value === 'Otro') return motivoOtro.value.trim().length > 0
    return true
})

function close() {
    emit('update:modelValue', false)
}

function confirmar() {
    if (!motivoValido.value) return
    const motivoFinal = motivoSeleccionado.value === 'Otro'
        ? motivoOtro.value.trim()
        : motivoSeleccionado.value
    emit('confirm', motivoFinal)
}

watch(() => props.modelValue, (open) => {
    if (open) {
        motivoSeleccionado.value = ''
        motivoOtro.value = ''
    }
})
</script>