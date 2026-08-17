import { defineStore } from "pinia";
import { ref } from "vue";
import { isAxiosError } from "axios";
import api from "../utils/api";
import type { PaginationMeta } from "./negocios";

// Coincide exacto con DespachoActualizado::broadcastWith() en el backend.
export interface DespachoActualizadoPayload {
  despacho_id: number;
  order_id: number;
  estado: string;
  aceptado_at: string | null;
  recogido_at: string | null;
  entregado_at: string | null;
  monto_cobrado: number | null;
  motorizado: {
    id: number;
    nombre: string;
    telefono: string;
    lat: number | null;
    lng: number | null;
  } | null;
}

export interface DespachoOrder {
  client_name: string | null;
  client_phone: string | null;
  address: string | null;
  district: string | null;
  reference: string | null;
  subtotal?: number | null;
  delivery_fee?: number | null;
  total: number;
  metodo_pago: string | null;
  pagado?: boolean | null;
  lat: number | null;
  lng: number | null;
  note: string | null;
  items: Array<{
    name?: string;
    qty?: number;
    unit_price?: number;
    subtotal?: number;
    custom_summary?: string | null;
  }>;
}

export interface DespachoItem {
  id: number;
  negocio_id: number;
  negocio: string | null;
  negocio_direccion?: string | null;
  negocio_lat?: number | null;
  negocio_lng?: number | null;
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

  async function asignar(
    id: number,
    motorizadoId: number,
  ): Promise<{ ok: boolean; message?: string }> {
    try {
      const { data } = await api.post(`/admin/despachos/${id}/asignar`, {
        motorizado_id: motorizadoId,
      });
      const idx = activos.value.findIndex((d) => d.id === id);
      if (idx !== -1) activos.value[idx] = data.data;
      return { ok: true };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "No se pudo asignar el pedido")
        : "No se pudo asignar el pedido";
      return { ok: false, message };
    }
  }

  function handleRealtimeUpdate(payload: DespachoActualizadoPayload) {
    if (payload.estado === "entregado" || payload.estado === "cancelado") {
      activos.value = activos.value.filter((d) => d.id !== payload.despacho_id);
      stats.value.total_activos = activos.value.length;
      if (payload.estado === "entregado") fetchAll();
    } else {
      const idx = activos.value.findIndex((d) => d.id === payload.despacho_id);
      if (idx !== -1) {
        // El payload del WebSocket trae lat/lng (para ubicación en vivo) pero
        // no foto — mientras que el motorizado de DespachoItem sí la trae.
        // Se arma el objeto explícito para no perder la foto que ya teníamos.
        activos.value[idx] = {
          ...activos.value[idx],
          estado: payload.estado,
          motorizado: payload.motorizado
            ? {
                id: payload.motorizado.id,
                nombre: payload.motorizado.nombre,
                telefono: payload.motorizado.telefono,
                foto: activos.value[idx].motorizado?.foto ?? null,
              }
            : activos.value[idx].motorizado,
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
    asignar,
    handleRealtimeUpdate,
  };
});
