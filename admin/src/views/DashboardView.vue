<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div>
            <h1 class="font-black text-[24px] sm:text-[28px] text-gray-900 m-0 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Dashboard
            </h1>
            <p class="text-[13.5px] text-gray-400 mt-1.5 m-0">
                Resumen general de tu plataforma de delivery
            </p>
        </div>

        <!-- Stats principales -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                    <BuildingStorefrontIcon class="w-4.5 h-4.5 text-blue-600" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-gray-900 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ restaurantsActivos }}
                </p>
                <p class="text-[12px] text-gray-400 mt-1 m-0">Restaurantes activos</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center mb-3">
                    <UserGroupIcon class="w-4.5 h-4.5 text-green-600" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-gray-900 leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ motorizadosDisponibles }}
                </p>
                <p class="text-[12px] text-gray-400 mt-1 m-0">Motorizados disponibles</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
                    <ClipboardDocumentListIcon class="w-4.5 h-4.5 text-amber-600" />
                </div>
                <p class="font-black text-[24px] sm:text-[28px] text-brand-primary leading-none m-0"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ despachosStore.stats.total_activos }}
                </p>
                <p class="text-[12px] text-gray-400 mt-1 m-0">Despachos en curso</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center mb-3">
                    <BanknotesIcon class="w-4.5 h-4.5 text-purple-600" />
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-[14px] text-gray-400 font-bold">S/</span>
                    <p class="font-black text-[24px] sm:text-[28px] text-purple-600 leading-none m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ deudaTotal.toFixed(2) }}
                    </p>
                </div>
                <p class="text-[12px] text-gray-400 mt-1 m-0">Deuda pendiente total</p>
            </div>
        </div>

        <!-- Grid contenido -->
        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">

            <!-- Despachos activos -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-black text-[15px] text-gray-900 m-0">
                        Despachos en curso
                    </h2>
                    <RouterLink to="/despachos" class="text-[12px] font-bold text-brand-primary no-underline
                   hover:underline">
                        Ver todos →
                    </RouterLink>
                </div>

                <div v-if="despachosStore.loading" class="flex flex-col gap-2">
                    <div v-for="n in 3" :key="n" class="h-14 rounded-xl bg-gray-100 animate-pulse" />
                </div>

                <div v-else-if="despachosStore.activos.length === 0"
                    class="flex flex-col items-center py-8 text-gray-400 gap-2">
                    <TruckIcon class="w-8 h-8 text-gray-300" />
                    <p class="text-[13px] m-0">Sin despachos activos</p>
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="d in despachosStore.activos.slice(0, 5)" :key="d.id"
                        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/60">
                        <div class="w-9 h-9 rounded-lg bg-white border border-gray-200
                        flex items-center justify-center shrink-0">
                            <span class="text-[11px] font-black text-gray-500">#{{ d.order_id }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[12.5px] text-gray-900 m-0 truncate">
                                {{ d.order?.client_name ?? 'Cliente' }}
                            </p>
                            <p class="text-[11px] text-gray-400 m-0 truncate">
                                {{ d.restaurant }}
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
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-black text-[15px] text-gray-900 m-0">
                        Mayores deudas pendientes
                    </h2>
                    <RouterLink to="/comisiones" class="text-[12px] font-bold text-brand-primary no-underline
                   hover:underline">
                        Ver todas →
                    </RouterLink>
                </div>

                <div v-if="comisiones.loading" class="flex flex-col gap-2">
                    <div v-for="n in 3" :key="n" class="h-14 rounded-xl bg-gray-100 animate-pulse" />
                </div>

                <div v-else-if="topDeudores.length === 0" class="flex flex-col items-center py-8 text-gray-400 gap-2">
                    <BanknotesIcon class="w-8 h-8 text-gray-300" />
                    <p class="text-[13px] m-0">Sin deudas pendientes</p>
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="m in topDeudores" :key="m.id"
                        class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/60">
                        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center
                        justify-center text-[12px] font-black text-purple-600 shrink-0">
                            {{ m.nombre.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[12.5px] text-gray-900 m-0 truncate">
                                {{ m.nombre }}
                            </p>
                            <p class="text-[11px] text-gray-400 m-0">{{ m.telefono }}</p>
                        </div>
                        <div class="flex items-baseline gap-0.5 shrink-0">
                            <span class="text-[10px] text-gray-400">S/</span>
                            <span class="font-black text-[14px] text-purple-600">
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
import { useRestaurantsStore } from '../stores/restaurants'
import { useMotorizadosStore } from '../stores/motorizados'

const despachosStore = useDespachosStore()
const comisiones = useComisionesStore()
const restaurants = useRestaurantsStore()
const motorizados = useMotorizadosStore()

const restaurantsActivos = computed(() =>
    restaurants.restaurants.filter(r => r.activo).length
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
    restaurants.fetchAll()
    motorizados.fetchAll()
    comisiones.fetchResumen()
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
        solicitado: 'bg-amber-50 text-amber-700',
        aceptado: 'bg-blue-50 text-blue-700',
        recogido: 'bg-orange-50 text-orange-700',
        entregado: 'bg-green-50 text-green-700',
        cancelado: 'bg-gray-100 text-gray-500',
    }
    return m[s] ?? 'bg-gray-100 text-gray-500'
}
</script>