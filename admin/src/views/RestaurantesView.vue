<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Restaurantes
                </h1>
                <p class="text-[13px] text-gray-400 mt-1 m-0">
                    {{ store.restaurants.length }} cliente{{ store.restaurants.length !== 1 ? 's' : '' }} registrado{{
                        store.restaurants.length !== 1 ? 's' : '' }}
                </p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-brand-primary
               text-white font-bold text-[13px] border-none cursor-pointer
               shadow-sm hover:bg-brand-primary-dark transition-all duration-150">
                <PlusIcon class="w-4 h-4" />
                Nuevo restaurante
            </button>
        </div>

        <!-- Skeleton -->
        <div v-if="store.loading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="n in 3" :key="n" class="h-44 rounded-2xl bg-gray-100 animate-pulse" />
        </div>

        <!-- Empty -->
        <div v-else-if="store.restaurants.length === 0" class="flex flex-col items-center py-20 text-gray-400 gap-4">
            <div class="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center">
                <BuildingStorefrontIcon class="w-10 h-10 text-gray-300" />
            </div>
            <div class="text-center">
                <p class="font-bold text-gray-600 text-[15px] m-0">Sin restaurantes</p>
                <p class="text-[13px] m-0 mt-1">Conecta tu primer cliente al sistema</p>
            </div>
            <button @click="openCreateModal" class="flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-brand-primary
               text-white font-bold text-[13px] border-none cursor-pointer
               shadow-sm hover:bg-brand-primary-dark transition-all duration-150">
                <PlusIcon class="w-4 h-4" />
                Registrar restaurante
            </button>
        </div>

        <!-- Grid -->
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="r in store.restaurants" :key="r.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5
               flex flex-col gap-3">

                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center
                        justify-center shrink-0">
                            <BuildingStorefrontIcon class="w-5 h-5 text-brand-primary" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-[14.5px] text-gray-900 m-0 truncate">
                                {{ r.name }}
                            </p>
                            <p class="text-[11.5px] text-gray-400 m-0 font-mono">{{ r.slug }}</p>
                        </div>
                    </div>
                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full shrink-0"
                        :class="r.activo ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                        {{ r.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <div class="flex items-center gap-1.5 text-[12px] text-gray-400">
                    <ClipboardDocumentListIcon class="w-3.5 h-3.5" />
                    {{ r.total_despachos }} despacho{{ r.total_despachos !== 1 ? 's' : '' }} histórico{{
                        r.total_despachos !== 1 ? 's' : '' }}
                </div>

                <div class="flex gap-2 mt-1">
                    <button @click="openKeyModal(r)" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl
                   text-[12px] font-bold border border-gray-200 bg-white
                   text-gray-600 cursor-pointer hover:border-brand-primary
                   hover:text-brand-primary transition-all duration-150">
                        <KeyIcon class="w-3.5 h-3.5" />
                        API Key
                    </button>
                    <button @click="openEditModal(r)" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl
                   text-[12px] font-bold border border-gray-200 bg-white
                   text-gray-600 cursor-pointer hover:border-gray-300
                   transition-all duration-150">
                        <PencilIcon class="w-3.5 h-3.5" />
                        Editar
                    </button>
                    <button @click="askToggleActivo(r)" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                   border border-gray-200 bg-white cursor-pointer
                   transition-all duration-150" :class="r.activo
                    ? 'text-amber-500 hover:border-amber-300'
                    : 'text-green-500 hover:border-green-300'" :title="r.activo ? 'Desactivar' : 'Activar'">
                        <component :is="r.activo ? PauseCircleIcon : PlayCircleIcon" class="w-4.5 h-4.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ MODAL CREAR/EDITAR ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="formModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="formModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="formModal.show" class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6">

                            <h3 class="font-black text-[18px] text-gray-900 m-0 mb-5"
                                style="font-family:'Plus Jakarta Sans',sans-serif;">
                                {{ formModal.editing ? 'Editar restaurante' : 'Nuevo restaurante' }}
                            </h3>

                            <div class="flex flex-col gap-3.5">
                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                tracking-widest text-gray-400 mb-1.5">
                                        Nombre del restaurante *
                                    </label>
                                    <input v-model="form.name" placeholder="Ej: Mahoma Chicken" class="modal-input" />
                                </div>

                                <div v-if="!formModal.editing">
                                    <label class="block text-[10.5px] font-black uppercase
                                tracking-widest text-gray-400 mb-1.5">
                                        Slug (identificador único) *
                                    </label>
                                    <input v-model="form.slug" placeholder="mahoma-chicken"
                                        class="modal-input font-mono" />
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        Solo letras minúsculas, números y guiones
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                tracking-widest text-gray-400 mb-1.5">
                                        Webhook URL
                                    </label>
                                    <input v-model="form.webhook_url"
                                        placeholder="https://mahoma.com/api/v1/webhooks/despacho"
                                        class="modal-input font-mono text-[12.5px]" />
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        URL donde se notificarán los cambios de estado de despachos
                                    </p>
                                </div>
                            </div>

                            <Transition enter-active-class="transition-all duration-150"
                                enter-from-class="opacity-0 -translate-y-1" leave-to-class="opacity-0">
                                <div v-if="formModal.error" class="mt-3.5 px-3.5 py-3 rounded-2xl bg-red-50 border border-red-200
                         text-[12.5px] text-red-600 flex items-center gap-2">
                                    <ExclamationCircleIcon class="w-4 h-4 shrink-0" />
                                    {{ formModal.error }}
                                </div>
                            </Transition>

                            <div class="flex gap-3 mt-5">
                                <button @click="formModal.show = false" :disabled="formModal.loading" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white
                         hover:border-gray-300 transition-all duration-150
                         disabled:opacity-50">
                                    Cancelar
                                </button>
                                <button @click="submitForm" :disabled="formModal.loading || !canSubmit" class="flex-1 py-3 rounded-2xl text-white font-bold text-[13.5px]
                         cursor-pointer border-none bg-brand-primary
                         hover:bg-brand-primary-dark disabled:opacity-50
                         transition-all duration-150 flex items-center
                         justify-center gap-2">
                                    <span v-if="formModal.loading" class="w-4 h-4 border-2 border-white/30 border-t-white
                           rounded-full animate-spin" />
                                    {{ formModal.loading
                                        ? 'Guardando...'
                                        : (formModal.editing ? 'Guardar cambios' : 'Crear restaurante') }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ MODAL API KEY ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="keyModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                 flex items-center justify-center p-4" @click.self="keyModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="keyModal.show" class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-6">

                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-2xl bg-blue-50 flex items-center
                            justify-center shrink-0">
                                    <KeyIcon class="w-5 h-5 text-brand-primary" />
                                </div>
                                <div>
                                    <h3 class="font-black text-[16px] text-gray-900 m-0">
                                        Credenciales API
                                    </h3>
                                    <p class="text-[12px] text-gray-400 m-0">{{ keyModal.restaurant?.name }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                tracking-widest text-gray-400 mb-1.5">
                                        API Key
                                    </label>
                                    <div class="flex gap-2">
                                        <input :value="keyModal.restaurant?.api_key" readonly
                                            class="modal-input font-mono text-[11.5px] flex-1" />
                                        <button @click="copyToClipboard(keyModal.restaurant?.api_key, 'API Key')" class="w-11 shrink-0 rounded-2xl border-2 border-gray-100
                             bg-gray-50 flex items-center justify-center
                             text-gray-500 hover:text-brand-primary hover:border-brand-primary
                             cursor-pointer transition-all duration-150">
                                            <ClipboardIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10.5px] font-black uppercase
                                tracking-widest text-gray-400 mb-1.5">
                                        Webhook Secret
                                    </label>
                                    <div class="flex gap-2">
                                        <input :value="keyModal.restaurant?.webhook_secret" readonly
                                            class="modal-input font-mono text-[11.5px] flex-1" />
                                        <button
                                            @click="copyToClipboard(keyModal.restaurant?.webhook_secret, 'Webhook Secret')"
                                            class="w-11 shrink-0 rounded-2xl border-2 border-gray-100
                             bg-gray-50 flex items-center justify-center
                             text-gray-500 hover:text-brand-primary hover:border-brand-primary
                             cursor-pointer transition-all duration-150">
                                            <ClipboardIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 px-3.5 py-3 rounded-2xl bg-amber-50 border border-amber-200
                          flex items-start gap-2">
                                <ExclamationTriangleIcon class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                                <p class="text-[11.5px] text-amber-700 m-0 leading-relaxed">
                                    Guarda estas credenciales en el <code class="font-mono">.env</code> del backend del
                                    restaurante.
                                    Si las regeneras, las anteriores dejan de funcionar inmediatamente.
                                </p>
                            </div>

                            <div class="flex gap-3 mt-5">
                                <button @click="keyModal.show = false" class="flex-1 py-3 rounded-2xl border-2 border-gray-200 text-gray-600
                         font-semibold text-[13.5px] cursor-pointer bg-white
                         hover:border-gray-300 transition-all duration-150">
                                    Cerrar
                                </button>
                                <button @click="askRegenerateKey" class="flex-1 py-3 rounded-2xl text-amber-600 font-bold text-[13.5px]
                         cursor-pointer border-2 border-amber-200 bg-amber-50
                         hover:bg-amber-100 transition-all duration-150
                         flex items-center justify-center gap-2">
                                    <ArrowPathIcon class="w-4 h-4" />
                                    Regenerar
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Modales de confirmación -->
        <ConfirmModal v-model="confirmToggle.show"
            :title="confirmToggle.target?.activo ? '¿Desactivar restaurante?' : '¿Activar restaurante?'" :message="confirmToggle.target?.activo
                ? `${confirmToggle.target?.name} dejará de poder solicitar despachos hasta que lo reactives.`
                : `${confirmToggle.target?.name} podrá volver a solicitar despachos normalmente.`"
            :variant="confirmToggle.target?.activo ? 'warning' : 'success'"
            :confirm-label="confirmToggle.target?.activo ? 'Sí, desactivar' : 'Sí, activar'"
            :loading="confirmToggle.loading" @confirm="executeToggleActivo" />

        <ConfirmModal v-model="confirmRegen.show" title="¿Regenerar API Key?"
            message="La API key y webhook secret actuales dejarán de funcionar de inmediato. El restaurante deberá actualizar sus credenciales."
            variant="danger" confirm-label="Sí, regenerar" :loading="confirmRegen.loading"
            @confirm="executeRegenerateKey" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import {
    PlusIcon, BuildingStorefrontIcon, ClipboardDocumentListIcon,
    KeyIcon, PencilIcon, PauseCircleIcon, PlayCircleIcon,
    ExclamationCircleIcon, ExclamationTriangleIcon, ClipboardIcon, ArrowPathIcon,
} from '@heroicons/vue/24/outline'
import { useRestaurantsStore, type Restaurant } from '../stores/restaurants'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToastStore } from '../stores/toast'

