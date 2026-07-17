import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";
import type { PaginationMeta } from "./restaurants";

export interface ComisionResumen {
  id: number;
  nombre: string;
  telefono: string;
  deuda_pendiente: number;
  total_cobrado: number;
  comisiones_pendientes?: number;
}

export interface ComisionDetalleItem {
  id: number;
  despacho_id: number;
  order_id: number;
  restaurant: string | null;
  monto: number;
  estado: "pendiente" | "cobrado";
  cobrado_at: string | null;
  created_at: string;
}

export interface RangoParams {
  preset?: string;
  desde?: string;
  hasta?: string;
  page?: number;
}

export const useComisionesStore = defineStore("comisiones", () => {
  const resumen = ref<ComisionResumen[]>([]);
  const detalle = ref<ComisionDetalleItem[]>([]);
  const detalleMeta = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15,
  });
  const motorizadoActual = ref<{ id: number; nombre: string } | null>(null);
  const deudaPendiente = ref(0);
  const deudaTotalHistorica = ref(0);
  const loading = ref(false);
  const comisionPorEntrega = ref(0.5);

  async function fetchResumen(rango: RangoParams = {}) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/comisiones", { params: rango });
      resumen.value = data.data.motorizados ?? data.data;
    } finally {
      loading.value = false;
    }
  }

  async function fetchDetalle(motorizadoId: number, rango: RangoParams = {}) {
    loading.value = true;
    try {
      const { data } = await api.get(`/admin/comisiones/${motorizadoId}`, {
        params: rango,
      });
      motorizadoActual.value = data.data.motorizado;
      deudaPendiente.value = data.data.deuda_pendiente;
      deudaTotalHistorica.value =
        data.data.deuda_total ?? data.data.deuda_pendiente;
      detalle.value = data.data.comisiones;
      detalleMeta.value = data.data.meta;
    } finally {
      loading.value = false;
    }
  }

  async function cobrar(
    motorizadoId: number,
    rango: RangoParams = {},
    comisionIds?: number[],
  ): Promise<{ ok: boolean; total?: number; count?: number }> {
    try {
      const { data } = await api.post("/admin/comisiones/cobrar", {
        motorizado_id: motorizadoId,
        comision_ids: comisionIds,
        ...rango,
      });
      await fetchDetalle(motorizadoId, rango);
      await fetchResumen(rango);
      return {
        ok: true,
        total: data.data.total_cobrado,
        count: data.data.comisiones_cobradas,
      };
    } catch {
      return { ok: false };
    }
  }

  async function fetchConfig() {
    try {
      const { data } = await api.get("/admin/config");
      comisionPorEntrega.value = data.data.comision_por_entrega;
    } catch {
      /* noop */
    }
  }

  async function updateConfig(monto: number): Promise<boolean> {
    try {
      await api.put("/admin/config", { comision_por_entrega: monto });
      comisionPorEntrega.value = monto;
      return true;
    } catch {
      return false;
    }
  }

  return {
    resumen,
    detalle,
    detalleMeta,
    motorizadoActual,
    deudaPendiente,
    deudaTotalHistorica,
    loading,
    comisionPorEntrega,
    fetchResumen,
    fetchDetalle,
    cobrar,
    fetchConfig,
    updateConfig,
  };
});
