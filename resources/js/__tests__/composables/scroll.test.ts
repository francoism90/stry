import { useHeaderCollapse } from '@/composables/scroll'
import { describe, expect, it } from 'vite-plus/test'

describe('useHeaderCollapse', () => {
  it('does not touch window during SSR', () => {
    expect(typeof window).toBe('undefined')

    const { isHeaderCollapsed } = useHeaderCollapse()

    expect(isHeaderCollapsed.value).toBe(false)
  })
})