const store = useRestaurantsStore()
const toast = useToastStore()

onMounted(() => store.fetchAll())

// ── Form crear/editar ─────────────────────────────────────
const form = reactive({ name: '', slug: '', webhook_url: '' })

const formModal = reactive({
    show: false, editing: null as Restaurant | null, loading: false, error: '',
})

const canSubmit = computed(() => {
    if (formModal.editing) return form.name.trim().length > 0
    return form.name.trim().length > 0 && /^[a-z0-9-]+$/.test(form.slug.trim())
})

function openCreateModal() {
    Object.assign(form, { name: '', slug: '', webhook_url: '' })
    formModal.editing = null
    formModal.error = ''
    formModal.show = true
}

function openEditModal(r: Restaurant) {
    Object.assign(form, { name: r.name, slug: r.slug, webhook_url: r.webhook_url ?? '' })
    formModal.editing = r
    formModal.error = ''
    formModal.show = true
}

async function submitForm() {
    if (!canSubmit.value) return
    formModal.loading = true
    formModal.error = ''

    if (formModal.editing) {
        const ok = await store.update(formModal.editing.id, {
            name: form.name.trim(),
            webhook_url: form.webhook_url.trim() || null,
        })
        formModal.loading = false
        if (ok) {
            formModal.show = false
            toast.success('Restaurante actualizado')
        } else {
            formModal.error = 'Error al actualizar el restaurante'
        }
    } else {
        const result = await store.create({
            name: form.name.trim(),
            slug: form.slug.trim(),
            webhook_url: form.webhook_url.trim() || undefined,
        })
        formModal.loading = false
        if (result.ok) {
            formModal.show = false
            toast.success('Restaurante creado correctamente')
            if (result.data) openKeyModal(result.data)
        } else {
            formModal.error = result.message ?? 'Error al crear el restaurante'
        }
    }
}

