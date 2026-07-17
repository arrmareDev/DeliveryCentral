import { defineStore } from "pinia";
import { ref, watch } from "vue";

export const useThemeStore = defineStore("theme", () => {
  const isDark = ref(resolveInitial());

  function resolveInitial(): boolean {
    const saved = localStorage.getItem("admin_theme");
    if (saved) return saved === "dark";
    return window.matchMedia("(prefers-color-scheme: dark)").matches;
  }

  function apply(dark: boolean) {
    document.documentElement.classList.toggle("dark", dark);
    localStorage.setItem("admin_theme", dark ? "dark" : "light");
  }

  function toggle() {
    isDark.value = !isDark.value;
  }

  watch(isDark, apply, { immediate: true });

  return { isDark, toggle };
});