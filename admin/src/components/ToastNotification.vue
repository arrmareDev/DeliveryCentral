<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[600] flex flex-col gap-2 max-w-sm w-full px-4 sm:px-0">
            <TransitionGroup enter-active-class="transition-all duration-300"
                leave-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-x-8"
                leave-to-class="opacity-0 translate-x-8">
                <div v-for="t in toasts" :key="t.id" class="flex items-center gap-3 px-4 py-3.5 rounded-2xl shadow-lg border
                 bg-white" :class="borderClass(t.type)">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" :class="iconBg(t.type)">
                        <component :is="icon(t.type)" class="w-4.5 h-4.5" :class="iconColor(t.type)" />
                    </div>
                    <p class="text-[13px] font-semibold text-gray-800 flex-1 m-0">{{ t.message }}</p>
                    <button @click="remove(t.id)" class="w-6 h-6 rounded-full flex items-center justify-center
                   text-gray-300 hover:text-gray-500 hover:bg-gray-100
                   border-none bg-transparent cursor-pointer transition-colors shrink-0">
                        <XMarkIcon class="w-3.5 h-3.5" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import {
    CheckCircleIcon, ExclamationTriangleIcon, XCircleIcon, XMarkIcon, InformationCircleIcon,
} from '@heroicons/vue/24/outline'
import { useToastStore } from '../stores/toast'

const { toasts, remove } = useToastStore()

function icon(type: string) {
    const map: Record<string, any> = {
        success: CheckCircleIcon, error: XCircleIcon,
        warning: ExclamationTriangleIcon, info: InformationCircleIcon,
    }
    return map[type] ?? InformationCircleIcon
}

function iconBg(type: string): string {
    const map: Record<string, string> = {
        success: 'bg-green-50', error: 'bg-red-50',
        warning: 'bg-amber-50', info: 'bg-blue-50',
    }
    return map[type] ?? 'bg-gray-50'
}

function iconColor(type: string): string {
    const map: Record<string, string> = {
        success: 'text-green-600', error: 'text-red-600',
        warning: 'text-amber-600', info: 'text-blue-600',
    }
    return map[type] ?? 'text-gray-600'
}

function borderClass(type: string): string {
    const map: Record<string, string> = {
        success: 'border-green-100', error: 'border-red-100',
        warning: 'border-amber-100', info: 'border-blue-100',
    }
    return map[type] ?? 'border-gray-100'
}
</script>