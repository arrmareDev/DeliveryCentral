<template>
    <div class="flex flex-col gap-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="font-black text-[22px] sm:text-[24px] text-gray-900 dark:text-gray-100 m-0 leading-none"
                    style="font-family:'Plus Jakarta Sans',sans-serif;">
                    Despachos
                </h1>
                <p class="text-[13px] text-gray-400 dark:text-gray-500 mt-1 m-0">Panel en tiempo real de todos tus
                    clientes</p>
            </div>
            <div v-if="tab === 'vivo'" class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span class="text-[12px] text-gray-400 dark:text-gray-500 font-medium hidden sm:inline">En vivo</span>
                <button @click="store.fetchAll()" :disabled="store.loading" class="flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                 bg-white dark:bg-gray-900 text-[12px] font-semibold text-gray-600 dark:text-gray-300 cursor-pointer
                 hover:border-brand-primary hover:text-brand-primary
                 transition-all duration-150 disabled:opacity-50">
                    <ArrowPathIcon class="w-3.5 h-3.5" :class="store.loading ? 'animate-spin' : ''" />
                    Actualizar
                </button>
            </div>
        </div>

        <!-- ══ TABS ══ -->
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl self-start">
            <button @click="tab = 'vivo'" class="px-4 py-2 rounded-lg text-[12.5px] font-bold cursor-pointer
               border-none transition-all duration-150" :class="tab === 'vivo'
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm'
                : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                En vivo
            </button>
            <button @click="tab = 'historial'" class="px-4 py-2 rounded-lg text-[12.5px] font-bold cursor-pointer
               border-none transition-all duration-150" :class="tab === 'historial'
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm'
                : 'bg-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                Historial completo
            </button>
        </div>

        <!-- ══════════════════════════════════════════════════════
             TAB: EN VIVO
             ══════════════════════════════════════════════════════ -->
        <template v-if="tab === 'vivo'">

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Activos</p>
                    <p class="font-black text-[24px] text-brand-primary leading-none m-0">{{
                        store.stats.total_activos }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Entregados hoy</p>
                    <p class="font-black text-[24px] text-green-600 dark:text-green-400 leading-none m-0">{{
                        store.stats.entregados_hoy }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Mot.
                        ocupados</p>
                    <p class="font-black text-[24px] text-amber-600 dark:text-amber-400 leading-none m-0">{{
                        store.stats.motorizados_ocupados }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 transition-colors duration-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                        Mot.
                        disponibles</p>
                    <p class="font-black text-[24px] text-blue-600 dark:text-blue-400 leading-none m-0">{{
                        store.stats.motorizados_disponibles
                    }}</p>
                </div>
            </div>

            <!-- Filtro por negocio -->
            <select v-model="negocioFilter" @change="store.fetchAll(negocioFilter || undefined)" class="self-start px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
             bg-white dark:bg-gray-900 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
             focus:border-brand-primary transition-all duration-200 font-semibold">
                <option :value="undefined">Todos los negocios</option>
                <option v-for="r in negocios.negocios" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>

            <!-- Activos -->
            <div>
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 mb-3">
                    En curso
                    <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-blue-50 text-brand-primary border border-blue-200
                     dark:bg-blue-500/10 dark:border-blue-500/20">
                        {{ store.activos.length }}
                    </span>
                </h2>

                <div v-if="store.loading" class="flex flex-col gap-3">
                    <div v-for="n in 3" :key="n" class="h-32 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                </div>

                <div v-else-if="store.activos.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex flex-col items-center py-12 gap-3 text-gray-400 dark:text-gray-600">
                    <TruckIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
                    <p class="font-bold text-gray-600 dark:text-gray-400 text-[14px] m-0">Sin despachos activos</p>
                </div>

                <div v-else class="flex flex-col gap-3">
                    <TransitionGroup name="despacho">
                        <div v-for="d in store.activos" :key="d.id" @click="openDetalle(d)" class="rounded-2xl border-2 shadow-sm dark:shadow-none overflow-hidden cursor-pointer
                   transition-all duration-150"
                            :class="esVencido(d)
                                ? 'bg-red-50/60 dark:bg-red-500/5 border-red-300 dark:border-red-500/40 hover:border-red-400'
                                : 'bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30'">
                            <div class="p-4">

                                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-black px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800
                               text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 font-mono">
                                            #{{ d.order_id }}
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full
                               bg-purple-50 text-purple-700 border border-purple-200
                               dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20">
                                            {{ d.negocio }}
                                        </span>
                                        <span :class="despachoCls(d.estado)"
                                            class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border">
                                            {{ despachoLabel(d.estado) }}
                                        </span>
                                        <span v-if="esVencido(d)" class="flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full
                                     bg-red-100 text-red-700 border border-red-300
                                     dark:bg-red-500/15 dark:text-red-400 dark:border-red-500/30">
                                            <ExclamationTriangleIcon class="w-3 h-3" />
                                            Sin aceptar hace {{ minutosSinAceptar(d) }} min
                                        </span>
                                    </div>
                                    <span class="text-[11.5px] text-gray-400 dark:text-gray-500">{{
                                        formatFecha(d.solicitado_at) }}</span>
                                </div>


                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <p
                                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                            Cliente</p>
                                        <p class="font-bold text-[14px] text-gray-900 dark:text-gray-100 m-0">{{
                                            d.order?.client_name }}</p>
                                        <p
                                            class="text-[12px] text-gray-500 dark:text-gray-400 m-0 mt-0.5 flex items-center gap-1">
                                            <MapPinIcon class="w-3.5 h-3.5 shrink-0" />
                                            {{ d.order?.address }}, {{ d.order?.district }}
                                        </p>
                                        <div class="flex items-baseline gap-0.5 mt-1.5">
                                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                                            <span class="font-black text-[16px] text-brand-primary leading-none">
                                                {{ d.order?.total.toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <p
                                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                            Motorizado</p>
                                        <div v-if="d.motorizado" class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                text-[13px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                                                {{ d.motorizado.nombre.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-[13px] text-gray-900 dark:text-gray-100 m-0">
                                                    {{
                                                        d.motorizado.nombre }}
                                                </p>
                                                <a :href="`https://wa.me/51${d.motorizado.telefono.replace(/\D/g, '')}`"
                                                    target="_blank"
                                                    class="text-[11.5px] text-green-600 dark:text-green-400 no-underline hover:underline font-medium">
                                                    {{ d.motorizado.telefono }}
                                                </a>
                                            </div>
                                        </div>
                                        <div v-else class="flex flex-col items-start gap-2">
                                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                                <ClockIcon class="w-4 h-4 shrink-0" />
                                                <span class="text-[12.5px] font-medium">Esperando motorizado...</span>
                                            </div>
                                            <button @click.stop="openAsignar(d)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold
                                     border cursor-pointer border-blue-200 text-blue-600 bg-blue-50
                                     hover:bg-blue-100 dark:border-blue-500/20 dark:text-blue-400 dark:bg-blue-500/10 dark:hover:bg-blue-500/20
                                     transition-all duration-150">
                                                <UserPlusIcon class="w-3.5 h-3.5" />
                                                Asignar motorizado
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                    <button @click.stop="askCancel(d)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[12px] font-bold
                         border cursor-pointer border-red-200 text-red-500 bg-red-50
                         hover:bg-red-100 dark:border-red-500/20 dark:text-red-400 dark:bg-red-500/10 dark:hover:bg-red-500/20
                         transition-all duration-150">
                                        <XCircleIcon class="w-3.5 h-3.5" />
                                        Cancelar despacho
                                    </button>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>

            <!-- Entregados hoy -->
            <div>
                <h2 class="font-black text-[15px] text-gray-900 dark:text-gray-100 mb-3">
                    Entregados hoy
                    <span class="ml-2 text-[12px] font-bold px-2 py-0.5 rounded-full
                     bg-green-50 text-green-700 border border-green-200
                     dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                        {{ store.entregadosHoy.length }}
                    </span>
                </h2>

                <div v-if="store.entregadosHoy.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex items-center justify-center py-10 text-gray-400 dark:text-gray-600 text-[13px]">
                    Sin entregas completadas hoy
                </div>

                <div v-else class="flex flex-col gap-2">
                    <div v-for="d in store.entregadosHoy" :key="d.id" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none p-4
                 flex items-center gap-4 transition-colors duration-200">
                        <div class="w-10 h-10 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20
                      flex items-center justify-center shrink-0">
                            <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-mono font-black text-gray-500 dark:text-gray-400">#{{
                                    d.order_id }}</span>
                                <span class="font-bold text-[13px] text-gray-900 dark:text-gray-100 truncate">{{
                                    d.order?.client_name }}</span>
                            </div>
                            <p class="text-[12px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">
                                {{ d.negocio }} · {{ d.motorizado?.nombre ?? '—' }}
                            </p>
                        </div>
                        <div class="flex items-baseline gap-0.5 shrink-0">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">S/</span>
                            <span class="font-black text-[16px] text-green-600 dark:text-green-400 leading-none">
                                {{ d.order?.total.toFixed(2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ══════════════════════════════════════════════════════
             TAB: HISTORIAL COMPLETO
             ══════════════════════════════════════════════════════ -->
        <template v-else>

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                        shadow-sm dark:shadow-none p-4 flex flex-col gap-3 transition-colors duration-200">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <MagnifyingGlassIcon
                            class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                        <input v-model="filtros.buscar" placeholder="Buscar por # de pedido o cliente..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                           bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                           focus:border-brand-primary transition-all duration-150" />
                    </div>

                    <select v-model="filtros.estado" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option value="">Todos los estados</option>
                        <option value="solicitado">Buscando motorizado</option>
                        <option value="aceptado">Asignado</option>
                        <option value="recogido">Recogido</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>

                    <select v-model="filtros.negocio_id" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option :value="undefined">Todos los negocios</option>
                        <option v-for="r in negocios.negocios" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>

                    <select v-model="filtros.motorizado_id" class="px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none cursor-pointer
                       focus:border-brand-primary transition-all duration-200 font-semibold">
                        <option :value="undefined">Todos los motorizados</option>
                        <option v-for="m in motorizados.motorizados" :key="m.id" :value="m.id">
                            {{ m.nombre }}{{ m.placa ? ` · ${m.placa}` : '' }}
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input v-model="filtros.desde" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                       outline-none focus:border-brand-primary transition-all duration-150" />
                    <span class="text-gray-400 dark:text-gray-500 text-[12px]">a</span>
                    <input v-model="filtros.hasta" type="date" class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700
                       bg-white dark:bg-gray-800 text-[12.5px] text-gray-700 dark:text-gray-300
                       outline-none focus:border-brand-primary transition-all duration-150" />
                    <button @click="limpiarFiltros" class="px-3.5 py-2 rounded-xl text-[12.5px] font-bold cursor-pointer
                       border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                       text-gray-600 dark:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600
                       transition-all duration-150">
                        Limpiar filtros
                    </button>
                    <ExportButtons endpoint="/admin/reportes/despachos" :params="{
                        buscar: filtros.buscar || undefined,
                        estado: filtros.estado || undefined,
                        negocio_id: filtros.negocio_id,
                        motorizado_id: filtros.motorizado_id,
                        desde: filtros.desde || undefined,
                        hasta: filtros.hasta || undefined,
                    }" filename="despachos" />
                </div>
            </div>

            <!-- Skeleton -->
            <div v-if="store.historialLoading" class="flex flex-col gap-2">
                <div v-for="n in 6" :key="n" class="h-16 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
            </div>

            <!-- Empty -->
            <div v-else-if="store.historial.length === 0" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm dark:shadow-none
               flex flex-col items-center py-16 gap-3 text-gray-400 dark:text-gray-600">
                <ClipboardDocumentListIcon class="w-10 h-10 text-gray-300 dark:text-gray-700" />
                <p class="font-bold text-gray-600 dark:text-gray-400 text-[14px] m-0">Sin resultados con estos
                    filtros</p>
            </div>

            <!-- Tabla -->
            <div v-else class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
                shadow-sm dark:shadow-none overflow-hidden transition-colors duration-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Pedido</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Negocio</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Cliente</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Motorizado</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                    Estado</th>
                                <th
                                    class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-right">
                                    Total</th>
                                <th @click="toggleSort" class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest
                                   text-gray-400 dark:text-gray-500 cursor-pointer select-none whitespace-nowrap
                                   hover:text-gray-600 dark:hover:text-gray-300">
                                    Fecha
                                    <component :is="filtros.sort_dir === 'asc' ? ChevronUpIcon : ChevronDownIcon"
                                        class="w-3 h-3 inline-block ml-0.5" />
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in store.historial" :key="d.id" @click="openDetalle(d)"
                                class="border-b border-gray-50 dark:border-gray-800/60 last:border-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors cursor-pointer">
                                <td
                                    class="px-4 py-3 text-[12.5px] font-mono font-bold text-gray-700 dark:text-gray-300">
                                    #{{ d.order_id }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-600 dark:text-gray-400">{{ d.negocio ??
                                    '—' }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-900 dark:text-gray-100 font-medium">{{
                                    d.order?.client_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-[12.5px] text-gray-600 dark:text-gray-400">{{
                                    d.motorizado?.nombre ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="despachoCls(d.estado)"
                                        class="text-[10.5px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap">
                                        {{ despachoLabel(d.estado) }}
                                    </span>
                                    <span v-if="d.estado === 'cancelado' && d.motivo_cancelacion"
                                        class="block text-[10.5px] text-gray-400 dark:text-gray-500 mt-0.5">
                                        {{ d.motivo_cancelacion }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 text-[12.5px] font-bold text-gray-900 dark:text-gray-100 text-right whitespace-nowrap">
                                    S/ {{ d.order?.total?.toFixed(2) ?? '0.00' }}
                                </td>
                                <td class="px-4 py-3 text-[12px] text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                    {{ formatFecha(d.solicitado_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    <Pagination :meta="store.historialMeta" @change="cambiarPaginaHistorial" />
                </div>
            </div>
        </template>

        <!-- ══ MODAL DETALLE — despacho completo con productos ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="detalleModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                     flex items-center justify-center p-4" @click.self="detalleModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="detalleModal.show && detalleModal.despacho" class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-3xl shadow-2xl
                             max-h-[88vh] flex flex-col overflow-hidden">

                            <div
                                class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-3 shrink-0">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <span class="text-[13px] font-black font-mono px-2 py-0.5 rounded-lg
                                     bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900">
                                            #{{ detalleModal.despacho.order_id }}
                                        </span>
                                        <span :class="despachoCls(detalleModal.despacho.estado)"
                                            class="text-[10.5px] font-bold px-2 py-0.5 rounded-full border">
                                            {{ despachoLabel(detalleModal.despacho.estado) }}
                                        </span>
                                    </div>
                                    <h3 class="font-black text-[17px] text-gray-900 dark:text-gray-100 m-0"
                                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                                        {{ detalleModal.despacho.negocio ?? 'Despacho' }}
                                    </h3>
                                </div>
                                <button @click="detalleModal.show = false"
                                    class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                     cursor-pointer border-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                                    <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto px-6 py-4 flex flex-col gap-4">

                                <!-- Recoger en -->
                                <div v-if="detalleModal.despacho.negocio_direccion"
                                    class="px-3.5 py-2.5 rounded-2xl bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-purple-500/20">
                                    <p
                                        class="text-[10px] font-black uppercase tracking-widest text-purple-500 dark:text-purple-400 m-0 mb-0.5">
                                        Recoger en</p>
                                    <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">{{
                                        detalleModal.despacho.negocio_direccion }}</p>
                                </div>

                                <!-- Cliente -->
                                <div>
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                        Cliente</p>
                                    <p class="font-bold text-[14px] text-gray-900 dark:text-gray-100 m-0">{{
                                        detalleModal.despacho.order?.client_name ?? '—' }}</p>
                                    <a v-if="detalleModal.despacho.order?.client_phone"
                                        :href="`https://wa.me/51${detalleModal.despacho.order.client_phone.replace(/\D/g, '')}`"
                                        target="_blank"
                                        class="text-[12.5px] text-green-600 dark:text-green-400 no-underline hover:underline font-medium">
                                        {{ detalleModal.despacho.order.client_phone }}
                                    </a>
                                    <p
                                        class="text-[12.5px] text-gray-500 dark:text-gray-400 m-0 mt-1 flex items-start gap-1.5">
                                        <MapPinIcon class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                                        {{ detalleModal.despacho.order?.address }}, {{
                                        detalleModal.despacho.order?.district }}
                                    </p>
                                    <p v-if="detalleModal.despacho.order?.reference"
                                        class="text-[12px] text-amber-600 dark:text-amber-400 m-0 mt-0.5 ml-5">
                                        Ref: {{ detalleModal.despacho.order.reference }}
                                    </p>
                                </div>

                                <!-- Productos -->
                                <div>
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                        Productos</p>
                                    <div class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-3">
                                        <div v-for="(item, i) in detalleModal.despacho.order?.items ?? []" :key="i"
                                            class="flex items-center justify-between gap-2 text-[13px]">
                                            <div class="min-w-0">
                                                <p class="m-0 text-gray-900 dark:text-gray-100">{{ item.qty }}x {{
                                                    item.name }}
                                                </p>
                                                <p v-if="item.custom_summary"
                                                    class="m-0 text-[11.5px] text-gray-400 dark:text-gray-500">{{
                                                    item.custom_summary }}</p>
                                            </div>
                                            <span v-if="item.subtotal"
                                                class="font-semibold text-gray-500 dark:text-gray-400 shrink-0">
                                                S/ {{ Number(item.subtotal).toFixed(2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pago -->
                                <div class="flex flex-col gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <div v-if="detalleModal.despacho.order?.subtotal != null"
                                        class="flex items-center justify-between text-[12.5px]">
                                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                        <span class="text-gray-600 dark:text-gray-300">S/ {{
                                            Number(detalleModal.despacho.order.subtotal).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="detalleModal.despacho.order?.delivery_fee != null"
                                        class="flex items-center justify-between text-[12.5px]">
                                        <span class="text-gray-500 dark:text-gray-400">Delivery</span>
                                        <span class="text-gray-600 dark:text-gray-300">S/ {{
                                            Number(detalleModal.despacho.order.delivery_fee).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-[13px] font-bold text-gray-900 dark:text-gray-100">Total</span>
                                        <span class="font-black text-[16px] text-gray-900 dark:text-gray-100">S/ {{
                                            detalleModal.despacho.order?.total?.toFixed(2) ?? '0.00' }}</span>
                                    </div>
                                    <span
                                        class="self-start text-[10.5px] font-bold px-2 py-0.5 rounded-full border mt-1"
                                        :class="detalleModal.despacho.order?.pagado
                                            ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20'
                                            : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'">
                                        {{ detalleModal.despacho.order?.pagado ? 'Ya pagado' : 'Por cobrar' }}
                                    </span>
                                </div>

                                <!-- Motorizado -->
                                <div v-if="detalleModal.despacho.motorizado"
                                    class="pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <p
                                        class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                        Motorizado</p>
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                text-[13px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                                            {{ detalleModal.despacho.motorizado.nombre.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[13px] text-gray-900 dark:text-gray-100 m-0">{{
                                                detalleModal.despacho.motorizado.nombre }}</p>
                                            <a :href="`https://wa.me/51${detalleModal.despacho.motorizado.telefono.replace(/\D/g, '')}`"
                                                target="_blank"
                                                class="text-[11.5px] text-green-600 dark:text-green-400 no-underline hover:underline font-medium">
                                                {{ detalleModal.despacho.motorizado.telefono }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Línea de tiempo -->
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 flex flex-col gap-1">
                                    <div class="flex items-center justify-between text-[11.5px]">
                                        <span class="text-gray-400 dark:text-gray-500">Solicitado</span>
                                        <span class="text-gray-600 dark:text-gray-300">{{
                                            formatFecha(detalleModal.despacho.solicitado_at) }}</span>
                                    </div>
                                    <div v-if="detalleModal.despacho.aceptado_at"
                                        class="flex items-center justify-between text-[11.5px]">
                                        <span class="text-gray-400 dark:text-gray-500">Aceptado</span>
                                        <span class="text-gray-600 dark:text-gray-300">{{
                                            formatFecha(detalleModal.despacho.aceptado_at) }}</span>
                                    </div>
                                    <div v-if="detalleModal.despacho.recogido_at"
                                        class="flex items-center justify-between text-[11.5px]">
                                        <span class="text-gray-400 dark:text-gray-500">Recogido</span>
                                        <span class="text-gray-600 dark:text-gray-300">{{
                                            formatFecha(detalleModal.despacho.recogido_at) }}</span>
                                    </div>
                                    <div v-if="detalleModal.despacho.entregado_at"
                                        class="flex items-center justify-between text-[11.5px]">
                                        <span class="text-gray-400 dark:text-gray-500">Entregado</span>
                                        <span class="text-gray-600 dark:text-gray-300">{{
                                            formatFecha(detalleModal.despacho.entregado_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ MODAL ASIGNAR MOTORIZADO ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200"
                leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0"
                leave-to-class="opacity-0">
                <div v-if="asignarModal.show" class="fixed inset-0 z-[500] bg-black/50 backdrop-blur-sm
                     flex items-center justify-center p-4" @click.self="asignarModal.show = false">
                    <Transition enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
                        <div v-if="asignarModal.show && asignarModal.despacho" class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-3xl shadow-2xl
                             max-h-[80vh] flex flex-col overflow-hidden">

                            <div
                                class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-3 shrink-0">
                                <div>
                                    <h3 class="font-black text-[17px] text-gray-900 dark:text-gray-100 m-0"
                                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                                        Asignar motorizado
                                    </h3>
                                    <p class="text-[12.5px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">
                                        Pedido #{{ asignarModal.despacho.order_id }} · {{ asignarModal.despacho.negocio
                                        }}
                                    </p>
                                </div>
                                <button @click="asignarModal.show = false"
                                    class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                     cursor-pointer border-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors shrink-0">
                                    <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto px-4 py-3">
                                <div v-if="motorizados.disponiblesLoading" class="flex flex-col gap-2">
                                    <div v-for="n in 3" :key="n"
                                        class="h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                                </div>

                                <div v-else-if="motorizados.disponibles.length === 0" class="text-center py-8">
                                    <p class="text-[13px] text-gray-400 dark:text-gray-500 m-0">
                                        No hay motorizados verificados y activos disponibles.
                                    </p>
                                </div>

                                <div v-else class="flex flex-col gap-2">
                                    <button v-for="m in motorizados.disponibles" :key="m.id"
                                        @click="m.puede_recibir && confirmarAsignar(m.id)" :disabled="!m.puede_recibir"
                                        class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition-all duration-150"
                                        :class="m.puede_recibir
                                            ? 'border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 cursor-pointer hover:border-blue-300 dark:hover:border-blue-500/40'
                                            : 'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 cursor-not-allowed opacity-60'">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                                    text-[13px] font-black text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shrink-0">
                                            {{ m.nombre.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="font-bold text-[13.5px] text-gray-900 dark:text-gray-100 m-0 truncate">
                                                {{
                                                m.nombre }}</p>
                                            <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0">
                                                {{ m.activos }}/{{ m.max }} pedidos activos
                                                <span v-if="!m.puede_recibir" class="text-red-500 dark:text-red-400">·
                                                    al
                                                    máximo</span>
                                            </p>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
                                            :class="m.estado === 'disponible'
                                                ? 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20'
                                                : 'bg-gray-100 text-gray-500 border border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700'">
                                            {{ m.estado }}
                                        </span>
                                    </button>
                                </div>

                                <p v-if="asignarModal.error"
                                    class="text-[12.5px] text-red-600 dark:text-red-400 text-center mt-3 mb-1">
                                    {{ asignarModal.error }}
                                </p>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <CancelarDespachoModal v-model="confirmCancel.show"
            :message="`El despacho del pedido #${confirmCancel.target?.order_id} de ${confirmCancel.target?.negocio} será cancelado y el motorizado quedará disponible nuevamente.`"
            :loading="confirmCancel.loading" @confirm="executeCancel" />
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import type Echo from 'laravel-echo'
import {
    TruckIcon, MapPinIcon, ClockIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon,
    MagnifyingGlassIcon, ClipboardDocumentListIcon, ChevronUpIcon, ChevronDownIcon, XMarkIcon,
    UserPlusIcon, ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'
import { useDespachosStore, type DespachoItem, type DespachoActualizadoPayload } from '../stores/despachos'
import { useNegociosStore } from '../stores/negocios'
import { useMotorizadosStore } from '../stores/motorizados'
import { useEcho } from '../composables/useHecho.ts'
import { useToastStore } from '../stores/toast'
import CancelarDespachoModal from '../components/CancelarDespachoModal.vue'
import Pagination from '../components/Pagination.vue'
import ExportButtons from '../components/ExportButtons.vue'

const store = useDespachosStore()
const negocios = useNegociosStore()
const motorizados = useMotorizadosStore()
const toast = useToastStore()
const negocioFilter = ref<number | undefined>(undefined)

// ── Modal de detalle ───────────────────────────────────────
const detalleModal = reactive({ show: false, despacho: null as DespachoItem | null })

function openDetalle(d: DespachoItem) {
    detalleModal.despacho = d
    detalleModal.show = true
}

// ── Modal de asignar motorizado ─────────────────────────────
const asignarModal = reactive({ show: false, despacho: null as DespachoItem | null, error: '' })

async function openAsignar(d: DespachoItem) {
    asignarModal.despacho = d
    asignarModal.error = ''
    asignarModal.show = true
    await motorizados.fetchDisponibles()
}

async function confirmarAsignar(motorizadoId: number) {
    if (!asignarModal.despacho) return
    asignarModal.error = ''
    const result = await store.asignar(asignarModal.despacho.id, motorizadoId)
    if (result.ok) {
        asignarModal.show = false
        toast.success('Motorizado asignado')
    } else {
        asignarModal.error = result.message ?? 'No se pudo asignar el pedido'
    }
}

// ── Tabs ───────────────────────────────────────────────────
const tab = ref<'vivo' | 'historial'>('vivo')

// ── Historial: filtros + paginación ───────────────────────
const filtros = reactive({
    buscar: '',
    estado: '',
    negocio_id: undefined as number | undefined,
    motorizado_id: undefined as number | undefined,
    desde: '',
    hasta: '',
    sort_dir: 'desc' as 'asc' | 'desc',
    page: 1,
})
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function cargarHistorial() {
    store.fetchHistorial({
        buscar: filtros.buscar || undefined,
        estado: filtros.estado || undefined,
        negocio_id: filtros.negocio_id,
        motorizado_id: filtros.motorizado_id,
        desde: filtros.desde || undefined,
        hasta: filtros.hasta || undefined,
        sort_dir: filtros.sort_dir,
        page: filtros.page,
    })
}

function cambiarPaginaHistorial(p: number) {
    filtros.page = p
    cargarHistorial()
}

function toggleSort() {
    filtros.sort_dir = filtros.sort_dir === 'asc' ? 'desc' : 'asc'
    filtros.page = 1
    cargarHistorial()
}

function limpiarFiltros() {
    Object.assign(filtros, {
        buscar: '', estado: '', negocio_id: undefined, motorizado_id: undefined,
        desde: '', hasta: '', sort_dir: 'desc', page: 1,
    })
    cargarHistorial()
}

watch(() => filtros.buscar, () => {
    filtros.page = 1
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(cargarHistorial, 350)
})

watch(() => [filtros.estado, filtros.negocio_id, filtros.motorizado_id, filtros.desde, filtros.hasta], () => {
    filtros.page = 1
    cargarHistorial()
})

watch(tab, (t) => {
    if (t === 'historial' && store.historial.length === 0) cargarHistorial()
    // Necesitamos el listado de motorizados cargado para el <select> del filtro
    if (t === 'historial' && motorizados.motorizados.length === 0) motorizados.fetchAll()
})

let echo: Echo<"reverb"> | null = null

onMounted(async () => {
    await Promise.all([store.fetchAll(), negocios.fetchAll()])
    echo = useEcho()
    echo.channel('admin.despachos')
        .listen('.despacho.actualizado', (data: DespachoActualizadoPayload) => store.handleRealtimeUpdate(data))

    // Reloj en vivo — sin esto, el resaltado en rojo a los 3 minutos
    // solo se actualizaría cuando algo más recargue la página.
    relojInterval = setInterval(() => { ahora.value = Date.now() }, 15_000)
})

onUnmounted(() => {
    try { echo?.leaveChannel('admin.despachos') } catch { /* noop */ }
    if (relojInterval) clearInterval(relojInterval)
})

// ── Alerta visual de pedidos sin aceptar (3+ minutos) ──────
const ahora = ref(Date.now())
let relojInterval: ReturnType<typeof setInterval> | null = null

function minutosSinAceptar(d: DespachoItem): number {
    if (!d.solicitado_at) return 0
    return Math.floor((ahora.value - new Date(d.solicitado_at).getTime()) / 60_000)
}

function esVencido(d: DespachoItem): boolean {
    return d.estado === 'solicitado' && minutosSinAceptar(d) >= 3
}

function despachoLabel(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'Buscando motorizado', aceptado: 'Motorizado asignado',
        recogido: 'Recogido en local', entregado: 'Entregado', cancelado: 'Cancelado',
    }
    return m[s] ?? s
}

function despachoCls(s: string): string {
    const m: Record<string, string> = {
        solicitado: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
        aceptado: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
        recogido: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20',
        entregado: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20',
        cancelado: 'bg-gray-100 text-gray-500 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700',
    }
    return m[s] ?? m.cancelado
}

function formatFecha(d: string | null): string {
    if (!d) return '—'
    return new Date(d).toLocaleString('es-PE', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: 'short' })
}

const confirmCancel = reactive({ show: false, loading: false, target: null as DespachoItem | null })

function askCancel(d: DespachoItem) {
    confirmCancel.target = d
    confirmCancel.show = true
}

async function executeCancel(motivo: string) {
    if (!confirmCancel.target) return
    confirmCancel.loading = true
    const ok = await store.cancelar(confirmCancel.target.id, motivo)
    confirmCancel.loading = false
    confirmCancel.show = false
    ok ? toast.success('Despacho cancelado') : toast.error('Error al cancelar el despacho')
}
</script>

<style scoped>
.despacho-enter-active {
    transition: all 0.3s ease;
}

.despacho-leave-active {
    transition: all 0.2s ease;
}

.despacho-enter-from {
    opacity: 0;
    transform: translateY(-8px);
}

.despacho-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>