<template>
    <Transition enter-active-class="transition-opacity duration-150" leave-active-class="transition-opacity duration-100"
        enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="open" class="fixed inset-0 z-[9998] bg-black/50 backdrop-blur-sm flex items-start justify-center
                   pt-[10vh] px-4" @click.self="close">
            <Transition enter-active-class="transition-all duration-150 ease-out" enter-from-class="opacity-0 scale-95 -translate-y-2"
                leave-to-class="opacity-0 scale-95">
                <div v-if="open" class="w-full max-w-xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl
                           border border-gray-200 dark:border-gray-800 overflow-hidden flex flex-col max-h-[70vh]">

                    <!-- Input -->
                    <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-100 dark:border-gray-800 shrink-0">
                        <MagnifyingGlassIcon class="w-4.5 h-4.5 text-gray-400 dark:text-gray-500 shrink-0" />
                        <input ref="inputRef" v-model="query" @keydown="onKeydown" placeholder="Buscar pedido, restaurante o motorizado..."
                            class="flex-1 bg-transparent border-none outline-none text-[14px] text-gray-900 dark:text-gray-100
                               placeholder:text-gray-400 dark:placeholder:text-gray-500" />
                        <kbd class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800
                             text-gray-400 dark:text-gray-500 shrink-0">ESC</kbd>
                    </div>

                    <!-- Resultados -->
                    <div class="flex-1 overflow-y-auto">
                        <div v-if="loading" class="flex flex-col gap-2 p-3">
                            <div v-for="n in 3" :key="n" class="h-12 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                        </div>

                        <div v-else-if="query.trim().length < 2" class="py-14 text-center text-gray-400 dark:text-gray-600 text-[13px]">
                            Escribe al menos 2 caracteres para buscar
                        </div>

                        <div v-else-if="totalResultados === 0" class="py-14 text-center text-gray-400 dark:text-gray-600 text-[13px]">
                            Sin resultados para "{{ query }}"
                        </div>

                        <div v-else class="py-2">
                            <div v-if="resultados.despachos.length" class="mb-1">
                                <p class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Despachos
                                </p>
                                <button v-for="(item, i) in resultados.despachos" :key="'d-' + item.id"
                                    ref="itemRefs" @click="ir('despacho', item)" :class="rowClass(flatIndex('despachos', i))"
                                    class="w-full text-left px-4 py-2.5 flex items-center justify-between gap-2
                                       border-none bg-transparent cursor-pointer transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0 truncate">{{ item.titulo }}</p>
                                        <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0 truncate">{{ item.subtitulo }}</p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
                                        :class="despachoCls(item.estado)">
                                        {{ item.estado }}
                                    </span>
                                </button>
                            </div>

                            <div v-if="resultados.restaurantes.length" class="mb-1">
                                <p class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Restaurantes
                                </p>
                                <button v-for="(item, i) in resultados.restaurantes" :key="'r-' + item.id"
                                    ref="itemRefs" @click="ir('restaurante', item)" :class="rowClass(flatIndex('restaurantes', i))"
                                    class="w-full text-left px-4 py-2.5 flex flex-col border-none bg-transparent
                                       cursor-pointer transition-colors">
                                    <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">{{ item.titulo }}</p>
                                    <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0">{{ item.subtitulo }}</p>
                                </button>
                            </div>

                            <div v-if="resultados.motorizados.length">
                                <p class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Motorizados
                                </p>
                                <button v-for="(item, i) in resultados.motorizados" :key="'m-' + item.id"
                                    ref="itemRefs" @click="ir('motorizado', item)" :class="rowClass(flatIndex('motorizados', i))"
                                    class="w-full text-left px-4 py-2.5 flex flex-col border-none bg-transparent
                                       cursor-pointer transition-colors">
                                    <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">{{ item.titulo }}</p>
                                    <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0">{{ item.subtitulo }}</p>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer con atajos -->
                    <div class="px-4 py-2 border-t border-gray-100 dark:border-gray-800 flex items-center gap-3 shrink-0
                                text-[10.5px] text-gray-400 dark:text-gray-500">
                        <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-800">↑↓</kbd> navegar</span>
                        <span class="flex items-center gap-1"><kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-800">↵</kbd> abrir</span>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import api from '../utils/api'

