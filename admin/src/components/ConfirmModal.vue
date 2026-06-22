<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="modelValue" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
               flex items-center justify-center p-4" @click.self="$emit('update:modelValue', false)">
                <Transition enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                    <div v-if="modelValue" class="w-full max-w-sm bg-white rounded-3xl shadow-2xl p-7 text-center">

                        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                            :class="variantClasses.iconBg">
                            <component :is="icon" class="w-7 h-7" :class="variantClasses.iconColor" />
                        </div>

                        <h3 class="font-black text-[19px] text-gray-900 m-0 mb-2"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ title }}
                        </h3>
                        <p class="text-[13.5px] text-gray-400 m-0 mb-6 leading-relaxed">
                            {{ message }}
                        </p>

                        <div class="flex gap-3">
                            <button @click="$emit('update:modelValue', false)" :disabled="loading" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                       font-semibold text-[13.5px] cursor-pointer bg-white
                       hover:border-gray-300 transition-all duration-150
                       disabled:opacity-50">
                                {{ cancelLabel }}
                            </button>
                            <button @click="$emit('confirm')" :disabled="loading" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px]
                       cursor-pointer border-none transition-all duration-150
                       disabled:opacity-50 flex items-center justify-center gap-2" :class="variantClasses.confirmBtn">
                                <span v-if="loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                         rounded-full animate-spin" />
                                {{ loading ? loadingLabel : confirmLabel }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import {
    ExclamationTriangleIcon, TrashIcon, XCircleIcon, CheckCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps<{
    modelValue: boolean
    title: string
    message: string
    variant?: 'danger' | 'warning' | 'success' | 'info'
    confirmLabel?: string
    cancelLabel?: string
    loadingLabel?: string
    loading?: boolean
}>()

defineEmits<{
    'update:modelValue': [value: boolean]
    confirm: []
}>()

const confirmLabel = computed(() => props.confirmLabel ?? 'Confirmar')
const cancelLabel = computed(() => props.cancelLabel ?? 'Cancelar')
const loadingLabel = computed(() => props.loadingLabel ?? 'Procesando...')

const icon = computed(() => {
    const map = {
        danger: TrashIcon,
        warning: ExclamationTriangleIcon,
        success: CheckCircleIcon,
        info: XCircleIcon,
    }
    return map[props.variant ?? 'warning']
})

const variantClasses = computed(() => {
    const map = {
        danger: {
            iconBg: 'bg-red-50', iconColor: 'text-red-500',
            confirmBtn: 'bg-red-600 hover:bg-red-700',
        },
        warning: {
            iconBg: 'bg-amber-50', iconColor: 'text-amber-500',
            confirmBtn: 'bg-amber-500 hover:bg-amber-600',
        },
        success: {
            iconBg: 'bg-green-50', iconColor: 'text-green-500',
            confirmBtn: 'bg-green-600 hover:bg-green-700',
        },
        info: {
            iconBg: 'bg-blue-50', iconColor: 'text-blue-500',
            confirmBtn: 'bg-blue-600 hover:bg-blue-700',
        },
    }
    return map[props.variant ?? 'warning']
})
</script>