import Vue from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue'

require("bootstrap-css-only/css/bootstrap.min.css");
require("mdbvue/lib/css/mdb.min.css");
require("@fortawesome/fontawesome-free/css/all.min.css");

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, app, props, plugin }) {
        Vue.use(plugin)

        new Vue({
            render: h => h(app, props),
        }).$mount(el)
    },
})
