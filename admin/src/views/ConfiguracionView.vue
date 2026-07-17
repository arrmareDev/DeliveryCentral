<template>
    <div class="flex flex-col gap-6 max-w-2xl">

        <!-- Header -->
        <div>
            <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                style="font-family:'Plus Jakarta Sans',sans-serif;">
                Configuración
            </h1>
            <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">
                Ajustes generales de la plataforma central
            </p>
        </div>

        <!-- Comisión por entrega -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                    shadow-sm dark:shadow-none p-5 sm:p-6 transition-colors duration-200">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center shrink-0">
                    <BanknotesIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0">Comisión por entrega</h2>
                    <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0">
                        Monto que cada motorizado te debe por cada pedido entregado
                    </p>
                </div>
            </div>

            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-[10.5px] font-black uppercase tracking-widest
                        text-gray-400 dark:text-gray-500 mb-1.5">
                        Monto (S/)
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-[14px] font-semibold">
                            S/
                        </span>
                        <input v-model.number="comisionInput" type="number" step="0.10" min="0" class="w-full pl-10 pr-4 py-3 rounded-2xl border-2 border-gray-100 dark:border-gray-700
                     bg-gray-50 dark:bg-gray-800 text-[16px] font-bold text-gray-900 dark:text-gray-100 outline-none
                     focus:border-brand-primary focus:bg-white dark:focus:bg-gray-900
                     transition-all duration-200" />
                    </div>
                </div>
                <button @click="askUpdateConfig"
                    :disabled="comisionInput === store.comisionPorEntrega || comisionInput < 0" class="px-5 py-3 rounded-2xl font-bold text-[13.5px] text-white
                 bg-brand-primary border-none cursor-pointer
                 hover:bg-brand-primary-dark transition-all duration-150
                 disabled:opacity-40 disabled:cursor-not-allowed">
                    Guardar
                </button>
            </div>

            <div class="mt-3 px-3.5 py-3 rounded-2xl bg-blue-50 dark:bg-blue-500/10
                  border border-blue-100 dark:border-blue-500/20
                  flex items-start gap-2">
                <InformationCircleIcon class="w-4 h-4 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5" />
                <p class="text-[11.5px] text-blue-700 dark:text-blue-400 m-0 leading-relaxed">
                    Este cambio solo aplica a las nuevas entregas. Las comisiones ya generadas no se modifican.
                </p>
            </div>
        </div>

        <!-- Info de la cuenta -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                    shadow-sm dark:shadow-none p-5 sm:p-6 transition-colors duration-200">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                    <UserCircleIcon class="w-5 h-5 text-brand-primary" />
                </div>
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 m-0">Tu cuenta</h2>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-[13px] text-gray-500 dark:text-gray-400">Nombre</span>
                    <span class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ auth.user?.name
                        }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-[13px] text-gray-500 dark:text-gray-400">Correo</span>
                    <span class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ auth.user?.email
                        }}</span>
                </div>
            </div>
        </div>

        <ConfirmModal v-model="confirmUpdate.show" title="¿Actualizar comisión por entrega?"
            :message="`El monto de comisión por entrega cambiará de S/ ${store.comisionPorEntrega.toFixed(2)} a S/ ${comisionInput.toFixed(2)}. Esto aplicará solo a entregas futuras.`"
            variant="info" confirm-label="Sí, actualizar" :loading="confirmUpdate.loading"
            @confirm="executeUpdateConfig" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue'
import { BanknotesIcon, InformationCircleIcon, UserCircleIcon } from '@heroicons/vue/24/outline'
import { useComisionesStore } from '../stores/comisiones'
import { useAuthStore } from '../stores/auth'
import ConfirmModal from '../components/ConfirmModal.vue'
import { useToastStore } from '../stores/toast'

const store = useComisionesStore()
const auth = useAuthStore()
const toast = useToastStore()

const comisionInput = ref(0.5)

onMounted(async () => {
    await store.fetchConfig()
    comisionInput.value = store.comisionPorEntrega
})

watch(() => store.comisionPorEntrega, (val) => { comisionInput.value = val })

const confirmUpdate = reactive({ show: false, loading: false })

function askUpdateConfig() {
    confirmUpdate.show = true
}

async function executeUpdateConfig() {
    confirmUpdate.loading = true
    const ok = await store.updateConfig(comisionInput.value)
    confirmUpdate.loading = false
    confirmUpdate.show = false
    ok ? toast.success('Comisión actualizada correctamente') : toast.error('Error al guardar la configuración')
}
</script>