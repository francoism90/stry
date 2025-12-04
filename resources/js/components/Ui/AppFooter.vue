<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

const links = <NavigationMenuItem[]>[
  {
    label: 'Privacy Policy',
    to: '/privacy-policy',
  },
  {
    label: 'Terms of Service',
    to: '/terms-of-service',
  },
]
</script>

<template>
  <UFooter
    class="z-10 bg-default"
    :ui="{ left: 'text-xs text-muted' }"
  >
    <template #left>
      <span>© 2025</span>
    </template>

    <template #right>
      <UButton
        v-for="(link, index) of links"
        :key="index"
        v-bind="{ size: 'xs', color: 'neutral', variant: 'ghost', ...link }"
      />
    </template>
  </UFooter>
</template>
