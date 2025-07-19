import shaka from 'shaka-player'
import { readonly, shallowRef, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useShaka(url?: MaybeRefOrGetter<string>, time?: MaybeRefOrGetter<number>) {
  const player = shallowRef<shaka.Player>()
  const manager = shallowRef<shaka.media.PreloadManager | null>()
  const events = shallowRef<shaka.util.EventManager | null>()

  const load = async () => (manager.value ? player.value?.load(manager.value) : '')

  const unload = async () => player.value?.unload()

  const container = async (el: HTMLElement) => player.value?.setVideoContainer(el)

  const attach = async (el: HTMLMediaElement) => player.value?.attach(el)

  const detach = async () => player.value?.detach()

  const leave = async () => player.value?.unloadAndSavePreload()

  const destroy = async () => manager.value?.destroy()

  watchEffect(async () => {
    const assetUri = toValue(url)
    const startTime = toValue(time)

    // Install polyfills
    await shaka.polyfill.installAll()

    // Check browser support
    if (!shaka.Player.isBrowserSupported()) {
      console.error('Browser not supported')
      return
    }

    // Setup player and events
    player.value = new shaka.Player()
    events.value = new shaka.util.EventManager()

    // Setup preload manager
    if (assetUri) {
      manager.value = await player.value?.preload(assetUri, startTime)
    }
  })

  return {
    load,
    unload,
    container,
    attach,
    detach,
    leave,
    destroy,
    events: readonly(events),
    manager: readonly(manager),
    state: readonly(player),
  }
}
