import { defineStore } from "pinia";
import { ref } from "vue";
import { isAxiosError } from "axios";
import api from "../utils/api";

export interface Negocio {
  id: number;
  name: string;
  slug: string;
  webhook_url: string | null;
  direccion: string | null;
  lat: number | null;
  lng: number | null;
  activo: boolean;
  total_despachos: number;
  created_at: string;
  api_key?: string;
  webhook_secret?: string;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  total: number;
  per_page: number;
}

export const useNegociosStore = defineStore("negocios", () => {
  const negocios = ref<Negocio[]>([]);
  const meta = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 12,
  });
  const loading = ref(false);

  async function fetchAll(
    params: {
      buscar?: string;
      page?: number;
      sort_by?: string;
      sort_dir?: "asc" | "desc";
    } = {},
  ) {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/negocios", { params });
      negocios.value = data.data.data;
      meta.value = data.data.meta;
    } finally {
      loading.value = false;
    }
  }

  async function getOne(id: number): Promise<Negocio | null> {
    try {
      const { data } = await api.get(`/admin/negocios/${id}`);
      return data.data;
    } catch {
      return null;
    }
  }

  async function create(payload: {
    name: string;
    slug: string;
    webhook_url?: string;
    direccion?: string;
    lat?: number;
    lng?: number;
  }): Promise<{ ok: boolean; data?: Negocio; message?: string }> {
    try {
      const { data } = await api.post("/admin/negocios", payload);
      return { ok: true, data: data.data };
    } catch (e: unknown) {
      const message = isAxiosError<{ message?: string }>(e)
        ? (e.response?.data?.message ?? "Error al crear negocio")
        : "Error al crear negocio";
      return { ok: false, message };
    }
  }

  async function update(
    id: number,
    payload: Partial<Negocio>,
  ): Promise<boolean> {
    try {
      const { data } = await api.put(`/admin/negocios/${id}`, payload);
      const idx = negocios.value.findIndex((n) => n.id === id);
      if (idx !== -1)
        negocios.value[idx] = { ...negocios.value[idx], ...data.data };
      return true;
    } catch {
      return false;
    }
  }

  async function regenerateKey(id: number): Promise<Negocio | null> {
    try {
      const { data } = await api.post(`/admin/negocios/${id}/regenerate-key`);
      return data.data;
    } catch {
      return null;
    }
  }

  async function remove(id: number): Promise<boolean> {
    try {
      await api.delete(`/admin/negocios/${id}`);
      negocios.value = negocios.value.filter((n) => n.id !== id);
      return true;
    } catch {
      return false;
    }
  }

  return {
    negocios,
    meta,
    loading,
    fetchAll,
    getOne,
    create,
    update,
    regenerateKey,
    remove,
  };
});
