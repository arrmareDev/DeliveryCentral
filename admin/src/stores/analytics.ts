import { defineStore } from "pinia";
import { ref } from "vue";
import api from "../utils/api";

export interface DespachoPorDia {
  fecha: string;
  total: number;
  entregados: number;
  cancelados: number;
}

export interface MetodoPago {
  metodo: string;
  total: number;
}

export interface ComparativaMes {
  despachos: number;
  comisiones: number;
}

export interface TopMotorizado {
  nombre: string;
  total: number;
}

export const useAnalyticsStore = defineStore("analytics", () => {
  const despachosPorDia = ref<DespachoPorDia[]>([]);
  const metodosPago = ref<MetodoPago[]>([]);
  const comparativa = ref<{
    mes_actual: ComparativaMes;
    mes_anterior: ComparativaMes;
  }>({
    mes_actual: { despachos: 0, comisiones: 0 },
    mes_anterior: { despachos: 0, comisiones: 0 },
  });
  const topMotorizados = ref<TopMotorizado[]>([]);
  const loading = ref(false);

  async function fetchDashboard() {
    loading.value = true;
    try {
      const { data } = await api.get("/admin/analytics/dashboard");
      despachosPorDia.value = data.data.despachos_por_dia;
      metodosPago.value = data.data.metodos_pago;
      comparativa.value = data.data.comparativa_mensual;
      topMotorizados.value = data.data.top_motorizados;
    } finally {
      loading.value = false;
    }
  }

  return {
    despachosPorDia,
    metodosPago,
    comparativa,
    topMotorizados,
    loading,
    fetchDashboard,
  };
});
