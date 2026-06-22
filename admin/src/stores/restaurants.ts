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

export const useRestaurantsStore = defineStore("restaurants", () => {
  const restaurants = ref<Restaurant[]>([]);
  const loading = ref(false);

  async function fetchAll() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/restaurants");
      restaurants.value = data.data;
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
      restaurants.value.unshift(data.data);
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
    loading,
    fetchAll,
    getOne,
    create,
    update,
    regenerateKey,
    remove,
  };
});
