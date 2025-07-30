<script setup lang="ts">
import type { NavigationMenuItem } from '@nuxt/ui'

interface Props {
  title: string
  actions?: NavigationMenuItem[]
}

defineProps<Props>()
</script>

<template>
  <section class="relative flex flex-col gap-1">
    <div class="flex items-center justify-between gap-2">
      <h1 class="line-clamp-2 font-serif text-lg font-semibold tracking-tight">
        {{ title }}
      </h1>

      <UNavigationMenu
        v-if="actions?.length"
        variant="link"
        :items="actions"
        :ui="{
          root: 'items-center gap-2',
          list: 'inline-flex size-full items-center gap-4',
          link: 'px-0',
        }"
      />
    </div>
  </section>
</template>
