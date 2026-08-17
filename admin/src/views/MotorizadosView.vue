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
            <ExportButtons endpoint="/admin/reportes/motorizados" :params="{
                buscar: buscar || undefined,
                filtro_estado: filtro || undefined,
            }" filename="motorizados" />
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

        <!-- Tabla -->
        <div v-else class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800
             shadow-sm dark:shadow-none overflow-hidden transition-colors duration-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Motorizado</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Contacto</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Vehículo</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Verificación</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Estado</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-right">
                                Entregas</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                Registrado</th>
                            <th
                                class="px-4 py-3 text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in store.motorizados" :key="m.id"
                            class="border-b border-gray-50 dark:border-gray-800/60 last:border-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">

                            <!-- Motorizado (avatar + nombre + DNI) -->
                            <td class="px-4 py-3">
                                <button @click="openDetalle(m)"
                                    class="flex items-center gap-2.5 bg-transparent border-none p-0 cursor-pointer text-left">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-800 shrink-0 flex items-center
                                                justify-center overflow-hidden border border-gray-200 dark:border-gray-700">
                                        <img v-if="m.foto" :src="m.foto" class="w-full h-full object-cover" />
                                        <span v-else class="text-[13px] font-black text-gray-400 dark:text-gray-500">
                                            {{ m.nombre.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <p
                                            class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0 hover:underline whitespace-nowrap">
                                            {{ m.nombre }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 m-0 font-mono">
                                            DNI {{ m.dni ?? '—' }}
                                        </p>
                                    </div>
                                </button>
                            </td>

                            <!-- Contacto -->
                            <td class="px-4 py-3">
                                <p class="text-[12px] text-gray-600 dark:text-gray-400 m-0 whitespace-nowrap">{{
                                    m.telefono }}</p>
                                <p class="text-[11.5px] text-gray-400 dark:text-gray-500 m-0 truncate max-w-[160px]">{{
                                    m.email }}</p>
                            </td>

                            <!-- Vehículo -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span v-if="m.placa" class="text-[10.5px] font-black font-mono px-1.5 py-0.5 rounded-md
                                             bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900">
                                    {{ m.placa }}
                                </span>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 m-0 mt-0.5">
                                    {{ m.marca_vehiculo ?? '—' }} {{ m.modelo_vehiculo ?? '' }}
                                </p>
                            </td>

                            <!-- Verificación -->
                            <td class="px-4 py-3">
                                <span v-if="m.verificado" class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap
                         bg-green-50 text-green-700 border border-green-200
                         dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20">
                                    ✓ Verificado
                                </span>
                                <span v-else class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap
                         bg-amber-50 text-amber-700 border border-amber-200
                         dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20">
                                    ⏳ Pendiente
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="px-4 py-3">
                                <span :class="estadoCls(m.estado)"
                                    class="text-[10px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap">
                                    {{ estadoLabel(m.estado) }}
                                </span>
                            </td>

                            <!-- Entregas -->
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <p class="text-[13px] font-bold text-gray-900 dark:text-gray-100 m-0">{{
                                    m.stats?.total_entregas ?? '—' }}</p>
                                <p v-if="m.stats" class="text-[11px] text-purple-500 dark:text-purple-400 m-0">
                                    Debe S/ {{ m.stats.deuda_pendiente.toFixed(2) }}
                                </p>
                            </td>

                            <!-- Registrado -->
                            <td class="px-4 py-3 text-[12px] text-gray-400 dark:text-gray-500 whitespace-nowrap">
                                {{ formatFecha(m.created_at) }}
                            </td>

                            <!-- Acciones -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <button @click="askToggleVerificado(m)"
                                        :title="m.verificado ? 'Quitar verificación' : 'Verificar'"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150"
                                        :class="m.verificado
                                            ? 'bg-red-50 border-red-200 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400'
                                            : 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-400'">
                                        <CheckCircleIcon class="w-4 h-4" />
                                    </button>
                                    <button v-if="m.verificado" @click="askToggleActivo(m)"
                                        :title="m.activo ? 'Desactivar' : 'Activar'"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center border cursor-pointer transition-all duration-150"
                                        :class="m.activo
                                            ? 'bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300'
                                            : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400'">
                                        <component :is="m.activo ? NoSymbolIcon : CheckIcon" class="w-4 h-4" />
                                    </button>
                                    <a :href="`https://wa.me/51${m.telefono.replace(/\D/g, '')}`" target="_blank"
                                        title="WhatsApp" class="w-8 h-8 rounded-lg flex items-center justify-center border no-underline
                                     bg-[#25D366]/10 border-[#25D366]/30 text-[#128C7E]
                                     dark:text-[#25D366] dark:border-[#25D366]/30 dark:bg-[#25D366]/10
                                     hover:bg-[#25D366]/20 transition-all duration-150">
                                        💬
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

                                <!-- Descuentos (faltas) -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <p
                                            class="text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 m-0">
                                            Descuentos aplicados
                                        </p>
                                        <button @click="openDescuento(detalleModal.motorizado)" class="flex items-center gap-1 text-[11.5px] font-bold text-red-600 dark:text-red-400
                                     bg-transparent border-none cursor-pointer hover:underline">
                                            <MinusCircleIcon class="w-3.5 h-3.5" />
                                            Aplicar descuento
                                        </button>
                                    </div>

                                    <div v-if="descuentosLoading" class="flex flex-col gap-1.5">
                                        <div v-for="n in 2" :key="n"
                                            class="h-10 rounded-xl bg-gray-100 dark:bg-gray-800 animate-pulse" />
                                    </div>
                                    <p v-else-if="descuentos.length === 0"
                                        class="text-[12px] text-gray-400 dark:text-gray-500 m-0">
                                        Sin descuentos registrados.
                                    </p>
                                    <div v-else class="flex flex-col gap-1.5">
                                        <div v-for="d in descuentos" :key="d.id" class="flex items-center justify-between gap-2 bg-red-50 dark:bg-red-500/10
                                     border border-red-100 dark:border-red-500/20 rounded-xl px-3 py-2">
                                            <div class="min-w-0">
                                                <p
                                                    class="text-[12.5px] font-semibold text-gray-800 dark:text-gray-200 m-0 truncate">
                                                    {{ d.motivo }}</p>
                                                <p class="text-[10.5px] text-gray-400 dark:text-gray-500 m-0">{{
                                                    formatFecha(d.created_at) }}</p>
                                            </div>
                                            <span
                                                class="text-[13px] font-black text-red-600 dark:text-red-400 shrink-0">
                                                -S/ {{ d.monto.toFixed(2) }}
                                            </span>
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

        <!-- ══ MODAL APLICAR DESCUENTO ══ -->
        <Teleport to="body">
            <div v-if="descuentoModal.show"
                class="fixed inset-0 z-[600] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="descuentoModal.show = false">
                <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-black text-[16px] text-gray-900 dark:text-gray-100 m-0"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">
                            Aplicar descuento
                        </h3>
                        <button @click="descuentoModal.show = false" class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center
                             cursor-pointer border-none hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <XMarkIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                        </button>
                    </div>
                    <p class="text-[12.5px] text-gray-400 dark:text-gray-500 -mt-2 mb-4">
                        A {{ descuentoModal.motorizado?.nombre }}, por faltas u otro motivo.
                    </p>

                    <div class="flex flex-col gap-3">
                        <div>
                            <label
                                class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                Monto (S/) *
                            </label>
                            <input v-model="descuentoForm.monto" type="number" step="0.01" min="0.01" placeholder="0.00"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                                       focus:border-red-400 transition-all duration-150" />
                        </div>
                        <div>
                            <label
                                class="block text-[10.5px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                                Motivo *
                            </label>
                            <input v-model="descuentoForm.motivo" placeholder="Ej: No recogió pedido #12345" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700
                                       bg-white dark:bg-gray-800 text-[13px] text-gray-700 dark:text-gray-300 outline-none
                                       focus:border-red-400 transition-all duration-150" />
                        </div>

                        <p v-if="descuentoModal.error" class="text-[12.5px] text-red-600 dark:text-red-400 m-0">{{
                            descuentoModal.error }}</p>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="descuentoModal.show = false"
                            class="flex-1 py-3 rounded-2xl border-2 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300
                               font-bold text-[13.5px] bg-white dark:bg-gray-900 cursor-pointer hover:border-gray-300 transition-all duration-150">
                            Cancelar
                        </button>
                        <button @click="submitDescuento" :disabled="descuentoModal.loading"
                            class="flex-1 py-3 rounded-2xl bg-red-600 text-white font-bold text-[13.5px]
                               border-none cursor-pointer hover:bg-red-700 disabled:opacity-60 transition-all duration-150">
                            {{ descuentoModal.loading ? 'Aplicando...' : 'Aplicar' }}
                        </button>
                    </div>
                </div>
            </div>
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
    UserGroupIcon, CheckCircleIcon, CheckIcon, NoSymbolIcon,
    MagnifyingGlassIcon, XMarkIcon, MinusCircleIcon,
} from '@heroicons/vue/24/outline'
import { useMotorizadosStore, type MotorizadoItem } from '../stores/motorizados'
import ConfirmModal from '../components/ConfirmModal.vue'
import Pagination from '../components/Pagination.vue'
import ExportButtons from '../components/ExportButtons.vue'
import { useToastStore } from '../stores/toast'
import api from '../utils/api'

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
    fetchDescuentos(m.id)
}

// ── Descuentos (faltas) ──────────────────────────────────
interface Descuento { id: number; monto: number; motivo: string; created_at: string }
const descuentos = ref<Descuento[]>([])
const descuentosLoading = ref(false)

async function fetchDescuentos(motorizadoId: number) {
    descuentosLoading.value = true
    try {
        const { data } = await api.get(`/admin/motorizados/${motorizadoId}/descuentos`)
        descuentos.value = data.data
    } finally {
        descuentosLoading.value = false
    }
}

const descuentoModal = reactive({ show: false, motorizado: null as MotorizadoItem | null, loading: false, error: '' })
const descuentoForm = reactive({ monto: '', motivo: '' })

function openDescuento(m: MotorizadoItem | null) {
    if (!m) return
    descuentoModal.motorizado = m
    descuentoModal.error = ''
    Object.assign(descuentoForm, { monto: '', motivo: '' })
    descuentoModal.show = true
}

async function submitDescuento() {
    if (!descuentoModal.motorizado) return
    const monto = parseFloat(descuentoForm.monto)
    if (!monto || monto <= 0) {
        descuentoModal.error = 'Ingresa un monto válido'
        return
    }
    if (!descuentoForm.motivo.trim()) {
        descuentoModal.error = 'El motivo es obligatorio'
        return
    }

    descuentoModal.loading = true
    descuentoModal.error = ''
    try {
        await api.post(`/admin/motorizados/${descuentoModal.motorizado.id}/descuentos`, {
            monto,
            motivo: descuentoForm.motivo.trim(),
        })
        descuentoModal.show = false
        toast.success('Descuento aplicado')
        await fetchDescuentos(descuentoModal.motorizado.id)
        store.fetchAll({ buscar: buscar.value || undefined, filtro_estado: filtro.value || undefined, page: page.value })
    } catch {
        descuentoModal.error = 'No se pudo aplicar el descuento'
    } finally {
        descuentoModal.loading = false
    }
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