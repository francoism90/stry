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
  <UCard variant="soft">
    <Link
      class="block"
      :href="show.url(item.id)"
    >
      <h2>{{ item.name }}</h2>
      <dl class="list text-xs font-light tracking-tight text-neutral-100">
        <dt class="sr-only">Type</dt>
        <dd>{{ item.type }}</dd>
      </dl>
    </Link>
  </UCard>
</template>
