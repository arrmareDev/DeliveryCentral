import { defineStore } from "pinia";
import { ref } from "vue";
import { isAxiosError } from "axios";
import api from "../utils/api";

export interface ZonaMotorizado {
  id: number;
  nombre: string;
  telefono: string;
  estado: "disponible" | "ocupado" | "inactivo";
  verificado: boolean;
  activo: boolean;
}

export interface Zona {
  id: number;
  nombre: string;
  descripcion: string | null;
  activo: boolean;
  total_motorizados: number;
  created_at: string;
  motorizados?: ZonaMotorizado[];
}

export const useZonasStore = defineStore("zonas", () => {
  const zonas = ref<Zona[]>([]);
  const loading = ref(false);

  async function fetchAll() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/zonas");
      zonas.value = data.data;
    } finally {
      loading.value = false;
    }
  }

  async function fetchOne(id: number): Promise<Zona | null> {
    try {
      const { data } = await api.get(`/admin/zonas/${id}`);
      return data.data;
    } catch {
      return null;
    }
  }

  async function create(payload: {
    nombre: string;
    descripcion?: string;
  }): Promise<{ ok: boolean; data?: Zona; message?: string }> {
    try {
      const { data } = await api.post("/admin/zonas", payload);
      return { ok: true, data: data.data };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "Error al crear la zona")
        : "Error al crear la zona";
      return { ok: false, message };
    }
  }

  async function update(
    id: number,
    payload: Partial<Pick<Zona, "nombre" | "descripcion" | "activo">>,
  ): Promise<boolean> {
    try {
      const { data } = await api.put(`/admin/zonas/${id}`, payload);
      const idx = zonas.value.findIndex((z) => z.id === id);
      if (idx !== -1) zonas.value[idx] = data.data;
      return true;
    } catch {
      return false;
    }
  }

  async function remove(id: number): Promise<boolean> {
    try {
      await api.delete(`/admin/zonas/${id}`);
      zonas.value = zonas.value.filter((z) => z.id !== id);
      return true;
    } catch {
      return false;
    }
  }

  async function sincronizarMotorizados(
    id: number,
    motorizadoIds: number[],
  ): Promise<{ ok: boolean; data?: Zona; message?: string }> {
    try {
      const { data } = await api.post(`/admin/zonas/${id}/motorizados`, {
        motorizado_ids: motorizadoIds,
      });
      const idx = zonas.value.findIndex((z) => z.id === id);
      if (idx !== -1) zonas.value[idx] = data.data;
      return { ok: true, data: data.data };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "No se pudo actualizar")
        : "No se pudo actualizar";
      return { ok: false, message };
    }
  }

  return {
    zonas,
    loading,
    fetchAll,
    fetchOne,
    create,
    update,
    remove,
    sincronizarMotorizados,
  };
});
