import { ref } from "vue";

export interface Toast {
  id: number;
  message: string;
  type: "success" | "error" | "warning" | "info";
}

const toasts = ref<Toast[]>([]);
let nextId = 1;

export function useToastStore() {
  function show(
    message: string,
    type: Toast["type"] = "info",
    duration = 4000,
  ) {
    const id = nextId++;
    toasts.value.push({ id, message, type });
    setTimeout(() => remove(id), duration);
  }

  function remove(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
  }

  return {
    toasts,
    remove,
    success: (msg: string) => show(msg, "success"),
    error: (msg: string) => show(msg, "error"),
    warning: (msg: string) => show(msg, "warning"),
    info: (msg: string) => show(msg, "info"),
  };
}
