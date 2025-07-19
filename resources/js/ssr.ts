import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { configureEcho } from '@laravel/echo-vue'
import { renderToString } from '@vue/server-renderer'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createSSRApp, h, type DefineComponent } from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

configureEcho({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  wssPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
})

createServer(
  (page) =>
    createInertiaApp({
      page,
      render: renderToString,
      title: (title) => (title ? `${title} - ${appName}` : appName),
      resolve: resolvePage,
      setup: ({ App, props, plugin }) => createSSRApp({ render: () => h(App, props) }).use(plugin),
    }),
  { cluster: true },
)

function resolvePage(name: string) {
  const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue')

  return resolvePageComponent<DefineComponent>(`./pages/${name}.vue`, pages)
}
