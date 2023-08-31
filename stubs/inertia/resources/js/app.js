import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

//Quasar
import { Quasar, Notify } from "quasar";
import quasarLang from "quasar/lang/ar";
import quasarIconSet from "quasar/icon-set/mdi-v7";

// Import icon libraries
import "@quasar/extras/material-icons/material-icons.css";
import "@quasar/extras/mdi-v7/mdi-v7.css";

// Import Quasar css
import "quasar/src/css/index.sass";
// A few examples for animations from Animate.css:
import "@quasar/extras/animate/zoomIn.css";
import "@quasar/extras/animate/zoomOut.css";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
import AppLayout from "./Layouts/AppLayout.vue";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    // resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        let page = pages[`./Pages/${name}.vue`].default;
        page.layout = page.layout || AppLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
        .use(Quasar, {
            plugins: { Notify }, // import Quasar plugins and add here
            lang: quasarLang,
            iconSet: quasarIconSet,
            animations: ["zoomIn", "zoomOut"],
            config: {
                notify: { position: "top" },
            },
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
