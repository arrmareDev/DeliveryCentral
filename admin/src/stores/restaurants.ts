import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";

export interface Restaurant {
  id: number;
  name: string;
  slug: string;
  webhook_url: string | null;
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

export const useRestaurantsStore = defineStore("restaurants", () => {
  const restaurants = ref<Restaurant[]>([]);
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
      const { data } = await api.get("/admin/restaurants", { params });
      restaurants.value = data.data.data;
      meta.value = data.data.meta;
    } finally {
      loading.value = false;
    }
  }

  async function getOne(id: number): Promise<Restaurant | null> {
    try {
      const { data } = await api.get(`/admin/restaurants/${id}`);
      return data.data;
    } catch {
      return null;
    }
  }

  async function create(payload: {
    name: string;
    slug: string;
    webhook_url?: string;
  }): Promise<{ ok: boolean; data?: Restaurant; message?: string }> {
    try {
      const { data } = await api.post("/admin/restaurants", payload);
      return { ok: true, data: data.data };
    } catch (e: any) {
      return {
        ok: false,
        message: e.response?.data?.message ?? "Error al crear restaurante",
      };
    }
  }

  async function update(
    id: number,
    payload: Partial<Restaurant>,
  ): Promise<boolean> {
    try {
      const { data } = await api.put(`/admin/restaurants/${id}`, payload);
      const idx = restaurants.value.findIndex((r) => r.id === id);
      if (idx !== -1)
        restaurants.value[idx] = { ...restaurants.value[idx], ...data.data };
      return true;
    } catch {
      return false;
    }
  }

  async function regenerateKey(id: number): Promise<Restaurant | null> {
    try {
      const { data } = await api.post(
        `/admin/restaurants/${id}/regenerate-key`,
      );
      return data.data;
    } catch {
      return null;
    }
  }

  async function remove(id: number): Promise<boolean> {
    try {
      await api.delete(`/admin/restaurants/${id}`);
      restaurants.value = restaurants.value.filter((r) => r.id !== id);
      return true;
    } catch {
      return false;
    }
  }

  return {
    restaurants,
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
