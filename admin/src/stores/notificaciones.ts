import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";

export interface Notificacion {
  id: number;
  tipo: string;
  titulo: string;
  mensaje: string;
  data: Record<string, any> | null;
  leido: boolean;
  created_at: string;
}

export const useNotificacionesStore = defineStore("notificaciones", () => {
  const notificaciones = ref<Notificacion[]>([]);
  const noLeidas = ref(0);
  const loading = ref(false);

  async function fetchAll() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/notificaciones");
      notificaciones.value = data.data.data;
      noLeidas.value = data.data.no_leidas;
    } finally {
      loading.value = false;
    }
  }

  async function marcarLeida(id: number) {
    try {
      await api.patch(`/admin/notificaciones/${id}/leer`);
      const item = notificaciones.value.find((n) => n.id === id);
      if (item && !item.leido) {
        item.leido = true;
        noLeidas.value = Math.max(0, noLeidas.value - 1);
      }
    } catch {
      /* noop */
    }
  }

  async function marcarTodasLeidas() {
    try {
      await api.patch("/admin/notificaciones/leer-todas");
      notificaciones.value.forEach((n) => (n.leido = true));
      noLeidas.value = 0;
    } catch {
      /* noop */
    }
  }

  // Recibida por WebSocket en tiempo real — se antepone al listado
  function agregarNueva(n: Notificacion) {
    notificaciones.value.unshift(n);
    if (!n.leido) noLeidas.value++;
  }

  return {
    notificaciones,
    noLeidas,
    loading,
    fetchAll,
    marcarLeida,
    marcarTodasLeidas,
    agregarNueva,
  };
});
