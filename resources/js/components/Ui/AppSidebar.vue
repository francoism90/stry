<script setup lang="ts">
import { show } from '@/actions/App/Web/Groups/Controllers/GroupController'
import AppLogo from '@/components/Ui/AppLogo.vue'
import { useAuth } from '@/composables/auth'
import { useEcho } from '@/composables/echo'
import { useGroups } from '@/composables/groups'
import type { CollectionItem } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import type { NavigationMenuItem } from '@nuxt/ui'
import { computed } from 'vue'

defineProps<{
  mode: 'drawer' | 'slideover' | 'modal'
}>()

const { user: auth, hasRole } = useAuth()
const { groupIcon } = useGroups()
const { privateChannel } = useEcho()

const collections = computed<CollectionItem[]>(() => usePage().props.collections ?? [])

const collectionItems = computed<NavigationMenuItem[]>(() =>
  collections.value.map((item: CollectionItem) => ({
    label: item.title,
    icon: groupIcon(item.type),
    to: show.url(item.id),
  })),
)

const items = computed<NavigationMenuItem[][]>(() => [
  [
    {
      label: 'Home',
      icon: 'i-lucide-house',
      to: '/',
      exact: true,
    },
    {
      label: 'Tags',
      icon: 'i-lucide-tags',
      to: '/tags',
    },
    {
      label: 'Collections',
      icon: 'i-lucide-folders',
      to: '/collections',
    },
  ],
  ...(hasRole('super-admin')
    ? [
        [
          {
            label: 'Library',
            icon: 'i-lucide-library-big',
            to: '/videos',
            exact: true,
          },
          {
            label: 'Users',
            icon: 'i-lucide-users',
            to: '/users',
            exact: true,
          },
          {
            label: 'Transcodes',
            icon: 'i-lucide-film',
            to: '/transcodes',
            exact: true,
          },
        ],
      ]
    : []),
  ...(collectionItems.value.length ? [collectionItems.value] : []),
])

if (auth.value) {
  const reloadCollections = () => router.reload({ only: ['collections'] })

  privateChannel(`users.${auth.value.id}`)
    .listen('.group.created', reloadCollections)
    .listen('.group.updated', reloadCollections)
    .listen('.group.trashed', reloadCollections)
}
</script>

<template>
  <UDashboardSidebar
    :mode="mode"
    :default-size="16"
    :resizable="false"
  >
    <template #header>
      <AppLogo />
    </template>

    <UNavigationMenu
      :items="items"
      orientation="vertical"
      :ui="{
        link: 'py-3',
        separator: 'my-1',
      }"
    />
  </UDashboardSidebar>
</template>
