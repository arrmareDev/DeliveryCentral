import { defineStore } from "pinia";
import { ref } from "vue";

/**
 * Breadcrumb dinámico — para vistas que muestran un "detalle" sin
 * cambiar de ruta (ej. ComisionesView al seleccionar un motorizado).
 * La vista lo setea al montar/seleccionar y lo limpia al salir.
 */
export const useBreadcrumbsStore = defineStore("breadcrumbs", () => {
  const extra = ref<string | null>(null);

  function setExtra(label: string) {
    extra.value = label;
  }

  function clearExtra() {
    extra.value = null;
  }

  return { extra, setExtra, clearExtra };
});
