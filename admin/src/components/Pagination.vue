<template>
    <div v-if="meta.last_page > 1" class="flex items-center justify-between flex-wrap gap-3 pt-2">
        <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0">
            Mostrando {{ rangeStart }}–{{ rangeEnd }} de {{ meta.total }}
        </p>
        <div class="flex items-center gap-1">
            <button @click="$emit('change', meta.current_page - 1)" :disabled="meta.current_page === 1" class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 disabled:opacity-40
                       disabled:cursor-not-allowed cursor-pointer hover:border-brand-primary transition-colors">
                <ChevronLeftIcon class="w-4 h-4" />
            </button>

            <button v-for="p in visiblePages" :key="p" @click="$emit('change', p)" class="min-w-8 h-8 px-2 rounded-lg text-[12.5px] font-bold cursor-pointer
                       border transition-colors"
                :class="p === meta.current_page
                    ? 'bg-brand-primary border-brand-primary text-white'
                    : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-brand-primary'">
                {{ p }}
            </button>

            <button @click="$emit('change', meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400 disabled:opacity-40
                       disabled:cursor-not-allowed cursor-pointer hover:border-brand-primary transition-colors">
                <ChevronRightIcon class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import type { PaginationMeta } from '../stores/negocios'

const props = defineProps<{ meta: PaginationMeta }>()
defineEmits<{ change: [page: number] }>()

const rangeStart = computed(() => (props.meta.current_page - 1) * props.meta.per_page + 1)
const rangeEnd = computed(() => Math.min(props.meta.current_page * props.meta.per_page, props.meta.total))

// Muestra máximo 5 números de página, centrados en la actual
const visiblePages = computed(() => {
    const total = props.meta.last_page
    const current = props.meta.current_page
    let start = Math.max(1, current - 2)
    let end = Math.min(total, start + 4)
    start = Math.max(1, end - 4)
    return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})
</script>