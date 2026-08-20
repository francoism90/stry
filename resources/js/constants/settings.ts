import AppearanceController from '@/actions/App/Web/Account/Controllers/AppearanceController'
import SettingsController from '@/actions/App/Web/Account/Controllers/SettingsController'
import type { NavigationMenuItem } from '@nuxt/ui'

export const settingsTabs: NavigationMenuItem[] = [
  {
    label: 'General',
    icon: 'i-lucide-settings-2',
    to: SettingsController.url(),
  },
  {
    label: 'Appearance',
    icon: 'i-lucide-palette',
    to: AppearanceController.url(),
  },
]
