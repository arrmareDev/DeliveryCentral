<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Despachos
                </h1>
                <p class="text-[13px] text-gray-400 mt-1 m-0">Panel en tiempo real de todos tus clientes</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span class="text-[12px] text-gray-400 font-medium hidden sm:inline">En vivo</span>
                <button @click="store.fetchAll()" :disabled="store.loading" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200
                 bg-white text-[12px] font-semibold text-gray-600 cursor-pointer
                 hover:border-brand-primary hover:text-brand-primary
                 transition-all duration-150 disabled:opacity-50">
                    <ArrowPathIcon class="w-3.5 h-3.5" :class="store.loading ? 'animate-spin' : ''" />
                    Actualizar
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Activos</p>
                <p class="font-black text-[24px] text-brand-primary leading-none m-0">{{ store.stats.total_activos }}
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Entregados hoy</p>
                <p class="font-black text-[24px] text-green-600 leading-none m-0">{{ store.stats.entregados_hoy }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Mot. ocupados</p>
                <p class="font-black text-[24px] text-amber-600 leading-none m-0">{{ store.stats.motorizados_ocupados }}
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Mot. disponibles</p>
                <p class="font-black text-[24px] text-blue-600 leading-none m-0">{{ store.stats.motorizados_disponibles
                    }}</p>
            </div>
        </div>

        <!-- Filtro por restaurante -->
        <select v-model="restaurantFilter" @change="store.fetchAll(restaurantFilter || undefined)" class="self-start px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white
             text-[13px] text-gray-700 outline-none cursor-pointer
             focus:border-brand-primary transition-all duration-200 font-semibold">
            <option :value="undefined">Todos los restaurantes</option>
            <option v-for="r in restaurants.restaurants" :key="r.id" :value="r.id">{{ r.name }}</option>
        </select>

        <!-- Activos -->
        <div>
            <h2 class="font-black text-[15px] text-gray-900 mb-3">
                En curso
                <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-blue-50 text-brand-primary border border-blue-200">
                    {{ store.activos.length }}
                </span>
            </h2>

            <div v-if="store.loading" class="flex flex-col gap-3">
                <div v-for="n in 3" :key="n" class="h-32 rounded-2xl bg-gray-100 animate-pulse" />
            </div>

            <div v-else-if="store.activos.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm
               flex flex-col items-center py-12 gap-3 text-gray-400">
                <TruckIcon class="w-10 h-10 text-gray-300" />
                <p class="font-bold text-gray-600 text-[14px] m-0">Sin despachos activos</p>
            </div>

            <div v-else class="flex flex-col gap-3">
                <TransitionGroup name="despacho">
                    <div v-for="d in store.activos" :key="d.id" class="bg-white rounded-2xl border-2 border-gray-100 shadow-sm overflow-hidden
                   hover:border-blue-200 transition-all duration-150">
                        <div class="p-4">

                            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-black px-2 py-0.5 rounded-lg bg-gray-100
                               text-gray-600 border border-gray-200 font-mono">
                                        #{{ d.order_id }}
                                    </span>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                               bg-purple-50 text-purple-700 border border-purple-200">
                                        {{ d.restaurant }}
                                    </span>
                                    <span :class="despachoCls(d.estado)"
                                        class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border">
                                        {{ despachoLabel(d.estado) }}
                                    </span>
                                </div>
                                <span class="text-[11.5px] text-gray-400">{{ formatFecha(d.solicitado_at) }}</span>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                                        Cliente</p>
                                    <p class="font-bold text-[14px] text-gray-900 m-0">{{ d.order?.client_name }}</p>
                                    <p class="text-[12px] text-gray-500 m-0 mt-0.5 flex items-center gap-1">
                                        <MapPinIcon class="w-3.5 h-3.5 shrink-0" />
                                        {{ d.order?.address }}, {{ d.order?.district }}
                                    </p>
                                    <div class="flex items-baseline gap-0.5 mt-1.5">
                                        <span class="text-[11px] text-gray-400">S/</span>
                                        <span class="font-black text-[16px] text-brand-primary leading-none">
                                            {{ d.order?.total.toFixed(2) }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 mb-1.5">
                                        Motorizado</p>
                                    <div v-if="d.motorizado" class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center
                                text-[13px] font-black text-gray-500 border border-gray-200 shrink-0">
                                            {{ d.motorizado.nombre.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[13px] text-gray-900 m-0">{{ d.motorizado.nombre }}
                                            </p>
                                            <a :href="`https://wa.me/51${d.motorizado.telefono.replace(/\D/g, '')}`"
                                                target="_blank"
                                                class="text-[11.5px] text-green-600 no-underline hover:underline font-medium">
                                                {{ d.motorizado.telefono }}
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center gap-2 text-amber-600">
                                        <ClockIcon class="w-4 h-4 shrink-0" />
                                        <span class="text-[12.5px] font-medium">Esperando motorizado...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-3 pt-3 border-t border-gray-100">
                                <button @click="askCancel(d)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold
                         border cursor-pointer border-red-200 text-red-500 bg-red-50
                         hover:bg-red-100 transition-all duration-150">
                                    <XCircleIcon class="w-3.5 h-3.5" />
                                    Cancelar despacho
                                </button>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>

        <!-- Entregados hoy -->
        <div>
            <h2 class="font-black text-[15px] text-gray-900 mb-3">
                Entregados hoy
                <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-green-50 text-green-700 border border-green-200">
                    {{ store.entregadosHoy.length }}
                </span>
            </h2>

            <div v-if="store.entregadosHoy.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm
               flex items-center justify-center py-10 text-gray-400 text-[13px]">
                Sin entregas completadas hoy
            </div>

            <div v-else class="flex flex-col gap-2">
                <div v-for="d in store.entregadosHoy" :key="d.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4
                 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-green-50 border border-green-200
                      flex items-center justify-center shrink-0">
                        <CheckCircleIcon class="w-5 h-5 text-green-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-mono font-black text-gray-500">#{{ d.order_id }}</span>
                            <span class="font-bold text-[13px] text-gray-900 truncate">{{ d.order?.client_name }}</span>
                        </div>
                        <p class="text-[12px] text-gray-400 m-0 mt-0.5">
                            {{ d.restaurant }} · {{ d.motorizado?.nombre ?? '—' }}
                        </p>
                    </div>
                    <div class="flex items-baseline gap-0.5 shrink-0">
                        <span class="text-[11px] text-gray-400">S/</span>
                        <span class="font-black text-[16px] text-green-600 leading-none">
                            {{ d.order?.total.toFixed(2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmModal v-model="confirmCancel.show" title="¿Cancelar despacho?"
            :message="`El despacho del pedido #${confirmCancel.target?.order_id} de ${confirmCancel.target?.restaurant} será cancelado y el motorizado quedará disponible nuevamente.`"
            variant="danger" confirm-label="Sí, cancelar despacho" :loading="confirmCancel.loading"
            @confirm="executeCancel" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import {
    TruckIcon, MapPinIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { useDespachosStore, type DespachoItem } from '../stores/despachos'
import { useRestaurantsStore } from '../stores/restaurants'
import { useEcho } from '../composables/useHecho.ts'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToastStore } from '../stores/toast'

const store = useDespachosStore()
const restaurants = useRestaurantsStore()
const toast = useToastStore()
const restaurantFilter = ref<number | undefined>(undefined)

let echo: any = null

onMounted(async () => {
    await Promise.all([store.fetchAll(), restaurants.fetchAll()])
    echo = useEcho()
    echo.channel('admin.despachos')
        .listen('.despacho.actualizado', (data: any) => store.handleRealtimeUpdate(data))
})

onUnmounted(() => {
    try { echo?.leaveChannel('admin.despachos') } catch { /* noop */ }
})

function despachoLabel(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'Buscando motorizado', aceptado: 'Motorizado asignado',
        recogido: 'Recogido en local', entregado: 'Entregado', cancelado: 'Cancelado',
    }
    return m[s] ?? s
}

function despachoCls(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'bg-amber-50 text-amber-700 border-amber-200',
        aceptado: 'bg-blue-50 text-blue-700 border-blue-200',
        recogido: 'bg-orange-50 text-orange-700 border-orange-200',
        entregado: 'bg-green-50 text-green-700 border-green-200',
        cancelado: 'bg-gray-100 text-gray-500 border-gray-200',
    }
    return m[s] ?? m.cancelado
}

function formatFecha(d: string | null): string {
    if (!d) return '—'
    return new Date(d).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
}

const confirmCancel = reactive({ show: false, loading: false, target: null as DespachoItem | null })

function askCancel(d: DespachoItem) {
    confirmCancel.target = d
    confirmCancel.show = true
}

async function executeCancel() {
    if (!confirmCancel.target) return
    confirmCancel.loading = true
    const ok = await store.cancelar(confirmCancel.target.id)
    confirmCancel.loading = false
    confirmCancel.show = false
    ok ? toast.success('Despacho cancelado') : toast.error('Error al cancelar el despacho')
}
</script>

<style scoped>
.despacho-enter-active {
    transition: all 0.3s ease;
}

.despacho-leave-active {
    transition: all 0.2s ease;
}

.despacho-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}

.despacho-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>