interface ResultItem { id: number; titulo: string; subtitulo: string; estado?: string }

const router = useRouter()
const open = ref(false)
const query = ref('')
const loading = ref(false)
const activeIndex = ref(0)
const inputRef = ref<HTMLInputElement | null>(null)
const itemRefs = ref<HTMLElement[]>([])

const resultados = ref<{ despachos: ResultItem[]; restaurantes: ResultItem[]; motorizados: ResultItem[] }>({
    despachos: [], restaurantes: [], motorizados: [],
})

const totalResultados = computed(() =>
    resultados.value.despachos.length + resultados.value.restaurantes.length + resultados.value.motorizados.length
)

// Índice plano para navegación con flechas a través de las 3 secciones
function flatIndex(seccion: 'despachos' | 'restaurantes' | 'motorizados', i: number): number {
    if (seccion === 'despachos') return i
    if (seccion === 'restaurantes') return resultados.value.despachos.length + i
    return resultados.value.despachos.length + resultados.value.restaurantes.length + i
}

function rowClass(index: number): string {
    return index === activeIndex.value
        ? 'bg-gray-50 dark:bg-gray-800'
        : 'hover:bg-gray-50 dark:hover:bg-gray-800/60'
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null

async function buscar() {
    if (query.value.trim().length < 2) {
        resultados.value = { despachos: [], restaurantes: [], motorizados: [] }
        return
    }
    loading.value = true
    try {
        const { data } = await api.get('/admin/buscar', { params: { q: query.value.trim() } })
        resultados.value = data.data
        activeIndex.value = 0
    } finally {
        loading.value = false
    }
}

watch(query, () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(buscar, 300)
})

function openSearch() {
    open.value = true
    query.value = ''
    resultados.value = { despachos: [], restaurantes: [], motorizados: [] }
    activeIndex.value = 0
    nextTick(() => inputRef.value?.focus())
}

function close() {
    open.value = false
}

function ir(tipo: 'despacho' | 'restaurante' | 'motorizado', item: ResultItem) {
    close()
    if (tipo === 'despacho') router.push('/despachos')
    else if (tipo === 'restaurante') router.push('/restaurantes')
    else router.push('/motorizados')
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        close()
        return
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault()
        activeIndex.value = Math.min(activeIndex.value + 1, totalResultados.value - 1)
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        activeIndex.value = Math.max(activeIndex.value - 1, 0)
    } else if (e.key === 'Enter') {
        e.preventDefault()
        const all = [
            ...resultados.value.despachos.map(i => ({ tipo: 'despacho' as const, item: i })),
            ...resultados.value.restaurantes.map(i => ({ tipo: 'restaurante' as const, item: i })),
            ...resultados.value.motorizados.map(i => ({ tipo: 'motorizado' as const, item: i })),
        ]
        const sel = all[activeIndex.value]
        if (sel) ir(sel.tipo, sel.item)
    }
}

function despachoCls(estado?: string): string {
    const m: Record<string, string> = {
        solicitado: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        aceptado: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        recogido: 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
        entregado: 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
        cancelado: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    }
    return m[estado ?? ''] ?? m.cancelado
}

// Global shortcut Cmd+K / Ctrl+K
function onGlobalKeydown(e: KeyboardEvent) {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        open.value ? close() : openSearch()
    }
}

defineExpose({ openSearch })

import { onMounted, onUnmounted } from 'vue'
onMounted(() => window.addEventListener('keydown', onGlobalKeydown))
onUnmounted(() => window.removeEventListener('keydown', onGlobalKeydown))
</script>