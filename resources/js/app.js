import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { VuesticPlugin } from 'vuestic-ui'
import { ZiggyVue } from 'ziggy'
import 'vuestic-ui/dist/vuestic-ui.css'
import Layout from './Layout.vue'

createInertiaApp({
  resolve: name => {
    const page = require(`./Pages/${name}`).default;
    page.layout = page.layout || Layout;
    return page;
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(VuesticPlugin)
      .use(ZiggyVue)
      .mount(el)
  },
})
