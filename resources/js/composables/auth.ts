import { usePage } from '@inertiajs/vue3'

export function useAuth() {
  const user = () => usePage().props.auth.user

  const hasRole = (key: string) => user()?.roles?.includes(key) ?? false
  const hasPermission = (key: string) => user()?.permissions?.includes(key) ?? false

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
