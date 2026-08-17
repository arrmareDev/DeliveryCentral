import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";
import type { PaginationMeta } from "./negocios";

export interface MotorizadoStats {
  total_entregas: number;
  entregas_hoy: number;
  deuda_pendiente: number;
}

export interface MotorizadoItem {
  id: number;
  nombre: string;
  nombres?: string;
  apellidos?: string;
  telefono: string;
  email: string;
  foto: string | null;
  estado: "disponible" | "ocupado" | "inactivo";
  verificado: boolean;
  activo: boolean;
  ultimo_ping: string | null;
  stats?: MotorizadoStats;
  dni?: string;
  fecha_nacimiento?: string;
  placa?: string;
  marca_vehiculo?: string;
  modelo_vehiculo?: string;
  anio_vehiculo?: number;
  foto_vehiculo?: string | null;
  soat_numero?: string | null;
  created_at?: string;
}

export interface MotorizadosGlobalStats {
  total: number;
  verificados: number;
  disponibles: number;
  pendientes: number;
}

// Lista liviana para el selector del modal de asignación manual —
// no la tabla paginada completa.
export interface MotorizadoDisponible {
  id: number;
  nombre: string;
  estado: "disponible" | "ocupado" | "inactivo";
  activos: number;
  max: number;
  puede_recibir: boolean;
}

export const useMotorizadosStore = defineStore("motorizados", () => {
  const motorizados = ref<MotorizadoItem[]>([]);
  const meta = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 10,
  });
  const stats = ref<MotorizadosGlobalStats>({
    total: 0,
    verificados: 0,
    disponibles: 0,
    pendientes: 0,
  });
  const loading = ref(false);
  const disponibles = ref<MotorizadoDisponible[]>([]);
  const disponiblesLoading = ref(false);

  async function fetchDisponibles() {
    disponiblesLoading.value = true;
    try {
      const { data } = await api.get("/admin/motorizados/disponibles");
      disponibles.value = data.data;
    } finally {
      disponiblesLoading.value = false;
    }
  }

  async function fetchAll(
    params: {
      buscar?: string;
      filtro_estado?: string;
      page?: number;
    } = {},
  ) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/motorizados", { params });
      motorizados.value = data.data.data;
      meta.value = data.data.meta;
      stats.value = data.data.stats;
    } finally {
      loading.value = false;
    }
  }

  async function toggleVerificado(id: number): Promise<boolean> {
    try {
      const { data } = await api.patch(`/admin/motorizados/${id}/verificar`);
      patch(id, data.data);
      return true;
    } catch {
      return false;
    }
  }

  async function toggleActivo(id: number): Promise<boolean> {
    try {
      const { data } = await api.patch(
        `/admin/motorizados/${id}/toggle-activo`,
      );
      patch(id, data.data);
      return true;
    } catch {
      return false;
    }
  }

  function patch(id: number, payload: Partial<MotorizadoItem>) {
    const idx = motorizados.value.findIndex((m) => m.id === id);
    if (idx !== -1)
      motorizados.value[idx] = { ...motorizados.value[idx], ...payload };
  }

  return {
    motorizados,
    meta,
    stats,
    loading,
    disponibles,
    disponiblesLoading,
    fetchAll,
    fetchDisponibles,
    toggleVerificado,
    toggleActivo,
  };
});
