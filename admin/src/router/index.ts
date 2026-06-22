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
      },
      {
        path: "restaurantes",
        name: "restaurantes",
        component: () => import("../views/RestaurantesView.vue"),
      },
      {
        path: "motorizados",
        name: "motorizados",
        component: () => import("../views/MotorizadosView.vue"),
      },
      {
        path: "despachos",
        name: "despachos",
        component: () => import("../views/DespachosView.vue"),
      },
      {
        path: "comisiones",
        name: "comisiones",
        component: () => import("../views/ComisionesView.vue"),
      },
      {
        path: "configuracion",
        name: "configuracion",
        component: () => import("../views/ConfiguracionView.vue"),
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
