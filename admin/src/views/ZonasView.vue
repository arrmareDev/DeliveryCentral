<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Zonas de cobertura
                </h1>
                <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">
                    Organiza qué motorizados cubren cada zona
                </p>
            </div>
            <button @click="openCreate" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-primary text-white font-bold
                     text-[13px] border-none cursor-pointer hover:-translate-y-0.5 transition-all duration-150">
                <PlusIcon class="w-4 h-4" />
                Nueva zona
            </button>
        </div>

        <!-- Tabla -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
             shadow-sm dark:shadow-none overflow-hidden transition-colors duration-200">

            <div v-if="store.loading" class="flex flex-col gap-2 p-4">
                <div v-for="n in 3" :key="n" class="h-14 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
            </div>

            <div v-else-if="store.zonas.length === 0" class="flex flex-col items-center py-16 gap-3 text-center">
                <MapIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
                <p class="text-gray-400 dark:text-gray-500 text-[13.5px] m-0">Todavía no hay zonas registradas.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Zona</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Descripción</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-right">
                                Motorizados</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Estado</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="z in store.zonas" :key="z.id"
                            class="border-b border-gray-50 dark:border-gray-800/60 last:border-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-bold text-[13.5px] text-gray-900 dark:text-gray-100 m-0">{{ z.nombre }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-[12.5px] text-gray-500 dark:text-gray-400 m-0 truncate max-w-[280px]">
                                    {{ z.descripcion || '—' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openMotorizados(z)" class="text-[13px] font-bold text-blue-600 dark:text-blue-400 bg-transparent border-none
                                     cursor-pointer hover:underline">
                                    {{ z.total_motorizados }}
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap"
                                    :class="z.activo
                                        ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20'
                                        : 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'">
                                    {{ z.activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button @click="openMotorizados(z)" title="Asignar motorizados" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                                     border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300
                                     bg-white dark:bg-gray-900 hover:border-blue-300 hover:text-blue-600">
                                        <UserGroupIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="openEdit(z)" title="Editar" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                                     border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300
                                     bg-white dark:bg-gray-900 hover:border-amber-300 hover:text-amber-600">
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button @click="askDelete(z)" title="Eliminar" class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150
                                     border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300
                                     bg-white dark:bg-gray-900 hover:border-red-300 hover:text-red-600">
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══ MODAL CREAR/EDITAR ══ -->
        <Teleport to="body">
            <div v-if="formModal.show"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="formModal.show = false">
                <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h2 class="font-black text-lg text-gray-900 dark:text-gray-100 m-0"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            {{ formModal.editing ? 'Editar zona' : 'Nueva zona' }}
                        </h2>
                        <button @click="formModal.show = false"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
                               bg-gray-100 dark:bg-gray-800 border-none cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="p-6 flex flex-col gap-4">
                        <div>
                            <label
                                class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                Nombre *
                            </label>
                            <input v-model="form.nombre" placeholder="Ej: Chiclayo Norte" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                                   bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                                   focus:border-brand-primary transition-all duration-150" />
                        </div>
                        <div>
                            <label
                                class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                Descripción
                            </label>
                            <input v-model="form.descripcion" placeholder="Ej: José Leonardo Ortiz, La Victoria" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                                   bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                                   focus:border-brand-primary transition-all duration-150" />
                        </div>

                        <p v-if="formModal.error" class="text-[12.5px] text-red-600 dark:text-red-400 m-0">{{
                            formModal.error }}</p>
                    </div>

                    <div class="p-6 pt-0 flex gap-3">
                        <button @click="formModal.show = false"
                            class="flex-1 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300
                               font-bold text-[13.5px] bg-white dark:bg-gray-900 cursor-pointer hover:border-gray-300 transition-all duration-150">
                            Cancelar
                        </button>
                        <button @click="submitForm" :disabled="formModal.loading || !form.nombre.trim()"
                            class="flex-1 py-3 rounded-2xl bg-brand-primary text-white font-bold text-[13.5px]
                               border-none cursor-pointer hover:-translate-y-0.5 disabled:opacity-60 transition-all duration-150">
                            {{ formModal.loading ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ MODAL ASIGNAR MOTORIZADOS ══ -->
        <Teleport to="body">
            <div v-if="motorizadosModal.show"
                class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="motorizadosModal.show = false">
                <div
                    class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-3xl shadow-2xl max-h-[80vh] flex flex-col overflow-hidden">
                    <div
                        class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                        <div>
                            <h2 class="font-black text-lg text-gray-900 dark:text-gray-100 m-0"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                Motorizados en {{ motorizadosModal.zona?.nombre }}
                            </h2>
                            <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">Marca quiénes cubren esta
                                zona</p>
                        </div>
                        <button @click="motorizadosModal.show = false"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400
                               bg-gray-100 dark:bg-gray-800 border-none cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 shrink-0">
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4">
                        <div v-if="motorizadosModal.loading" class="flex flex-col gap-2">
                            <div v-for="n in 4" :key="n"
                                class="h-12 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                        </div>
                        <div v-else class="flex flex-col gap-1.5">
                            <label v-for="m in todosMotorizados" :key="m.id" class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer
                                   hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                                <input type="checkbox" :checked="seleccionados.has(m.id)"
                                    @change="toggleSeleccion(m.id)"
                                    class="w-4 h-4 rounded accent-brand-primary cursor-pointer shrink-0" />
                                <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate">{{ m.nombre
                                    }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-6 pt-4 border-t border-gray-100 dark:border-gray-800 shrink-0">
                        <button @click="guardarMotorizados" :disabled="motorizadosModal.saving"
                            class="w-full py-3 rounded-2xl bg-brand-primary text-white font-bold text-[13.5px]
                               border-none cursor-pointer hover:-translate-y-0.5 disabled:opacity-60 transition-all duration-150">
                            {{ motorizadosModal.saving ? 'Guardando...' : 'Guardar asignación' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <ConfirmModal v-model="confirmDelete.show" title="¿Eliminar esta zona?"
            :message="`«${confirmDelete.zona?.nombre}» se eliminará junto con sus asignaciones de motorizados.`"
            variant="danger" confirm-label="Sí, eliminar" :loading="confirmDelete.loading" @confirm="doDelete" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import {
    PlusIcon, PencilIcon, TrashIcon, XMarkIcon, MapIcon, UserGroupIcon,
} from '@heroicons/vue/24/outline'
import { useZonasStore, type Zona } from '../stores/zonas'
import { useMotorizadosStore } from '../stores/motorizados'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToastStore } from '../stores/toast'

const store = useZonasStore()
const motorizadosStore = useMotorizadosStore()
const toast = useToastStore()

onMounted(() => store.fetchAll())

// ── Crear / editar ──────────────────────────────────────────
const formModal = reactive({ show: false, editing: null as Zona | null, loading: false, error: '' })
const form = reactive({ nombre: '', descripcion: '' })

function openCreate() {
    Object.assign(form, { nombre: '', descripcion: '' })
    formModal.editing = null
    formModal.error = ''
    formModal.show = true
}

function openEdit(z: Zona) {
    Object.assign(form, { nombre: z.nombre, descripcion: z.descripcion ?? '' })
    formModal.editing = z
    formModal.error = ''
    formModal.show = true
}

async function submitForm() {
    if (!form.nombre.trim()) return
    formModal.loading = true
    formModal.error = ''

    if (formModal.editing) {
        const ok = await store.update(formModal.editing.id, {
            nombre: form.nombre.trim(),
            descripcion: form.descripcion.trim() || null,
        })
        formModal.loading = false
        if (ok) {
            formModal.show = false
            toast.success('Zona actualizada')
        } else {
            formModal.error = 'No se pudo actualizar la zona'
        }
    } else {
        const result = await store.create({
            nombre: form.nombre.trim(),
            descripcion: form.descripcion.trim() || undefined,
        })
        formModal.loading = false
        if (result.ok) {
            formModal.show = false
            toast.success('Zona creada')
            store.fetchAll()
        } else {
            formModal.error = result.message ?? 'No se pudo crear la zona'
        }
    }
}

// ── Eliminar ─────────────────────────────────────────────
const confirmDelete = reactive({ show: false, zona: null as Zona | null, loading: false })

function askDelete(z: Zona) {
    confirmDelete.zona = z
    confirmDelete.show = true
}

async function doDelete() {
    if (!confirmDelete.zona) return
    confirmDelete.loading = true
    const ok = await store.remove(confirmDelete.zona.id)
    confirmDelete.loading = false
    confirmDelete.show = false
    if (ok) toast.success('Zona eliminada')
    else toast.error('No se pudo eliminar la zona')
}

// ── Asignar motorizados ──────────────────────────────────
const motorizadosModal = reactive({ show: false, zona: null as Zona | null, loading: false, saving: false })
const todosMotorizados = ref<Array<{ id: number; nombre: string }>>([])
const seleccionados = ref<Set<number>>(new Set())

async function openMotorizados(z: Zona) {
    motorizadosModal.zona = z
    motorizadosModal.show = true
    motorizadosModal.loading = true

    await motorizadosStore.fetchDisponibles()
    todosMotorizados.value = motorizadosStore.disponibles.map((m) => ({ id: m.id, nombre: m.nombre }))

    const zonaCompleta = await store.fetchOne(z.id)
    seleccionados.value = new Set((zonaCompleta?.motorizados ?? []).map((m) => m.id))

    motorizadosModal.loading = false
}

function toggleSeleccion(id: number) {
    if (seleccionados.value.has(id)) seleccionados.value.delete(id)
    else seleccionados.value.add(id)
}

async function guardarMotorizados() {
    if (!motorizadosModal.zona) return
    motorizadosModal.saving = true
    const result = await store.sincronizarMotorizados(motorizadosModal.zona.id, Array.from(seleccionados.value))
    motorizadosModal.saving = false
    if (result.ok) {
        motorizadosModal.show = false
        toast.success('Motorizados asignados')
    } else {
        toast.error(result.message ?? 'No se pudo guardar')
    }
}
</script>