// ── Modal API Key ──────────────────────────────────────────
const keyModal = reactive({ show: false, restaurant: null as Restaurant | null })

async function openKeyModal(r: Restaurant) {
    const full = await store.getOne(r.id)
    keyModal.restaurant = full ?? r
    keyModal.show = true
}

function copyToClipboard(text: string | undefined, label: string) {
    if (!text) return
    navigator.clipboard.writeText(text)
    toast.success(`${label} copiado al portapapeles`)
}

// ── Confirmar regenerar key ───────────────────────────────
const confirmRegen = reactive({ show: false, loading: false })

function askRegenerateKey() {
    confirmRegen.show = true
}

async function executeRegenerateKey() {
    if (!keyModal.restaurant) return
    confirmRegen.loading = true
    const updated = await store.regenerateKey(keyModal.restaurant.id)
    confirmRegen.loading = false
    confirmRegen.show = false
    if (updated) {
        keyModal.restaurant = updated
        toast.success('Credenciales regeneradas')
    } else {
        toast.error('Error al regenerar las credenciales')
    }
}

// ── Confirmar activar/desactivar ──────────────────────────
const confirmToggle = reactive({
    show: false, loading: false, target: null as Restaurant | null,
})

function askToggleActivo(r: Restaurant) {
    confirmToggle.target = r
    confirmToggle.show = true
}

async function executeToggleActivo() {
    if (!confirmToggle.target) return
    confirmToggle.loading = true
    const ok = await store.update(confirmToggle.target.id, { activo: !confirmToggle.target.activo })
    confirmToggle.loading = false
    confirmToggle.show = false
    if (ok) {
        toast.success(confirmToggle.target.activo ? 'Restaurante desactivado' : 'Restaurante activado')
    } else {
        toast.error('Error al actualizar el estado')
    }
}
</script>

<style scoped>
.modal-input {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border-radius: 0.875rem;
    border: 2px solid #f3f4f6;
    background: #f9fafb;
    font-size: 13.5px;
    color: #111827;
    outline: none;
    transition: all 0.2s;
}

.modal-input::placeholder {
    color: #d1d5db;
}

.modal-input:focus {
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
}
</style>