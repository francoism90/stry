import { afterEach, beforeAll, describe, expect, it, vi } from 'vite-plus/test'
import { nextTick, ref } from 'vue'

const { get } = vi.hoisted(() => ({ get: vi.fn<(...args: unknown[]) => Promise<void>>(() => Promise.resolve()) }))

vi.mock('@inertiajs/vue3', () => ({
  useHttp: () => ({ get }),
  usePage: () => ({ props: { tags: [] } }),
}))

vi.mock('@/actions/Modules/Api/Tags/Controllers/TagController', () => ({
  index: { url: () => '/api/v1/tags' },
}))

// The composable skips fetching during SSR, and Vitest's node environment reports SSR, so load it as a browser would.
let useTags: typeof import('@/composables/tags').useTags

beforeAll(async () => {
  vi.stubEnv('SSR', false)
  ;({ useTags } = await import('@/composables/tags'))
})

describe('useTags', () => {
  afterEach(() => get.mockClear())

  it('does not fetch suggestions while disabled', async () => {
    useTags([], false)
    await nextTick()

    expect(get).not.toHaveBeenCalled()
  })

  it('fetches suggestions once after it is enabled', async () => {
    const isOpen = ref(false)

    useTags([], isOpen)
    await nextTick()
    expect(get).not.toHaveBeenCalled()

    isOpen.value = true
    await nextTick()
    await Promise.resolve()

    isOpen.value = false
    await nextTick()
    isOpen.value = true
    await nextTick()

    expect(get).toHaveBeenCalledTimes(1)
  })
})
