import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAuth() {
  const user = computed(() => usePage().props.auth.user)

  const hasRole = (key: string) => user.value?.roles?.includes(key) ?? false
  const hasPermission = (key: string) => user.value?.permissions?.includes(key) ?? false

  const hasAnyRole = (names: string[]) => names.some((name) => hasRole(name))
  const hasAnyPermission = (names: string[]) => names.some((name) => hasPermission(name))

  return {
    user,
    hasRole,
    hasPermission,
    hasAnyRole,
    hasAnyPermission,
  }
}
