import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "../utils/api";

interface AdminUser {
  id: number;
  name: string;
  email: string;
}

export const useAuthStore = defineStore("auth", () => {
  const token = ref<string | null>(localStorage.getItem("admin_token"));
  const user = ref<AdminUser | null>(
    JSON.parse(localStorage.getItem("admin_user") ?? "null"),
  );
  const loading = ref(false);

  const isAuth = computed(() => !!token.value);

  async function login(
    email: string,
    password: string,
  ): Promise<{ ok: boolean; message?: string }> {
    loading.value = true;
    try {
      const { data } = await api.post("/admin/auth/login", { email, password });
      token.value = data.data.token;
      user.value = data.data.user;
      localStorage.setItem("admin_token", data.data.token);
      localStorage.setItem("admin_user", JSON.stringify(data.data.user));
      return { ok: true };
    } catch (e: any) {
      return {
        ok: false,
        message: e.response?.data?.message ?? "Credenciales inválidas",
      };
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await api.post("/admin/auth/logout");
    } catch {
      /* noop */
    }
    token.value = null;
    user.value = null;
    localStorage.removeItem("admin_token");
    localStorage.removeItem("admin_user");
  }

  async function fetchMe() {
    try {
      const { data } = await api.get("/admin/auth/me");
      user.value = data.data;
      localStorage.setItem("admin_user", JSON.stringify(data.data));
    } catch {
      await logout();
    }
  }

  return { token, user, loading, isAuth, login, logout, fetchMe };
});
