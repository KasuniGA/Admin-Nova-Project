import Tool from "./pages/Tool";
import Header from "./components/Header.vue";

Nova.inertia("PriceTracker", Tool);

Nova.booting((Vue, store) => {
    Vue.component("PriceTrackerHeader", Header);
});
