<template>
    <div class="relative">
        <button @click="open = !open" :disabled="loading" class="flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-900 text-[13px] font-semibold text-gray-600 dark:text-gray-300 cursor-pointer
                   hover:border-brand-primary hover:text-brand-primary transition-all duration-150
                   disabled:opacity-50">
            <span v-if="loading"
                class="w-3.5 h-3.5 border-2 border-gray-300 border-t-brand-primary rounded-full animate-spin" />
            <ArrowDownTrayIcon v-else class="w-4 h-4" />
            Exportar
        </button>

        <Transition enter-active-class="transition-all duration-150" enter-from-class="opacity-0 scale-95"
            leave-to-class="opacity-0 scale-95">
            <div v-if="open" v-click-outside="() => open = false" class="absolute right-0 mt-2 w-44 bg-white dark:bg-gray-900 rounded-xl border
                       border-gray-100 dark:border-gray-800 shadow-lg z-30 overflow-hidden">
                <button @click="descargar('pdf')" class="w-full text-left px-4 py-2.5 text-[13px] font-semibold text-gray-700 dark:text-gray-300
                           hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-none bg-transparent
                           flex items-center gap-2 transition-colors">
                    <DocumentTextIcon class="w-4 h-4 text-red-500" /> PDF
                </button>
                <button @click="descargar('excel')" class="w-full text-left px-4 py-2.5 text-[13px] font-semibold text-gray-700 dark:text-gray-300
                           hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-none bg-transparent
                           flex items-center gap-2 transition-colors border-t border-gray-50 dark:border-gray-800">
                    <TableCellsIcon class="w-4 h-4 text-green-600" /> Excel
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ArrowDownTrayIcon, DocumentTextIcon, TableCellsIcon } from '@heroicons/vue/24/outline'
import api from '../utils/api'
import { useToastStore } from '../stores/toast'

const props = defineProps<{
    endpoint: string          // ej. '/admin/reportes/despachos'
    params?: Record<string, any>
    filename?: string
}>()

const toast = useToastStore()
const open = ref(false)
const loading = ref(false)

async function descargar(formato: 'pdf' | 'excel') {
    open.value = false
    loading.value = true
    try {
        const response = await api.get(`${props.endpoint}/${formato}`, {
            params: props.params,
            responseType: 'blob',
        })

        const blob = new Blob([response.data])
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `${props.filename ?? 'reporte'}.${formato === 'pdf' ? 'pdf' : 'xlsx'}`
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
    } catch {
        toast.error('Error al generar el reporte')
    } finally {
        loading.value = false
    }
}
</script>