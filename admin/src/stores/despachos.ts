import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";
import type { PaginationMeta } from "./negocios";

export interface DespachoOrder {
  client_name: string | null;
  client_phone: string | null;
  address: string | null;
  district: string | null;
  reference: string | null;
  total: number;
  metodo_pago: string | null;
  lat: number | null;
  lng: number | null;
  note: string | null;
  items: Array<{ name?: string; qty?: number }>;
}

export interface DespachoItem {
  id: number;
  negocio_id: number;
  negocio: string | null;
  order_id: number;
  estado: string;
  motivo_cancelacion?: string | null;
  comision_motorizado: number;
  monto_cobrado: number | null;
  nota_motorizado: string | null;
  solicitado_at: string | null;
  aceptado_at: string | null;
  recogido_at: string | null;
  entregado_at: string | null;
  order: DespachoOrder;
  motorizado: {
    id: number;
    nombre: string;
    telefono: string;
    foto: string | null;
  } | null;
}

interface Stats {
  total_activos: number;
  entregados_hoy: number;
  motorizados_ocupados: number;
  motorizados_disponibles: number;
}

export interface HistorialParams {
  desde?: string;
  hasta?: string;
  estado?: string;
  negocio_id?: number;
  motorizado_id?: number;
  buscar?: string;
  sort_dir?: "asc" | "desc";
  page?: number;
}

export const useDespachosStore = defineStore("despachos", () => {
  const activos = ref<DespachoItem[]>([]);
  const entregadosHoy = ref<DespachoItem[]>([]);
  const stats = ref<Stats>({
    total_activos: 0,
    entregados_hoy: 0,
    motorizados_ocupados: 0,
    motorizados_disponibles: 0,
  });
  const loading = ref(false);

  // ── Historial (nuevo) ─────────────────────────────────────
  const historial = ref<DespachoItem[]>([]);
  const historialMeta = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const historialLoading = ref(false);

  async function fetchAll(negocioId?: number) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/despachos", {
        params: negocioId ? { negocio_id: negocioId } : {},
      });
      activos.value = data.data.activos;
      entregadosHoy.value = data.data.entregados_hoy;
      stats.value = data.data.stats;
    } finally {
      loading.value = false;
    }
  }

  async function fetchHistorial(params: HistorialParams = {}) {
    historialLoading.value = true;
    try {
      const { data } = await api.get("/admin/despachos/historial", { params });
      historial.value = data.data.data;
      historialMeta.value = data.data.meta;
    } finally {
      historialLoading.value = false;
    }
  }

  async function cancelar(id: number, motivo: string): Promise<boolean> {
    try {
      await api.post(`/admin/despachos/${id}/cancelar`, { motivo });
      activos.value = activos.value.filter((d) => d.id !== id);
      stats.value.total_activos = activos.value.length;
      return true;
    } catch {
      return false;
    }
  }

  function handleRealtimeUpdate(payload: any) {
    if (payload.estado === "entregado" || payload.estado === "cancelado") {
      activos.value = activos.value.filter((d) => d.id !== payload.despacho_id);
      stats.value.total_activos = activos.value.length;
      if (payload.estado === "entregado") fetchAll();
    } else {
      const idx = activos.value.findIndex((d) => d.id === payload.despacho_id);
      if (idx !== -1) {
        activos.value[idx] = {
          ...activos.value[idx],
          estado: payload.estado,
          motorizado: payload.motorizado ?? activos.value[idx].motorizado,
        };
      } else {
        fetchAll();
      }
    }
  }

  return {
    activos,
    entregadosHoy,
    stats,
    loading,
    historial,
    historialMeta,
    historialLoading,
    fetchAll,
    fetchHistorial,
    cancelar,
    handleRealtimeUpdate,
  };
});
