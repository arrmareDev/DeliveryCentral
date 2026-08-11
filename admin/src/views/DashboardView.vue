<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div>
            <h1 class="font-black text-[24px] sm:text-[28px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Dashboard
            </h1>
            <p class="text-[13.5px] text-gray-400 dark:text-gray-500 mt-1.5 m-0">
                Resumen general de tu plataforma de delivery
            </p>
        </div>

        <!-- Stats principales -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 sm:p-5 transition-colors duration-200">
                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center mb-3">
                    <BuildingStorefrontIcon class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-gray-900 dark:text-gray-100 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ negociosActivos }}
                </p>
                <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1 m-0">Negocios activos</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 sm:p-5 transition-colors duration-200">
                <div class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center mb-3">
                    <UserGroupIcon class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-gray-900 dark:text-gray-100 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ motorizadosDisponibles }}
                </p>
                <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1 m-0">Motorizados disponibles</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 sm:p-5 transition-colors duration-200">
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center mb-3">
                    <ClipboardDocumentListIcon class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-brand-primary leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ despachosStore.stats.total_activos }}
                </p>
                <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1 m-0">Despachos en curso</p>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 sm:p-5 transition-colors duration-200">
                <div
                    class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center mb-3">
                    <BanknotesIcon class="w-4.5 h-4.5 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-[14px] text-gray-400 dark:text-gray-500 font-bold">S/</span>
                    <p class="font-black text-[24px] sm:text-[28px] text-purple-600 dark:text-purple-400 leading-none m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ deudaTotal.toFixed(2) }}
                    </p>
                </div>
                <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1 m-0">Deuda pendiente total</p>
            </div>
        </div>

        <!-- ══ GRÁFICAS ══ -->
        <div class="grid lg:grid-cols-3 gap-4 sm:gap-6">

            <!-- Despachos últimos 30 días -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 mb-4">
                    Despachos — últimos 30 días
                </h2>
                <apexchart v-if="!analytics.loading" type="area" height="260" :options="chartDespachosOptions"
                    :series="chartDespachosSeries" />
                <div v-else class="h-[260px] rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
            </div>

            <!-- Métodos de pago -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 mb-4">
                    Métodos de pago
                </h2>
                <apexchart v-if="!analytics.loading && analytics.metodosPago.length > 0" type="donut" height="260"
                    :options="chartMetodosOptions" :series="chartMetodosSeries" />
                <div v-else-if="analytics.loading"
                    class="h-[260px] rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                <div v-else
                    class="h-[260px] flex items-center justify-center text-gray-400 dark:text-gray-600 text-[13px]">
                    Sin datos todavía
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">

            <!-- Comparativa mensual -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 mb-4">
                    Este mes vs. mes anterior
                </h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p
                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                            Despachos entregados
                        </p>
                        <div class="flex items-baseline gap-2">
                            <span class="font-black text-[22px] text-gray-900 dark:text-gray-100">
                                {{ analytics.comparativa.mes_actual.despachos }}
                            </span>
                            <span class="text-[12px] font-bold"
                                :class="crecimientoDespachos >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'">
                                {{ crecimientoDespachos >= 0 ? '+' : '' }}{{ crecimientoDespachos }}%
                            </span>
                        </div>
                    </div>
                    <div>
                        <p
                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                            Comisiones generadas
                        </p>
                        <div class="flex items-baseline gap-2">
                            <span class="font-black text-[22px] text-purple-600 dark:text-purple-400">
                                S/ {{ analytics.comparativa.mes_actual.comisiones.toFixed(0) }}
                            </span>
                            <span class="text-[12px] font-bold"
                                :class="crecimientoComisiones >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'">
                                {{ crecimientoComisiones >= 0 ? '+' : '' }}{{ crecimientoComisiones }}%
                            </span>
                        </div>
                    </div>
                </div>
                <apexchart v-if="!analytics.loading" type="bar" height="180" :options="chartComparativaOptions"
                    :series="chartComparativaSeries" />
            </div>

            <!-- Top motorizados -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 mb-4">
                    Top 5 motorizados por comisiones
                </h2>
                <apexchart v-if="!analytics.loading && analytics.topMotorizados.length > 0" type="bar" height="220"
                    :options="chartTopMotorizadosOptions" :series="chartTopMotorizadosSeries" />
                <div v-else-if="analytics.loading"
                    class="h-[220px] rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                <div v-else
                    class="h-[220px] flex items-center justify-center text-gray-400 dark:text-gray-600 text-[13px]">
                    Sin comisiones registradas
                </div>
            </div>
        </div>

        <!-- Grid contenido -->
        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">

            <!-- Despachos activos -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0">
                        Despachos en curso
                    </h2>
                    <RouterLink to="/despachos" class="text-[12px] font-bold text-brand-primary no-underline
                   hover:underline">
                        Ver todos →
                    </RouterLink>
                </div>

                <div v-if="despachosStore.loading" class="flex flex-col gap-2">
                    <div v-for="n in 3" :key="n" class="h-14 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                </div>

                <div v-else-if="despachosStore.activos.length === 0"
                    class="flex flex-col items-center py-8 text-gray-400 dark:text-gray-600 gap-2">
                    <TruckIcon class="w-8 h-8 text-gray-300 dark:text-gray-700" />
                    <p class="text-[13px] m-0">Sin despachos activos</p>
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="d in despachosStore.activos.slice(0, 5)" :key="d.id"
                        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/60 dark:bg-gray-800/60">
                        <div class="w-9 h-9 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700
                        flex items-center justify-center shrink-0">
                            <span class="text-[11px] font-black text-gray-500 dark:text-gray-400">#{{ d.order_id
                            }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[12.5px] text-gray-900 dark:text-gray-100 m-0 truncate">
                                {{ d.order?.client_name ?? 'Cliente' }}
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 m-0 truncate">
                                {{ d.negocio }}
                            </p>
                        </div>
                        <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full shrink-0"
                            :class="estadoBadge(d.estado)">
                            {{ estadoLabel(d.estado) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top deudores -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-5 transition-colors duration-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0">
                        Mayores deudas pendientes
                    </h2>
                    <RouterLink to="/comisiones" class="text-[12px] font-bold text-brand-primary no-underline
                   hover:underline">
                        Ver todas →
                    </RouterLink>
                </div>

                <div v-if="comisiones.loading" class="flex flex-col gap-2">
                    <div v-for="n in 3" :key="n" class="h-14 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                </div>

                <div v-else-if="topDeudores.length === 0"
                    class="flex flex-col items-center py-8 text-gray-400 dark:text-gray-600 gap-2">
                    <BanknotesIcon class="w-8 h-8 text-gray-300 dark:text-gray-700" />
                    <p class="text-[13px] m-0">Sin deudas pendientes</p>
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="m in topDeudores" :key="m.id"
                        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/60 dark:bg-gray-800/60">
                        <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-500/15 flex items-center
                        justify-center text-[12px] font-black text-purple-600 dark:text-purple-400 shrink-0">
                            {{ m.nombre.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[12.5px] text-gray-900 dark:text-gray-100 m-0 truncate">
                                {{ m.nombre }}
                            </p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 m-0">{{ m.telefono }}</p>
                        </div>
                        <div class="flex items-baseline gap-0.5 shrink-0">
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">S/</span>
                            <span class="font-black text-[14px] text-purple-600 dark:text-purple-400">
                                {{ m.deuda_pendiente.toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
    BuildingStorefrontIcon, UserGroupIcon, ClipboardDocumentListIcon,
    BanknotesIcon, TruckIcon,
} from '@heroicons/vue/24/outline'
import { useDespachosStore } from '../stores/despachos'
import { useComisionesStore } from '../stores/comisiones'
import { useNegociosStore } from '../stores/negocios'
import { useMotorizadosStore } from '../stores/motorizados'
import { useAnalyticsStore } from '../stores/analytics'
import { useThemeStore } from '../stores/theme'

const despachosStore = useDespachosStore()
const comisiones = useComisionesStore()
const negocios = useNegociosStore()
const motorizados = useMotorizadosStore()
const analytics = useAnalyticsStore()
const theme = useThemeStore()

const negociosActivos = computed(() =>
    negocios.negocios.filter(r => r.activo).length
)

const motorizadosDisponibles = computed(() =>
    motorizados.motorizados.filter(m => m.estado === 'disponible').length
)

const deudaTotal = computed(() =>
    comisiones.resumen.reduce((sum, m) => sum + m.deuda_pendiente, 0)
)

const topDeudores = computed(() =>
    [...comisiones.resumen]
        .filter(m => m.deuda_pendiente > 0)
        .sort((a, b) => b.deuda_pendiente - a.deuda_pendiente)
        .slice(0, 5)
)

onMounted(() => {
    negocios.fetchAll()
    motorizados.fetchAll()
    comisiones.fetchResumen()
    analytics.fetchDashboard()
})

function estadoLabel(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'Buscando', aceptado: 'Asignado',
        recogido: 'En camino', entregado: 'Entregado', cancelado: 'Cancelado',
    }
    return m[s] ?? s
}

function estadoBadge(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        aceptado: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        recogido: 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
        entregado: 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
        cancelado: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    }
    return m[s] ?? 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
}

// ── Gráfica: despachos últimos 30 días ────────────────────
const chartDespachosSeries = computed(() => [
    { name: 'Total', data: analytics.despachosPorDia.map(d => d.total) },
    { name: 'Entregados', data: analytics.despachosPorDia.map(d => d.entregados) },
    { name: 'Cancelados', data: analytics.despachosPorDia.map(d => d.cancelados) },
])

const chartDespachosOptions = computed(() => ({
    chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif', background: 'transparent' },
    theme: { mode: theme.isDark ? 'dark' : 'light' },
    colors: ['#2563eb', '#16a34a', '#9ca3af'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
    grid: { borderColor: theme.isDark ? '#1f2937' : '#f3f4f6', strokeDashArray: 4 },
    xaxis: {
        categories: analytics.despachosPorDia.map(d =>
            new Date(d.fecha).toLocaleDateString('es-PE', { day: '2-digit', month: 'short' })
        ),
        labels: { style: { fontSize: '10.5px' } },
        tickAmount: 8,
    },
    yaxis: { labels: { style: { fontSize: '11px' } } },
    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' },
    tooltip: { theme: theme.isDark ? 'dark' : 'light' },
}))

// ── Gráfica: métodos de pago ───────────────────────────────
const chartMetodosSeries = computed(() => analytics.metodosPago.map(m => m.total))

const chartMetodosOptions = computed(() => ({
    chart: { fontFamily: 'Inter, sans-serif' },
    theme: { mode: theme.isDark ? 'dark' : 'light' },
    labels: analytics.metodosPago.map(m => m.metodo),
    colors: ['#2563eb', '#16a34a', '#f59e0b'],
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { style: { fontSize: '11px' } },
    tooltip: { theme: theme.isDark ? 'dark' : 'light' },
}))

// ── Gráfica: comparativa mensual ──────────────────────────
const crecimientoDespachos = computed(() => {
    const anterior = analytics.comparativa.mes_anterior.despachos
    const actual = analytics.comparativa.mes_actual.despachos
    if (anterior === 0) return actual > 0 ? 100 : 0
    return Math.round(((actual - anterior) / anterior) * 100)
})

const crecimientoComisiones = computed(() => {
    const anterior = analytics.comparativa.mes_anterior.comisiones
    const actual = analytics.comparativa.mes_actual.comisiones
    if (anterior === 0) return actual > 0 ? 100 : 0
    return Math.round(((actual - anterior) / anterior) * 100)
})

const chartComparativaSeries = computed(() => [
    { name: 'Despachos', data: [analytics.comparativa.mes_anterior.despachos, analytics.comparativa.mes_actual.despachos] },
])

const chartComparativaOptions = computed(() => ({
    chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    theme: { mode: theme.isDark ? 'dark' : 'light' },
    colors: ['#2563eb'],
    plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
    dataLabels: { enabled: false },
    xaxis: { categories: ['Mes anterior', 'Este mes'], labels: { style: { fontSize: '11px' } } },
    grid: { borderColor: theme.isDark ? '#1f2937' : '#f3f4f6', strokeDashArray: 4 },
    tooltip: { theme: theme.isDark ? 'dark' : 'light' },
}))

// ── Gráfica: top motorizados ──────────────────────────────
const chartTopMotorizadosSeries = computed(() => [
    { name: 'Comisiones', data: analytics.topMotorizados.map(m => m.total) },
])

const chartTopMotorizadosOptions = computed(() => ({
    chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    theme: { mode: theme.isDark ? 'dark' : 'light' },
    colors: ['#9333ea'],
    plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '55%' } },
    dataLabels: { enabled: false },
    xaxis: { categories: analytics.topMotorizados.map(m => m.nombre), labels: { style: { fontSize: '11px' } } },
    grid: { borderColor: theme.isDark ? '#1f2937' : '#f3f4f6', strokeDashArray: 4 },
    tooltip: { theme: theme.isDark ? 'dark' : 'light', y: { formatter: (v: number) => `S/ ${v.toFixed(2)}` } },
}))
</script>