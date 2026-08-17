<template>
    <div class="h-screen flex overflow-hidden bg-gray-50 dark:bg-gray-950 transition-colors duration-200">

        <!-- ══ OVERLAY MÓVIL ══ -->
        <Transition enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden" />
        </Transition>

        <!-- ══ SIDEBAR — fijo siempre, también en escritorio ══ -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 dark:bg-gray-950 dark:border-r dark:border-gray-800
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

        <!-- ══ MAIN — desplazado por el ancho del sidebar en escritorio,
             con su propia área de scroll independiente del header ══ -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64 h-screen overflow-hidden">

            <!-- Header — visible siempre (móvil y escritorio), no se mueve -->
            <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800
                      flex items-center gap-3 px-4 sm:px-6 lg:px-8 shrink-0 transition-colors duration-200">
                <button @click="sidebarOpen = true" class="w-9 h-9 rounded-xl flex items-center justify-center
                 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800
                 border-none bg-transparent cursor-pointer lg:hidden">
                    <Bars3Icon class="w-5 h-5" />
                </button>
                <div>
                    <Breadcrumbs />
                    <p class="font-black text-[15px] sm:text-[17px] text-gray-900 dark:text-gray-100 m-0"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        {{ currentTitle }}
                    </p>
                </div>

                <!-- Búsqueda global + campana de notificaciones + toggle de tema, agrupados a la derecha -->
                <div class="ml-auto flex items-center gap-1">
                    <button @click="globalSearch?.openSearch()" class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-gray-50 dark:bg-gray-800 text-[12.5px] font-medium text-gray-400 dark:text-gray-500
                       cursor-pointer hover:border-gray-300 dark:hover:border-gray-600 transition-all duration-150">
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Buscar
                        <kbd
                            class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">⌘K</kbd>
                    </button>
                    <NotificationBell />
                    <button @click="theme.toggle()" class="w-9 h-9 rounded-xl flex items-center justify-center
                     text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800
                     border-none bg-transparent cursor-pointer transition-colors">
                        <SunIcon v-if="theme.isDark" class="w-[18px] h-[18px]" />
                        <MoonIcon v-else class="w-[18px] h-[18px]" />
                    </button>
                </div>
            </header>

            <!-- Content — esta es la única zona que hace scroll -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <RouterView />
            </main>
        </div>

        <!-- Modal confirmar logout -->
        <ConfirmModal v-model="confirmLogout" title="¿Cerrar sesión?"
            message="Tendrás que volver a iniciar sesión para acceder al panel." variant="warning"
            confirm-label="Sí, cerrar sesión" :loading="loggingOut" @confirm="handleLogout" />

        <!-- Búsqueda global (Cmd+K) -->
        <GlobalSearch ref="globalSearch" />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type Echo from 'laravel-echo'
import {
    TruckIcon, Bars3Icon, XMarkIcon, ArrowRightStartOnRectangleIcon,
    HomeIcon, BuildingStorefrontIcon, UserGroupIcon, MapIcon,
    ClipboardDocumentListIcon, BanknotesIcon, Cog6ToothIcon,
    SunIcon, MoonIcon, MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../stores/auth'
import { useDespachosStore, type DespachoActualizadoPayload } from '../stores/despachos'
import { useThemeStore } from '../stores/theme'
import { useEcho } from '../composables/useHecho.ts'
import ConfirmModal from '../components/ConfirmModal.vue'
import Breadcrumbs from '../components/Breadcrumbs.vue'
import NotificationBell from '../components/NotificationBell.vue'
import GlobalSearch from '../components/GlobalSearch.vue'

const globalSearch = ref<InstanceType<typeof GlobalSearch> | null>(null)
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const despachos = useDespachosStore()
const theme = useThemeStore()

const sidebarOpen = ref(false)
const confirmLogout = ref(false)
const loggingOut = ref(false)

const NAV_ITEMS = [
    { to: '/', label: 'Dashboard', icon: HomeIcon },
    { to: '/negocios', label: 'Negocios', icon: BuildingStorefrontIcon },
    { to: '/motorizados', label: 'Motorizados', icon: UserGroupIcon },
    { to: '/zonas', label: 'Zonas', icon: MapIcon },
    { to: '/despachos', label: 'Despachos', icon: ClipboardDocumentListIcon, badge: () => despachos.stats.total_activos },
    { to: '/comisiones', label: 'Comisiones', icon: BanknotesIcon },
    { to: '/configuracion', label: 'Configuración', icon: Cog6ToothIcon },
]

const TITLES: Record<string, string> = {
    dashboard: 'Dashboard',
    negocios: 'Negocios',
    motorizados: 'Motorizados',
    zonas: 'Zonas',
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
let echo: Echo<"reverb"> | null = null

onMounted(async () => {
    await despachos.fetchAll()
    echo = useEcho()
    echo.channel('admin.despachos')
        .listen('.despacho.actualizado', (data: DespachoActualizadoPayload) => {
            despachos.handleRealtimeUpdate(data)
        })
})

onUnmounted(() => {
    try { echo?.leaveChannel('admin.despachos') } catch { /* noop */ }
})
</script>