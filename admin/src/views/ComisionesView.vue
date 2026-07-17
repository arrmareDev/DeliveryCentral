<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Comisiones
                </h1>
                <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">
                    Deuda de cada motorizado por comisiones de entrega
                </p>
            </div>
        </div>

        <!-- ══ SELECTOR DE RANGO ══ -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                    shadow-sm dark:shadow-none p-3 flex flex-wrap items-center gap-2 transition-colors duration-200">
            <button v-for="p in PRESETS" :key="p.value" @click="seleccionarPreset(p.value)" class="px-3.5 py-2 rounded-xl text-[12.5px] font-bold cursor-pointer
                       border transition-all duration-150"
                :class="rango.preset === p.value
                    ? 'bg-red-600 border-red-600 text-white'
                    : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-red-200 dark:hover:border-red-500/40'">
                {{ p.label }}
            </button>

            <div class="flex items-center gap-2 ml-auto">
                <input v-model="rango.desdeInput" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                           outline-none focus:border-red-400 transition-all duration-150" />
                <span class="text-gray-400 dark:text-gray-500 text-[12px]">a</span>
                <input v-model="rango.hastaInput" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                           outline-none focus:border-red-400 transition-all duration-150" />
                <button @click="aplicarRangoPersonalizado" :disabled="!rango.desdeInput || !rango.hastaInput" class="px-3.5 py-2 rounded-xl text-[12.5px] font-bold cursor-pointer
                           border-none bg-gray-900 dark:bg-gray-700 text-white hover:bg-gray-800 dark:hover:bg-gray-600
                           disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-150">
                    Aplicar
                </button>
                <ExportButtons endpoint="/admin/reportes/comisiones" :params="paramsRango()" filename="comisiones" />
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                    Deuda pendiente {{ rango.label }}
                </p>
                <div class="flex items-baseline gap-1">
                    <span class="text-[14px] text-gray-400 dark:text-gray-500 font-bold">S/</span>
                    <p class="font-black text-[28px] text-purple-600 dark:text-purple-400 leading-none m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ deudaTotal.toFixed(2) }}
                    </p>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                    Cobrado {{ rango.label }}
                </p>
                <div class="flex items-baseline gap-1">
                    <span class="text-[14px] text-gray-400 dark:text-gray-500 font-bold">S/</span>
                    <p class="font-black text-[28px] text-green-600 dark:text-green-400 leading-none m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ cobradoTotal.toFixed(2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="store.loading && !store.motorizadoActual" class="flex flex-col gap-3">
            <div v-for="n in 4" :key="n" class="h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
        </div>

        <!-- Empty -->
        <div v-else-if="store.resumen.length === 0"
            class="flex flex-col items-center py-20 text-gray-400 dark:text-gray-600 gap-4">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <BanknotesIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
            </div>
            <p class="font-bold text-gray-600 dark:text-gray-400 text-[15px] m-0">
                Sin comisiones registradas {{ rango.label }}
            </p>
        </div>

        <!-- Lista de motorizados con deuda -->
        <div v-else class="flex flex-col gap-3">
            <div v-for="m in sortedResumen" :key="m.id"
                class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
               shadow-sm dark:shadow-none p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4 transition-colors duration-200">

                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-11 h-11 rounded-full bg-purple-100 dark:bg-purple-500/15 flex items-center
                      justify-center text-[14px] font-black text-purple-600 dark:text-purple-400 shrink-0">
                        {{ m.nombre.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold text-[14px] text-gray-900 dark:text-gray-100 m-0 truncate">{{ m.nombre }}
                        </p>
                        <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0">
                            {{ m.telefono }}
                            <span v-if="m.comisiones_pendientes"
                                class="text-amber-600 dark:text-amber-400 font-semibold">
                                · {{ m.comisiones_pendientes }} pendientes
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-6 sm:gap-8">
                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">
                            Debe
                        </p>
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                            <span class="font-black text-[18px]"
                                :class="m.deuda_pendiente > 0 ? 'text-purple-600 dark:text-purple-400' : 'text-gray-300 dark:text-gray-700'">
                                {{ m.deuda_pendiente.toFixed(2) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-0.5">
                            Cobrado
                        </p>
                        <div class="flex items-baseline gap-0.5">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                            <span class="font-black text-[18px] text-green-600 dark:text-green-400">{{
                                m.total_cobrado.toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0">
                    <button @click="openDetalle(m.id)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl
                   text-[12.5px] font-bold border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                   text-gray-600 dark:text-gray-300 cursor-pointer hover:border-brand-primary hover:text-brand-primary
                   transition-all duration-150">
                        <EyeIcon class="w-3.5 h-3.5" />
                        Ver detalle
                    </button>
                    <button v-if="m.deuda_pendiente > 0" @click="askCobrarTodo(m)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl
                   text-[12.5px] font-bold border-none cursor-pointer
                   bg-green-600 text-white hover:bg-green-700
                   transition-all duration-150">
                        <CheckCircleIcon class="w-3.5 h-3.5" />
                        Cobrar {{ rango.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ MODAL DETALLE ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="detalleModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="closeDetalle">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="detalleModal.show" class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6
                     max-h-[85vh] flex flex-col">

                            <div class="flex items-center justify-between mb-4 shrink-0">
                                <div>
                                    <h3 class="font-black text-[17px] text-gray-900 dark:text-gray-100 m-0"
                                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                                        {{ store.motorizadoActual?.nombre }}
                                    </h3>
                                    <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">
                                        Debe S/ {{ store.deudaPendiente.toFixed(2) }} {{ rango.label }}
                                        <span
                                            v-if="store.deudaTotalHistorica && store.deudaTotalHistorica !== store.deudaPendiente"
                                            class="text-gray-300 dark:text-gray-700">
                                            · S/ {{ store.deudaTotalHistorica.toFixed(2) }} en total histórico
                                        </span>
                                    </p>
                                </div>
                                <button @click="closeDetalle" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                         cursor-pointer border-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto -mx-2 px-2">
                                <div v-if="store.loading" class="flex flex-col gap-2">
                                    <div v-for="n in 4" :key="n"
                                        class="h-14 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                                </div>

                                <div v-else-if="store.detalle.length === 0"
                                    class="flex flex-col items-center py-10 text-gray-400 dark:text-gray-600 gap-2">
                                    <BanknotesIcon class="w-8 h-8 text-gray-300 dark:text-gray-700" />
                                    <p class="text-[13px] m-0">Sin comisiones en este rango</p>
                                </div>

                                <div v-else class="flex flex-col gap-2">
                                    <div v-for="c in store.detalle" :key="c.id"
                                        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/60 dark:bg-gray-800/60">
                                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                            :class="c.estado === 'pendiente' ? 'bg-amber-50 dark:bg-amber-500/10' : 'bg-green-50 dark:bg-green-500/10'">
                                            <component :is="c.estado === 'pendiente' ? ClockIcon : CheckCircleIcon"
                                                class="w-4.5 h-4.5"
                                                :class="c.estado === 'pendiente' ? 'text-amber-500 dark:text-amber-400' : 'text-green-500 dark:text-green-400'" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-[12.5px] text-gray-900 dark:text-gray-100 m-0">
                                                #{{ c.order_id }} · {{ c.restaurant }}
                                            </p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 m-0">
                                                {{ formatFecha(c.created_at) }}
                                                <span v-if="c.cobrado_at"> · Cobrado {{ formatFecha(c.cobrado_at)
                                                    }}</span>
                                            </p>
                                        </div>
                                        <div class="flex items-baseline gap-0.5 shrink-0">
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">S/</span>
                                            <span class="font-black text-[14px]"
                                                :class="c.estado === 'pendiente' ? 'text-purple-600 dark:text-purple-400' : 'text-green-600 dark:text-green-400'">
                                                {{ c.monto.toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Pagination v-if="!store.loading && store.detalle.length > 0" :meta="store.detalleMeta"
                                @change="cambiarPaginaDetalle" class="shrink-0 mt-2" />

                            <div v-if="store.deudaPendiente > 0"
                                class="pt-4 mt-2 border-t border-gray-100 dark:border-gray-800 shrink-0">
                                <button @click="askCobrarDesdeDetalle" class="w-full py-3 rounded-2xl font-bold text-[13.5px] text-white
                         bg-green-600 border-none cursor-pointer hover:bg-green-700
                         transition-all duration-150 flex items-center justify-center gap-2">
                                    <CheckCircleIcon class="w-4 h-4" />
                                    Marcar como cobrado {{ rango.label }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Confirmación de cobro -->
        <ConfirmModal v-model="confirmCobro.show" title="¿Confirmar cobro?"
            :message="`Estás confirmando que ${confirmCobro.nombre} te pagó S/ ${confirmCobro.monto.toFixed(2)} (${rango.label}). Esta acción marcará esas comisiones como cobradas.`"
            variant="success" confirm-label="Sí, ya me pagó" :loading="confirmCobro.loading" @confirm="executeCobro" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch, onUnmounted } from 'vue'
import {
    BanknotesIcon, EyeIcon, CheckCircleIcon, XMarkIcon, ClockIcon,
} from '@heroicons/vue/24/outline'
import { useComisionesStore, type ComisionResumen } from '../stores/comisiones'
import ConfirmModal from '../components/ConfirmModal.vue'
import Pagination from '../components/Pagination.vue'
import { useToastStore } from '../stores/toast'
import { useBreadcrumbsStore } from '../stores/breadcrumbs'
import ExportButtons from '../components/ExportButtons.vue'

const breadcrumbsStore = useBreadcrumbsStore()

const store = useComisionesStore()
const toast = useToastStore()

// ── Selector de rango ──────────────────────────────────────
const PRESETS = [
    { value: 'todo', label: 'Histórico' },
    { value: 'hoy', label: 'Hoy' },
    { value: 'semana', label: 'Esta semana' },
    { value: 'mes', label: 'Este mes' },
] as const

const rango = reactive({
    preset: 'todo' as string,
    desde: '' as string,
    hasta: '' as string,
    desdeInput: '',
    hastaInput: '',
    label: 'histórico',
})

function seleccionarPreset(preset: string) {
    rango.preset = preset
    rango.desde = ''
    rango.hasta = ''
    rango.desdeInput = ''
    rango.hastaInput = ''
    rango.label = PRESETS.find(p => p.value === preset)?.label.toLowerCase() ?? 'histórico'
    recargar()
}

function aplicarRangoPersonalizado() {
    if (!rango.desdeInput || !rango.hastaInput) return
    rango.preset = ''
    rango.desde = rango.desdeInput
    rango.hasta = rango.hastaInput
    rango.label = `${rango.desdeInput} al ${rango.hastaInput}`
    recargar()
}

function paramsRango() {
    if (rango.desde && rango.hasta) {
        return { desde: rango.desde, hasta: rango.hasta }
    }
    if (rango.preset && rango.preset !== 'todo') {
        return { preset: rango.preset }
    }
    return {}
}

async function recargar() {
    await store.fetchResumen(paramsRango())
}

onMounted(() => recargar())

const deudaTotal = computed(() => store.resumen.reduce((s, m) => s + m.deuda_pendiente, 0))
const cobradoTotal = computed(() => store.resumen.reduce((s, m) => s + m.total_cobrado, 0))

const sortedResumen = computed(() =>
    [...store.resumen].sort((a, b) => b.deuda_pendiente - a.deuda_pendiente)
)

function formatFecha(d: string | null): string {
    if (!d) return '—'
    return new Date(d).toLocaleString('es-PE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

// ── Modal detalle ──────────────────────────────────────────
const detalleModal = reactive({ show: false, motorizadoId: 0, page: 1 })

async function openDetalle(id: number) {
    detalleModal.motorizadoId = id
    detalleModal.page = 1
    detalleModal.show = true
    await store.fetchDetalle(id, { ...paramsRango(), page: 1 })

    if (store.motorizadoActual) {
        breadcrumbsStore.setExtra(store.motorizadoActual.nombre)
    }
}

async function cambiarPaginaDetalle(p: number) {
    detalleModal.page = p
    await store.fetchDetalle(detalleModal.motorizadoId, { ...paramsRango(), page: p })
}

function closeDetalle() {
    detalleModal.show = false
    breadcrumbsStore.clearExtra()
}

onUnmounted(() => breadcrumbsStore.clearExtra())

// ── Confirmar cobro ────────────────────────────────────────
const confirmCobro = reactive({ show: false, loading: false, motorizadoId: 0, nombre: '', monto: 0 })

function askCobrarTodo(m: ComisionResumen) {
    confirmCobro.motorizadoId = m.id
    confirmCobro.nombre = m.nombre
    confirmCobro.monto = m.deuda_pendiente
    confirmCobro.show = true
}

function askCobrarDesdeDetalle() {
    if (!store.motorizadoActual) return
    confirmCobro.motorizadoId = store.motorizadoActual.id
    confirmCobro.nombre = store.motorizadoActual.nombre
    confirmCobro.monto = store.deudaPendiente
    confirmCobro.show = true
}

async function executeCobro() {
    confirmCobro.loading = true
    const result = await store.cobrar(confirmCobro.motorizadoId, { ...paramsRango(), page: detalleModal.page })
    confirmCobro.loading = false
    confirmCobro.show = false

    if (result.ok) {
        toast.success(`S/ ${result.total?.toFixed(2)} marcado como cobrado`)
        closeDetalle()
        await recargar()
    } else {
        toast.error('Error al registrar el cobro')
    }
}
</script>