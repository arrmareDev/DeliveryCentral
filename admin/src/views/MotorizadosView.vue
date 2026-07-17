<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Motorizados
                </h1>
                <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">
                    {{ store.stats.total }} registrado{{ store.stats.total !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <!-- Stats resumen -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Total
                </p>
                <p class="font-black text-[24px] text-gray-900 dark:text-gray-100 leading-none m-0">{{ store.stats.total
                }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                    Verificados</p>
                <p class="font-black text-[24px] text-green-600 dark:text-green-400 leading-none m-0">{{
                    store.stats.verificados }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                    Disponibles</p>
                <p class="font-black text-[24px] text-blue-600 dark:text-blue-400 leading-none m-0">{{
                    store.stats.disponibles }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                    Pendientes</p>
                <p class="font-black text-[24px] text-amber-600 dark:text-amber-400 leading-none m-0">{{
                    store.stats.pendientes }}</p>
            </div>
        </div>

        <!-- Búsqueda + filtro -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="relative flex-1 min-w-[220px]">
                <MagnifyingGlassIcon
                    class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                <input v-model="buscar" placeholder="Buscar por nombre, teléfono, DNI o placa..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-900 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                   focus:border-brand-primary transition-all duration-150" />
            </div>
            <select v-model="filtro" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
               focus:border-brand-primary transition-all duration-200 font-semibold">
                <option value="">Todos</option>
                <option value="pendiente">Pendientes de verificar</option>
                <option value="verificado">Verificados activos</option>
                <option value="inactivo">Verificados inactivos</option>
            </select>
        </div>

        <!-- Skeleton -->
        <div v-if="store.loading" class="flex flex-col gap-3">
            <div v-for="n in 4" :key="n" class="h-28 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
        </div>

        <!-- Empty -->
        <div v-else-if="store.motorizados.length === 0"
            class="flex flex-col items-center py-20 text-gray-400 dark:text-gray-600 gap-4">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <UserGroupIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
            </div>
            <p class="font-bold text-gray-600 dark:text-gray-400 text-[15px] m-0">
                Sin motorizados {{ filtro || buscar ? 'con este filtro' : 'registrados' }}
            </p>
        </div>

        <!-- Lista -->
        <div v-else class="flex flex-col gap-3">
            <div v-for="m in store.motorizados" :key="m.id" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                       shadow-sm dark:shadow-none p-4 sm:p-5 transition-colors duration-200">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                    <!-- Avatar + info -->
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <button @click="openDetalle(m)" class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 shrink-0 flex items-center
                        justify-center overflow-hidden border border-gray-200 dark:border-gray-700
                        cursor-pointer p-0">
                            <img v-if="m.foto" :src="m.foto" class="w-full h-full object-cover" />
                            <span v-else class="text-[18px] font-black text-gray-400 dark:text-gray-500">
                                {{ m.nombre.charAt(0).toUpperCase() }}
                            </span>
                        </button>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <button @click="openDetalle(m)" class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0
                           bg-transparent border-none p-0 cursor-pointer hover:underline">
                                    {{ m.nombre }}
                                </button>
                                <span v-if="m.verificado" class="text-[10px] font-bold px-2 py-0.5 rounded-full
                         bg-green-50 text-green-700 border border-green-200
                         dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                                    ✓ Verificado
                                </span>
                                <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded-full
                         bg-amber-50 text-amber-700 border border-amber-200
                         dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                    ⏳ Pendiente
                                </span>
                                <span :class="estadoCls(m.estado)"
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border">
                                    {{ estadoLabel(m.estado) }}
                                </span>
                            </div>

                            <div class="flex flex-col gap-0.5">
                                <p class="text-[12.5px] text-gray-500 dark:text-gray-400 m-0 flex items-center gap-1.5">
                                    <PhoneIcon class="w-3.5 h-3.5 shrink-0" />{{ m.telefono }}
                                </p>
                                <p class="text-[12.5px] text-gray-500 dark:text-gray-400 m-0 flex items-center gap-1.5">
                                    <EnvelopeIcon class="w-3.5 h-3.5 shrink-0" />{{ m.email }}
                                </p>
                                <p v-if="m.dni"
                                    class="text-[12.5px] text-gray-500 dark:text-gray-400 m-0 flex items-center gap-1.5">
                                    <IdentificationIcon class="w-3.5 h-3.5 shrink-0" />DNI {{ m.dni }}
                                </p>
                            </div>

                            <!-- Vehículo -->
                            <div v-if="m.placa" class="flex items-center gap-2 mt-2.5 flex-wrap">
                                <span class="text-[10.5px] font-black font-mono px-2 py-1 rounded-lg
                                             bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900">
                                    {{ m.placa }}
                                </span>
                                <span class="text-[11.5px] text-gray-400 dark:text-gray-500">
                                    {{ m.marca_vehiculo }} {{ m.modelo_vehiculo }} · {{ m.anio_vehiculo }}
                                </span>
                            </div>

                            <!-- Stats -->
                            <div v-if="m.stats"
                                class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <p class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 leading-none">
                                        {{
                                            m.stats.total_entregas }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">Entregas</p>
                                </div>
                                <div>
                                    <p class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0 leading-none">
                                        {{
                                            m.stats.entregas_hoy }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">Hoy</p>
                                </div>
                                <div>
                                    <div class="flex items-baseline gap-0.5">
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">S/</span>
                                        <p
                                            class="font-black text-[15px] text-purple-600 dark:text-purple-400 m-0 leading-none">
                                            {{ m.stats.deuda_pendiente.toFixed(2) }}
                                        </p>
                                    </div>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">Te debe</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex sm:flex-col gap-2 shrink-0">
                        <button @click="askToggleVerificado(m)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     transition-all duration-150"
                            :class="m.verificado
                                ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400 dark:hover:bg-red-500/20'
                                : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-400 dark:hover:bg-green-500/20'">
                            <CheckCircleIcon class="w-3.5 h-3.5" />
                            {{ m.verificado ? 'Quitar verif.' : 'Verificar' }}
                        </button>

                        <button v-if="m.verificado" @click="askToggleActivo(m)" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     transition-all duration-150"
                            :class="m.activo
                                ? 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700'
                                : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400 dark:hover:bg-blue-500/20'">
                            <component :is="m.activo ? NoSymbolIcon : CheckIcon" class="w-3.5 h-3.5" />
                            {{ m.activo ? 'Desactivar' : 'Activar' }}
                        </button>

                        <a :href="`https://wa.me/51${m.telefono.replace(/\D/g, '')}`" target="_blank" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5
                     px-3 py-2 rounded-xl text-[12px] font-bold border cursor-pointer
                     bg-[#25D366]/10 border-[#25D366]/30 text-[#128C7E]
                     dark:text-[#25D366] dark:border-[#25D366]/30 dark:bg-[#25D366]/10
                     hover:bg-[#25D366]/20 transition-all duration-150 no-underline">
                            💬 WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <Pagination v-if="!store.loading && store.motorizados.length > 0" :meta="store.meta" @change="cambiarPagina" />

        <!-- ══ MODAL DETALLE — verificación de identidad y vehículo ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="detalleModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="detalleModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="detalleModal.show && detalleModal.motorizado" class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6
                     max-h-[85vh] flex flex-col overflow-hidden">

                            <div class="flex items-center justify-between mb-4 shrink-0">
                                <h3 class="font-black text-[17px] text-gray-900 dark:text-gray-100 m-0"
                                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                                    {{ detalleModal.motorizado.nombre }}
                                </h3>
                                <button @click="detalleModal.show = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                         cursor-pointer border-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                    <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto -mx-2 px-2 flex flex-col gap-4">

                                <!-- Foto del vehículo -->
                                <div v-if="detalleModal.motorizado.foto_vehiculo">
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                        Foto del vehículo
                                    </p>
                                    <img :src="detalleModal.motorizado.foto_vehiculo"
                                        class="w-full h-48 object-cover rounded-2xl border border-gray-100 dark:border-gray-800" />
                                </div>

                                <!-- Datos personales -->
                                <div>
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">
                                        Datos personales
                                    </p>
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">DNI</p>
                                            <p
                                                class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0 font-mono">
                                                {{ detalleModal.motorizado.dni ?? '—' }}
                                            </p>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">Fecha de
                                                nacimiento</p>
                                            <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">
                                                {{ formatFecha(detalleModal.motorizado.fecha_nacimiento) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos del vehículo -->
                                <div>
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">
                                        Vehículo
                                    </p>
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">Placa</p>
                                            <p
                                                class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0 font-mono">
                                                {{ detalleModal.motorizado.placa ?? '—' }}
                                            </p>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">Marca / Modelo
                                            </p>
                                            <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">
                                                {{ detalleModal.motorizado.marca_vehiculo }} {{
                                                    detalleModal.motorizado.modelo_vehiculo }}
                                            </p>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">Año</p>
                                            <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">
                                                {{ detalleModal.motorizado.anio_vehiculo ?? '—' }}
                                            </p>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-2.5">
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 m-0">SOAT</p>
                                            <p
                                                class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0 font-mono">
                                                {{ detalleModal.motorizado.soat_numero ?? 'No registrado' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4 mt-2 border-t border-gray-100 dark:border-gray-800 shrink-0 flex gap-2">
                                <button @click="detalleModal.show = false; askToggleVerificado(detalleModal.motorizado)"
                                    class="flex-1 py-3 rounded-2xl font-bold text-[13.5px] text-white
                             border-none cursor-pointer transition-all duration-150"
                                    :class="detalleModal.motorizado.verificado ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'">
                                    {{ detalleModal.motorizado.verificado ? 'Quitar verificación' : 'Verificar motorizado' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

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
import { ref, reactive, onMounted, watch } from 'vue'
import {
    UserGroupIcon, PhoneIcon, EnvelopeIcon, CheckCircleIcon, CheckIcon, NoSymbolIcon,
    MagnifyingGlassIcon, IdentificationIcon, XMarkIcon,
} from '@heroicons/vue/24/outline'
import { useMotorizadosStore, type MotorizadoItem } from '../stores/motorizados'
import ConfirmModal from '../components/ConfirmModal.vue'
import Pagination from '../components/Pagination.vue'
import { useToastStore } from '../stores/toast'

const store = useMotorizadosStore()
const toast = useToastStore()

// ── Búsqueda + filtro + paginación ────────────────────────
const buscar = ref('')
const filtro = ref('')
const page = ref(1)
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function cargar() {
    store.fetchAll({
        buscar: buscar.value || undefined,
        filtro_estado: filtro.value || undefined,
        page: page.value,
    })
}

function cambiarPagina(p: number) {
    page.value = p
    cargar()
}

watch(buscar, () => {
    page.value = 1
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(cargar, 350)
})

watch(filtro, () => {
    page.value = 1
    cargar()
})

onMounted(cargar)

function estadoLabel(s: string): string {
    const m: Record<string, string> = { disponible: 'Disponible', ocupado: 'Ocupado', inactivo: 'Inactivo' }
    return m[s] ?? s
}

function estadoCls(s: string): string {
    const m: Record<string, string> = {
        disponible: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20',
        ocupado: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        inactivo: 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
    }
    return m[s] ?? m.inactivo
}

function formatFecha(d: string | undefined | null): string {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' })
}

// ── Modal de detalle (foto vehículo + datos personales) ───
const detalleModal = reactive({ show: false, motorizado: null as MotorizadoItem | null })

function openDetalle(m: MotorizadoItem) {
    detalleModal.motorizado = m
    detalleModal.show = true
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