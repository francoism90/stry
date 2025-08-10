<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { Link } from '@inertiajs/vue3'

interface Props {
  item: Tag
}

defineProps<Props>()
</script>

<template>
  <UCard
    variant="soft"
    :ui="{ body: '!p-0' }"
  >
    <Link
      class="block p-4 sm:p-6"
      :href="show.url(item.id)"
    >
      <h2>{{ item.name }}</h2>
      <dl class="details text-xs font-light tracking-tight text-neutral-300">
        <dt class="sr-only">Type</dt>
        <dd>{{ item.category }}</dd>
        <dt class="sr-only">Videos</dt>
        <dd>{{ item.videos }} videos</dd>
      </dl>
    </Link>
  </UCard>
</template>
