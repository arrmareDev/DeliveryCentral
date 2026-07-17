import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const routes = [
  {
    path: "/login",
    name: "login",
    component: () => import("../views/LoginView.vue"),
    meta: { guestOnly: true },
  },
  {
    path: "/",
    component: () => import("../layouts/AdminLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "dashboard",
        component: () => import("../views/DashboardView.vue"),
        meta: { breadcrumb: "Dashboard" },
      },
      {
        path: "restaurantes",
        name: "restaurantes",
        component: () => import("../views/RestaurantesView.vue"),
        meta: { breadcrumb: "Restaurantes" },
      },
      {
        path: "motorizados",
        name: "motorizados",
        component: () => import("../views/MotorizadosView.vue"),
        meta: { breadcrumb: "Motorizados" },
      },
      {
        path: "despachos",
        name: "despachos",
        component: () => import("../views/DespachosView.vue"),
        meta: { breadcrumb: "Despachos" },
      },
      {
        path: "comisiones",
        name: "comisiones",
        component: () => import("../views/ComisionesView.vue"),
        meta: { breadcrumb: "Comisiones" },
      },
      {
        path: "configuracion",
        name: "configuracion",
        component: () => import("../views/ConfiguracionView.vue"),
        meta: { breadcrumb: "Configuración" },
      },
    ],
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuth) {
    return { name: "login" };
  }

  if (to.meta.guestOnly && auth.isAuth) {
    return { name: "dashboard" };
  }
});

export default router;
