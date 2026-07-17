<template>
    <div class="relative">
        <button @click="toggle" class="relative w-9 h-9 rounded-xl flex items-center justify-center
                   text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800
                   border-none bg-transparent cursor-pointer transition-colors">
            <BellIcon class="w-[18px] h-[18px]" />
            <span v-if="store.noLeidas > 0" class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full
                       bg-red-500 text-white text-[9.5px] font-black flex items-center justify-center">
                {{ store.noLeidas > 9 ? '9+' : store.noLeidas }}
            </span>
        </button>

        <Transition enter-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1" leave-to-class="opacity-0 scale-95">
            <div v-if="open" class="fixed inset-0 z-40" @click="open = false" />
        </Transition>

        <Transition enter-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-95 -translate-y-1" leave-to-class="opacity-0 scale-95">
            <div v-if="open" class="absolute right-0 mt-2 w-80 max-h-[70vh] bg-white dark:bg-gray-900 rounded-2xl
                       border border-gray-100 dark:border-gray-800 shadow-xl z-50 flex flex-col overflow-hidden">

                <div
                    class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between shrink-0">
                    <h3 class="font-black text-[14px] text-gray-900 dark:text-gray-100 m-0">Notificaciones</h3>
                    <button v-if="store.noLeidas > 0" @click="store.marcarTodasLeidas()" class="text-[11.5px] font-bold text-brand-primary bg-transparent border-none
                               cursor-pointer hover:underline">
                        Marcar todas leídas
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div v-if="store.loading" class="flex flex-col gap-2 p-3">
                        <div v-for="n in 4" :key="n"
                            class="h-14 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                    </div>

                    <div v-else-if="store.notificaciones.length === 0"
                        class="flex flex-col items-center py-12 gap-2 text-gray-400 dark:text-gray-600">
                        <BellSlashIcon class="w-7 h-7 text-gray-300 dark:text-gray-700" />
                        <p class="text-[12.5px] m-0">Sin notificaciones</p>
                    </div>

                    <button v-for="n in store.notificaciones" :key="n.id" @click="abrir(n)" class="w-full text-left px-4 py-3 border-b border-gray-50 dark:border-gray-800/60
                               last:border-0 cursor-pointer border-x-0 border-t-0 bg-transparent
                               hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors flex gap-2.5">
                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0"
                            :class="n.leido ? 'bg-transparent' : 'bg-brand-primary'" />
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold m-0"
                                :class="n.leido ? 'text-gray-600 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100'">
                                {{ n.titulo }}
                            </p>
                            <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0 mt-0.5 line-clamp-2">
                                {{ n.mensaje }}
                            </p>
                            <p class="text-[10.5px] text-gray-300 dark:text-gray-600 m-0 mt-1">
                                {{ formatFecha(n.created_at) }}
                            </p>
                        </div>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { BellIcon, BellSlashIcon } from '@heroicons/vue/24/outline'
import { useNotificacionesStore, type Notificacion } from '../stores/notificaciones'
import { useEcho } from '../composables/useHecho.ts'
import { useNotificationSound } from '../composables/useNotificationSound'

const { play } = useNotificationSound()
const store = useNotificacionesStore()
const router = useRouter()
const open = ref(false)

function toggle() {
    open.value = !open.value
    if (open.value && store.notificaciones.length === 0) store.fetchAll()
}

function formatFecha(d: string): string {
    return new Date(d).toLocaleString('es-PE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

// Navega a la vista relevante según el tipo de notificación
function abrir(n: Notificacion) {
    if (!n.leido) store.marcarLeida(n.id)
    open.value = false

    if (n.tipo === 'nuevo_despacho' || n.tipo === 'despacho_cancelado') {
        router.push('/despachos')
    } else if (n.tipo === 'motorizado_pendiente') {
        router.push('/motorizados')
    }
}

let echo: any = null

onMounted(() => {
    store.fetchAll()
    echo = useEcho()
    echo.channel('admin.notificaciones')
        .listen('.notificacion.creada', (data: Notificacion) => {
            store.agregarNueva(data)
            play()
        })
})

onUnmounted(() => {
    try { echo?.leaveChannel('admin.notificaciones') } catch { /* noop */ }
})
</script>