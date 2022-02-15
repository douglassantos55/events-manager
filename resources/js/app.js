import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { VuesticPlugin } from 'vuestic-ui'
import 'vuestic-ui/dist/vuestic-ui.css'

createInertiaApp({
  resolve: name => require(`./Pages/${name}`),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(VuesticPlugin)
      .mount(el)
  },
})
