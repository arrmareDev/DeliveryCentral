<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Despachos
                </h1>
                <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">Panel en tiempo real de todos tus
                    clientes</p>
            </div>
            <div v-if="tab === 'vivo'" class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span class="text-[12px] text-gray-400 dark:text-gray-500 font-medium hidden sm:inline">En vivo</span>
                <button @click="store.fetchAll()" :disabled="store.loading" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                 bg-white dark:bg-gray-900 text-[12px] font-semibold text-gray-600 dark:text-gray-300 cursor-pointer
                 hover:border-brand-primary hover:text-brand-primary
                 transition-all duration-150 disabled:opacity-50">
                    <ArrowPathIcon class="w-3.5 h-3.5" :class="store.loading ? 'animate-spin' : ''" />
                    Actualizar
                </button>
            </div>
        </div>

        <!-- ══ TABS ══ -->
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl self-start">
            <button @click="tab = 'vivo'" class="px-4 py-2 rounded-lg text-[12.5px] font-bold cursor-pointer
               border-none transition-all duration-150" :class="tab === 'vivo'
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm'
                : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                En vivo
            </button>
            <button @click="tab = 'historial'" class="px-4 py-2 rounded-lg text-[12.5px] font-bold cursor-pointer
               border-none transition-all duration-150" :class="tab === 'historial'
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm'
                : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                Historial completo
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════
             TAB: EN VIVO
             ══════════════════════════════════════════════════════ -->
        <template v-if="tab === 'vivo'">

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Activos</p>
                    <p class="font-black text-[24px] text-brand-primary leading-none m-0">{{
                        store.stats.total_activos }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Entregados hoy</p>
                    <p class="font-black text-[24px] text-green-600 dark:text-green-400 leading-none m-0">{{
                        store.stats.entregados_hoy }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Mot.
                        ocupados</p>
                    <p class="font-black text-[24px] text-amber-600 dark:text-amber-400 leading-none m-0">{{
                        store.stats.motorizados_ocupados }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Mot.
                        disponibles</p>
                    <p class="font-black text-[24px] text-blue-600 dark:text-blue-400 leading-none m-0">{{
                        store.stats.motorizados_disponibles
                    }}</p>
                </div>
            </div>

            <!-- Filtro por negocio -->
            <select v-model="negocioFilter" @change="store.fetchAll(negocioFilter || undefined)" class="self-start px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
             bg-white dark:bg-gray-900 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
             focus:border-brand-primary transition-all duration-200 font-semibold">
                <option :value="undefined">Todos los negocios</option>
                <option v-for="r in negocios.negocios" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>

            <!-- Activos -->
            <div>
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 mb-3">
                    En curso
                    <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-blue-50 text-brand-primary border border-blue-200
                     dark:bg-blue-500/10 dark:border-blue-500/20">
                        {{ store.activos.length }}
                    </span>
                </h2>

                <div v-if="store.loading" class="flex flex-col gap-3">
                    <div v-for="n in 3" :key="n" class="h-32 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                </div>

                <div v-else-if="store.activos.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex flex-col items-center py-12 gap-3 text-gray-400 dark:text-gray-600">
                    <TruckIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
                    <p class="font-bold text-gray-600 dark:text-gray-400 text-[14px] m-0">Sin despachos activos</p>
                </div>

                <div v-else class="flex flex-col gap-3">
                    <TransitionGroup name="despacho">
                        <div v-for="d in store.activos" :key="d.id" class="bg-white dark:bg-gray-900 rounded-2xl border-2 border-gray-100 dark:border-gray-800
                   shadow-sm dark:shadow-none overflow-hidden
                   hover:border-blue-200 dark:hover:border-blue-500/30 transition-all duration-150">
                            <div class="p-4">

                                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-black px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800
                               text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-mono">
                                            #{{ d.order_id }}
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                               bg-purple-50 text-purple-700 border border-purple-200
                               dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20">
                                            {{ d.negocio }}
                                        </span>
                                        <span :class="despachoCls(d.estado)"
                                            class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border">
                                            {{ despachoLabel(d.estado) }}
                                        </span>
                                    </div>
                                    <span class="text-[11.5px] text-gray-400 dark:text-gray-500">{{
                                        formatFecha(d.solicitado_at) }}</span>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <p
                                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                            Cliente</p>
                                        <p class="font-bold text-[14px] text-gray-900 dark:text-gray-100 m-0">{{
                                            d.order?.client_name }}</p>
                                        <p
                                            class="text-[12px] text-gray-500 dark:text-gray-400 m-0 mt-0.5 flex items-center gap-1">
                                            <MapPinIcon class="w-3.5 h-3.5 shrink-0" />
                                            {{ d.order?.address }}, {{ d.order?.district }}
                                        </p>
                                        <div class="flex items-baseline gap-0.5 mt-1.5">
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                                            <span class="font-black text-[16px] text-brand-primary leading-none">
                                                {{ d.order?.total.toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                            Motorizado</p>
                                        <div v-if="d.motorizado" class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                text-[13px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                                                {{ d.motorizado.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[13px] text-gray-900 dark:text-gray-100 m-0">
                                                    {{
                                                        d.motorizado.nombre }}
                                                </p>
                                                <a :href="`https://wa.me/51${d.motorizado.telefono.replace(/\D/g, '')}`"
                                                    target="_blank"
                                                    class="text-[11.5px] text-green-600 dark:text-green-400 no-underline hover:underline font-medium">
                                                    {{ d.motorizado.telefono }}
                                                </a>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                            <ClockIcon class="w-4 h-4 shrink-0" />
                                            <span class="text-[12.5px] font-medium">Esperando motorizado...</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <button @click="askCancel(d)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold
                         border cursor-pointer border-red-200 text-red-500 bg-red-50
                         hover:bg-red-100 dark:border-red-500/20 dark:text-red-400 dark:bg-red-500/10 dark:hover:bg-red-500/20
                         transition-all duration-150">
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
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 mb-3">
                    Entregados hoy
                    <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-green-50 text-green-700 border border-green-200
                     dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                        {{ store.entregadosHoy.length }}
                    </span>
                </h2>

                <div v-if="store.entregadosHoy.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex items-center justify-center py-10 text-gray-400 dark:text-gray-600 text-[13px]">
                    Sin entregas completadas hoy
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="d in store.entregadosHoy" :key="d.id" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none p-4
                 flex items-center gap-4 transition-colors duration-200">
                        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20
                      flex items-center justify-center shrink-0">
                            <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-mono font-black text-gray-500 dark:text-gray-400">#{{
                                    d.order_id }}</span>
                                <span class="font-bold text-[13px] text-gray-900 dark:text-gray-100 truncate">{{
                                    d.order?.client_name }}</span>
                            </div>
                            <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">
                                {{ d.negocio }} · {{ d.motorizado?.nombre ?? '—' }}
                            </p>
                        </div>
                        <div class="flex items-baseline gap-0.5 shrink-0">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                            <span class="font-black text-[16px] text-green-600 dark:text-green-400 leading-none">
                                {{ d.order?.total.toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ══════════════════════════════════════════════════════
             TAB: HISTORIAL COMPLETO
             ══════════════════════════════════════════════════════ -->
        <template v-else>

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 flex flex-col gap-3 transition-colors duration-200">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <MagnifyingGlassIcon
                            class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                        <input v-model="filtros.buscar" placeholder="Buscar por # de pedido o cliente..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                           focus:border-brand-primary transition-all duration-150" />
                    </div>

                    <select v-model="filtros.estado" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option value="">Todos los estados</option>
                        <option value="solicitado">Buscando motorizado</option>
                        <option value="aceptado">Asignado</option>
                        <option value="recogido">Recogido</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>

                    <select v-model="filtros.negocio_id" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option :value="undefined">Todos los negocios</option>
                        <option v-for="r in negocios.negocios" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>

                    <select v-model="filtros.motorizado_id" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option :value="undefined">Todos los motorizados</option>
                        <option v-for="m in motorizados.motorizados" :key="m.id" :value="m.id">
                            {{ m.nombre }}{{ m.placa ? ` · ${m.placa}` : '' }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input v-model="filtros.desde" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                       outline-none focus:border-brand-primary transition-all duration-150" />
                    <span class="text-gray-400 dark:text-gray-500 text-[12px]">a</span>
                    <input v-model="filtros.hasta" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                       outline-none focus:border-brand-primary transition-all duration-150" />
                    <button @click="limpiarFiltros" class="px-3.5 py-2 rounded-xl text-[12.5px] font-bold cursor-pointer
                       border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                       text-gray-600 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600
                       transition-all duration-150">
                        Limpiar filtros
                    </button>
                    <ExportButtons endpoint="/admin/reportes/despachos" :params="{
                        buscar: filtros.buscar || undefined,
                        estado: filtros.estado || undefined,
                        negocio_id: filtros.negocio_id,
                        motorizado_id: filtros.motorizado_id,
                        desde: filtros.desde || undefined,
                        hasta: filtros.hasta || undefined,
                    }" filename="despachos" />
                </div>
            </div>

            <!-- Skeleton -->
            <div v-if="store.historialLoading" class="flex flex-col gap-2">
                <div v-for="n in 6" :key="n" class="h-16 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
            </div>

            <!-- Empty -->
            <div v-else-if="store.historial.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex flex-col items-center py-16 gap-3 text-gray-400 dark:text-gray-600">
                <ClipboardDocumentListIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
                <p class="font-bold text-gray-600 dark:text-gray-400 text-[14px] m-0">Sin resultados con estos
                    filtros</p>
            </div>

            <!-- Tabla -->
            <div v-else class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none overflow-hidden transition-colors duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Pedido</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Negocio</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Cliente</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Motorizado</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Estado</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-right">
                                    Total</th>
                                <th @click="toggleSort" class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest
                                   text-gray-400 dark:text-gray-500 cursor-pointer select-none whitespace-nowrap
                                   hover:text-gray-600 dark:hover:text-gray-300">
                                    Fecha
                                    <component :is="filtros.sort_dir === 'asc' ? ChevronUpIcon : ChevronDownIcon"
                                        class="w-3 h-3 inline-block ml-0.5" />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in store.historial" :key="d.id"
                                class="border-b border-gray-50 dark:border-gray-800/60 last:border-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                <td
                                    class="px-4 py-3 text-[12.5px] font-mono font-bold text-gray-700 dark:text-gray-300">
                                    #{{ d.order_id }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-600 dark:text-gray-400">{{ d.negocio ??
                                    '—' }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-900 dark:text-gray-100 font-medium">{{
                                    d.order?.client_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-600 dark:text-gray-400">{{
                                    d.motorizado?.nombre ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="despachoCls(d.estado)"
                                        class="text-[10.5px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap">
                                        {{ despachoLabel(d.estado) }}
                                    </span>
                                    <span v-if="d.estado === 'cancelado' && d.motivo_cancelacion"
                                        class="block text-[10.5px] text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ d.motivo_cancelacion }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 text-[12.5px] font-bold text-gray-900 dark:text-gray-100 text-right whitespace-nowrap">
                                    S/ {{ d.order?.total?.toFixed(2) ?? '0.00' }}
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                    {{ formatFecha(d.solicitado_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :meta="store.historialMeta" @change="cambiarPaginaHistorial" />
                </div>
            </div>
        </template>

        <CancelarDespachoModal v-model="confirmCancel.show"
            :message="`El despacho del pedido #${confirmCancel.target?.order_id} de ${confirmCancel.target?.negocio} será cancelado y el motorizado quedará disponible nuevamente.`"
            :loading="confirmCancel.loading" @confirm="executeCancel" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import {
    TruckIcon, MapPinIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon,
    MagnifyingGlassIcon, ClipboardDocumentListIcon, ChevronUpIcon, ChevronDownIcon,
} from '@heroicons/vue/24/outline'
import { useDespachosStore, type DespachoItem } from '../stores/despachos'
import { useNegociosStore } from '../stores/negocios'
import { useMotorizadosStore } from '../stores/motorizados'
import { useEcho } from '../composables/useHecho.ts'
import { useToastStore } from '../stores/toast'
import CancelarDespachoModal from '../components/CancelarDespachoModal.vue'
import Pagination from '../components/Pagination.vue'
import ExportButtons from '../components/ExportButtons.vue'

const store = useDespachosStore()
const negocios = useNegociosStore()
const motorizados = useMotorizadosStore()
const toast = useToastStore()
const negocioFilter = ref<number | undefined>(undefined)

// ── Tabs ───────────────────────────────────────────────────
const tab = ref<'vivo' | 'historial'>('vivo')

// ── Historial: filtros + paginación ───────────────────────
const filtros = reactive({
    buscar: '',
    estado: '',
    negocio_id: undefined as number | undefined,
    motorizado_id: undefined as number | undefined,
    desde: '',
    hasta: '',
    sort_dir: 'desc' as 'asc' | 'desc',
    page: 1,
})
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function cargarHistorial() {
    store.fetchHistorial({
        buscar: filtros.buscar || undefined,
        estado: filtros.estado || undefined,
        negocio_id: filtros.negocio_id,
        motorizado_id: filtros.motorizado_id,
        desde: filtros.desde || undefined,
        hasta: filtros.hasta || undefined,
        sort_dir: filtros.sort_dir,
        page: filtros.page,
    })
}

function cambiarPaginaHistorial(p: number) {
    filtros.page = p
    cargarHistorial()
}

function toggleSort() {
    filtros.sort_dir = filtros.sort_dir === 'asc' ? 'desc' : 'asc'
    filtros.page = 1
    cargarHistorial()
}

function limpiarFiltros() {
    Object.assign(filtros, {
        buscar: '', estado: '', negocio_id: undefined, motorizado_id: undefined,
        desde: '', hasta: '', sort_dir: 'desc', page: 1,
    })
    cargarHistorial()
}

watch(() => filtros.buscar, () => {
    filtros.page = 1
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(cargarHistorial, 350)
})

watch(() => [filtros.estado, filtros.negocio_id, filtros.motorizado_id, filtros.desde, filtros.hasta], () => {
    filtros.page = 1
    cargarHistorial()
})

watch(tab, (t) => {
    if (t === 'historial' && store.historial.length === 0) cargarHistorial()
    // Necesitamos el listado de motorizados cargado para el <select> del filtro
    if (t === 'historial' && motorizados.motorizados.length === 0) motorizados.fetchAll()
})

let echo: any = null

onMounted(async () => {
    await Promise.all([store.fetchAll(), negocios.fetchAll()])
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
        solicitado: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        aceptado: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
        recogido: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20',
        entregado: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20',
        cancelado: 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
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

async function executeCancel(motivo: string) {
    if (!confirmCancel.target) return
    confirmCancel.loading = true
    const ok = await store.cancelar(confirmCancel.target.id, motivo)
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