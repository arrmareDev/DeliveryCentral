<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Motorizados
                </h1>
                <p class="text-[13px] text-gray-400 mt-1 m-0">
                    {{ store.motorizados.length }} registrado{{ store.motorizados.length !== 1 ? 's' : '' }}
                </p>
            </div>
            <select v-model="filtro" class="px-3.5 py-2.5 rounded-xl border border-gray-200 bg-white
               text-[13px] text-gray-700 outline-none cursor-pointer
               focus:border-brand-primary transition-all duration-200 font-semibold">
                <option value="">Todos</option>
                <option value="pendiente">Pendientes de verificar</option>
                <option value="verificado">Verificados activos</option>
                <option value="inactivo">Verificados inactivos</option>
            </select>
        </div>

        <!-- Stats resumen -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total</p>
                <p class="font-black text-[24px] text-gray-900 leading-none m-0">{{ store.motorizados.length }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Verificados</p>
                <p class="font-black text-[24px] text-green-600 leading-none m-0">{{ verificadosCount }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Disponibles</p>
                <p class="font-black text-[24px] text-blue-600 leading-none m-0">{{ disponiblesCount }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Pendientes</p>
                <p class="font-black text-[24px] text-amber-600 leading-none m-0">{{ pendientesCount }}</p>
            </div>
        </div>

        <!-- Skeleton -->
        <div v-if="store.loading" class="flex flex-col gap-3">
            <div v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 animate-pulse" />
        </div>

        <!-- Empty -->
        <div v-else-if="filtered.length === 0" class="flex flex-col items-center py-20 text-gray-400 gap-4">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
                <UserGroupIcon class="w-10 h-10 text-gray-300" />
            </div>
            <p class="font-bold text-gray-600 text-[15px] m-0">
                Sin motorizados {{ filtro ? 'con este filtro' : 'registrados' }}
            </p>
        </div>

        <!-- Lista -->
        <div v-else class="flex flex-col gap-3">
            <div v-for="m in filtered" :key="m.id"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                    <!-- Avatar + info -->
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 shrink-0 flex items-center
                        justify-center overflow-hidden border border-gray-200">
                            <img v-if="m.foto" :src="m.foto" class="w-full h-full object-cover" />
                            <span v-else class="text-[18px] font-black text-gray-400">
                                {{ m.nombre.charAt(0).toUpperCase() }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <p class="font-black text-[15px] text-gray-900 m-0">{{ m.nombre }}</p>
                                <span v-if="m.verificado" class="text-[10px] font-bold px-2 py-0.5 rounded-full
                         bg-green-50 text-green-700 border border-green-200">
                                    ✓ Verificado
                                </span>
                                <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded-full
                         bg-amber-50 text-amber-700 border border-amber-200">
                                    ⏳ Pendiente
                                </span>
                                <span :class="estadoCls(m.estado)"
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border">
                                    {{ estadoLabel(m.estado) }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-0.5">
                                <p class="text-[12.5px] text-gray-500 m-0 flex items-center gap-1.5">
                                    <PhoneIcon class="w-3.5 h-3.5 shrink-0" />{{ m.telefono }}
                                </p>
                                <p class="text-[12.5px] text-gray-500 m-0 flex items-center gap-1.5">
                                    <EnvelopeIcon class="w-3.5 h-3.5 shrink-0" />{{ m.email }}
                                </p>
                            </div>

                            <!-- Stats -->
                            <div v-if="m.stats" class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                                <div>
                                    <p class="font-black text-[15px] text-gray-900 m-0 leading-none">{{
                                        m.stats.total_entregas }}</p>
                                    <p class="text-[10px] text-gray-400 m-0 mt-0.5">Entregas</p>
                                </div>
                                <div>
                                    <p class="font-black text-[15px] text-gray-900 m-0 leading-none">{{
                                        m.stats.entregas_hoy }}</p>
                                    <p class="text-[10px] text-gray-400 m-0 mt-0.5">Hoy</p>
                                </div>
                                <div>
                                    <div class="flex items-baseline gap-0.5">
                                        <span class="text-[10px] text-gray-400">S/</span>
                                        <p class="font-black text-[15px] text-purple-600 m-0 leading-none">
                                            {{ m.stats.deuda_pendiente.toFixed(2) }}
                                        </p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 m-0 mt-0.5">Te debe</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex sm:flex-col gap-2 shrink-0">
                        <button @click="askToggleVerificado(m)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     transition-all duration-150" :class="m.verificado
                        ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100'
                        : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'">
                            <CheckCircleIcon class="w-3.5 h-3.5" />
                            {{ m.verificado ? 'Quitar verif.' : 'Verificar' }}
                        </button>

                        <button v-if="m.verificado" @click="askToggleActivo(m)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     transition-all duration-150" :class="m.activo
                        ? 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100'
                        : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100'">
                            <component :is="m.activo ? NoSymbolIcon : CheckIcon" class="w-3.5 h-3.5" />
                            {{ m.activo ? 'Desactivar' : 'Activar' }}
                        </button>

                        <a :href="`https://wa.me/51${m.telefono.replace(/\D/g, '')}`" target="_blank" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     bg-[#25D366]/10 border-[#25D366]/30 text-[#128C7E]
                     hover:bg-[#25D366]/20 transition-all duration-150 no-underline">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmaciones -->
        <ConfirmModal v-model="confirmVerif.show"
            :title="confirmVerif.target?.verificado ? '¿Quitar verificación?' : '¿Verificar motorizado?'" :message="confirmVerif.target?.verificado
                ? `${confirmVerif.target?.nombre} no podrá recibir pedidos hasta que sea verificado de nuevo.`
                : `${confirmVerif.target?.nombre} podrá ponerse disponible y recibir pedidos.`"
            :variant="confirmVerif.target?.verificado ? 'warning' : 'success'"
            :confirm-label="confirmVerif.target?.verificado ? 'Sí, quitar' : 'Sí, verificar'"
            :loading="confirmVerif.loading" @confirm="executeToggleVerificado" />

        <ConfirmModal v-model="confirmActivo.show"
            :title="confirmActivo.target?.activo ? '¿Desactivar motorizado?' : '¿Activar motorizado?'" :message="confirmActivo.target?.activo
                ? `${confirmActivo.target?.nombre} no podrá recibir pedidos mientras esté desactivado.`
                : `${confirmActivo.target?.nombre} podrá volver a recibir pedidos.`"
            :variant="confirmActivo.target?.activo ? 'warning' : 'success'"
            :confirm-label="confirmActivo.target?.activo ? 'Sí, desactivar' : 'Sí, activar'"
            :loading="confirmActivo.loading" @confirm="executeToggleActivo" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import {
    UserGroupIcon, PhoneIcon, EnvelopeIcon, CheckCircleIcon, CheckIcon, NoSymbolIcon,
} from '@heroicons/vue/24/outline'
import { useMotorizadosStore, type MotorizadoItem } from '../stores/motorizados'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToastStore } from '../stores/toast'

const store = useMotorizadosStore()
const toast = useToastStore()
const filtro = ref('')

onMounted(() => store.fetchAll())

const verificadosCount = computed(() => store.motorizados.filter(m => m.verificado).length)
const disponiblesCount = computed(() => store.motorizados.filter(m => m.estado === 'disponible').length)
const pendientesCount = computed(() => store.motorizados.filter(m => !m.verificado).length)

const filtered = computed(() => {
    if (filtro.value === 'pendiente') return store.motorizados.filter(m => !m.verificado)
    if (filtro.value === 'verificado') return store.motorizados.filter(m => m.verificado && m.activo)
    if (filtro.value === 'inactivo') return store.motorizados.filter(m => m.verificado && !m.activo)
    return store.motorizados
})

function estadoLabel(s: string): string {
    const m: Record<string, string> = { disponible: 'Disponible', ocupado: 'Ocupado', inactivo: 'Inactivo' }
    return m[s] ?? s
}

function estadoCls(s: string): string {
    const m: Record<string, string> = {
        disponible: 'bg-green-50 text-green-700 border-green-200',
        ocupado: 'bg-amber-50 text-amber-700 border-amber-200',
        inactivo: 'bg-gray-100 text-gray-500 border-gray-200',
    }
    return m[s] ?? m.inactivo
}

// ── Confirmar verificar ────────────────────────────────────
const confirmVerif = reactive({ show: false, loading: false, target: null as MotorizadoItem | null })

function askToggleVerificado(m: MotorizadoItem) {
    confirmVerif.target = m
    confirmVerif.show = true
}

async function executeToggleVerificado() {
    if (!confirmVerif.target) return
    confirmVerif.loading = true
    const ok = await store.toggleVerificado(confirmVerif.target.id)
    confirmVerif.loading = false
    confirmVerif.show = false
    ok ? toast.success('Estado de verificación actualizado') : toast.error('Error al actualizar')
}

// ── Confirmar activar/desactivar ──────────────────────────
const confirmActivo = reactive({ show: false, loading: false, target: null as MotorizadoItem | null })

function askToggleActivo(m: MotorizadoItem) {
    confirmActivo.target = m
    confirmActivo.show = true
}

async function executeToggleActivo() {
    if (!confirmActivo.target) return
    confirmActivo.loading = true
    const ok = await store.toggleActivo(confirmActivo.target.id)
    confirmActivo.loading = false
    confirmActivo.show = false
    ok ? toast.success('Estado actualizado') : toast.error('Error al actualizar')
}
</script>