import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";

export interface MotorizadoStats {
  total_entregas: number;
  entregas_hoy: number;
  deuda_pendiente: number;
}

export interface MotorizadoItem {
  id: number;
  nombre: string;
  telefono: string;
  email: string;
  foto: string | null;
  estado: "disponible" | "ocupado" | "inactivo";
  verificado: boolean;
  activo: boolean;
  ultimo_ping: string | null;
  stats?: MotorizadoStats;
}

export const useMotorizadosStore = defineStore("motorizados", () => {
  const motorizados = ref<MotorizadoItem[]>([]);
  const loading = ref(false);

  async function fetchAll() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/motorizados");
      motorizados.value = data.data;
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

  return { motorizados, loading, fetchAll, toggleVerificado, toggleActivo };
});
