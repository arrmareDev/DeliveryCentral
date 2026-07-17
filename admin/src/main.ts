import { createApp } from "vue";
import { createPinia } from "pinia";
import VueApexCharts from "vue3-apexcharts";
import App from "./App.vue";
import router from "./router";
import { useThemeStore } from "./stores/theme";
import "./style.css";

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.use(VueApexCharts);

useThemeStore();

app.mount("#app");
