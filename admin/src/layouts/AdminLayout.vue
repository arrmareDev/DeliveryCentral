<template>
    <div class="min-h-screen bg-gray-50 flex">

        <!-- ══ OVERLAY MÓVIL ══ -->
        <Transition enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden" />
        </Transition>

        <!-- ══ SIDEBAR ══ -->
        <aside class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-gray-900
                  flex flex-col transition-transform duration-300 shrink-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

            <!-- Logo -->
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-800 shrink-0">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
                    <TruckIcon class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="font-black text-[14px] text-white m-0 leading-none"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        Delivery Central
                    </p>
                    <p class="text-[10.5px] text-gray-400 m-0 mt-0.5">Panel superadmin</p>
                </div>
                <button @click="sidebarOpen = false" class="ml-auto w-7 h-7 rounded-lg flex items-center justify-center
                 text-gray-400 hover:text-white hover:bg-gray-800
                 border-none bg-transparent cursor-pointer lg:hidden">
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-1">
                <RouterLink v-for="item in NAV_ITEMS" :key="item.to" :to="item.to" @click="sidebarOpen = false" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13.5px]
                 font-semibold no-underline transition-all duration-150" :class="isActive(item.to)
                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'">
                    <component :is="item.icon" class="w-[18px] h-[18px] shrink-0" />
                    {{ item.label }}
                    <span v-if="item.badge && item.badge() > 0" class="ml-auto text-[10px] font-black px-1.5 py-0.5 rounded-full
                   bg-red-500 text-white min-w-[18px] text-center">
                        {{ item.badge() }}
                    </span>
                </RouterLink>
            </nav>

            <!-- User -->
            <div class="p-3 border-t border-gray-800 shrink-0">
                <div class="flex items-center gap-2.5 px-2 py-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center
                      text-white text-[12px] font-black shrink-0">
                        {{ auth.user?.name?.charAt(0).toUpperCase() ?? 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12.5px] font-bold text-white m-0 truncate">
                            {{ auth.user?.name }}
                        </p>
                        <p class="text-[10.5px] text-gray-500 m-0 truncate">
                            {{ auth.user?.email }}
                        </p>
                    </div>
                    <button @click="confirmLogout = true" title="Cerrar sesión" class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0
                   text-gray-400 hover:text-red-400 hover:bg-gray-800
                   border-none bg-transparent cursor-pointer transition-colors">
                        <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- ══ MAIN ══ -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Header móvil -->
            <header class="h-16 bg-white border-b border-gray-100 flex items-center
                      gap-3 px-4 lg:hidden shrink-0">
                <button @click="sidebarOpen = true" class="w-9 h-9 rounded-xl flex items-center justify-center
                 text-gray-600 hover:bg-gray-100 border-none bg-transparent
                 cursor-pointer">
                    <Bars3Icon class="w-5 h-5" />
                </button>
                <p class="font-black text-[15px] text-gray-900 m-0" style="font-family:'Plus Jakarta Sans',sans-serif;">
                    {{ currentTitle }}
                </p>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <RouterView />
            </main>
        </div>

        <!-- Modal confirmar logout -->
        <ConfirmModal v-model="confirmLogout" title="¿Cerrar sesión?"
            message="Tendrás que volver a iniciar sesión para acceder al panel." variant="warning"
            confirm-label="Sí, cerrar sesión" :loading="loggingOut" @confirm="handleLogout" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
    TruckIcon, Bars3Icon, XMarkIcon, ArrowRightStartOnRectangleIcon,
    HomeIcon, BuildingStorefrontIcon, UserGroupIcon,
    ClipboardDocumentListIcon, BanknotesIcon, Cog6ToothIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../stores/auth'
import { useDespachosStore } from '../stores/despachos'
import { useEcho } from '../composables/useHecho.ts'
import ConfirmModal from '../components/ConfirmModal.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const despachos = useDespachosStore()

const sidebarOpen = ref(false)
const confirmLogout = ref(false)
const loggingOut = ref(false)

const NAV_ITEMS = [
    { to: '/', label: 'Dashboard', icon: HomeIcon },
    { to: '/restaurantes', label: 'Restaurantes', icon: BuildingStorefrontIcon },
    { to: '/motorizados', label: 'Motorizados', icon: UserGroupIcon },
    { to: '/despachos', label: 'Despachos', icon: ClipboardDocumentListIcon, badge: () => despachos.stats.total_activos },
    { to: '/comisiones', label: 'Comisiones', icon: BanknotesIcon },
    { to: '/configuracion', label: 'Configuración', icon: Cog6ToothIcon },
]

const TITLES: Record<string, string> = {
    dashboard: 'Dashboard',
    restaurantes: 'Restaurantes',
    motorizados: 'Motorizados',
    despachos: 'Despachos',
    comisiones: 'Comisiones',
    configuracion: 'Configuración',
}

const currentTitle = computed(() => TITLES[route.name as string] ?? '')

function isActive(path: string): boolean {
    return route.path === path
}

async function handleLogout() {
    loggingOut.value = true
    await auth.logout()
    loggingOut.value = false
    confirmLogout.value = false
    router.push('/login')
}

// ── WebSocket global para badge de despachos activos ──────
let echo: any = null

onMounted(async () => {
    await despachos.fetchAll()
    echo = useEcho()
    echo.channel('admin.despachos')
        .listen('.despacho.actualizado', (data: any) => {
            despachos.handleRealtimeUpdate(data)
        })
})

onUnmounted(() => {
    try { echo?.leaveChannel('admin.despachos') } catch { /* noop */ }
})
</script>