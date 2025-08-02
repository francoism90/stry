<script setup lang="ts">
import { show } from '@/actions/App/Web/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  item: Tag
}

const props = defineProps<Props>()

const link = computed(() => show.url(props.item.id))
</script>

<template>
  <UCard
    variant="solid"
    :ui="{
      root: 'group h-52 min-h-52 rounded-none bg-transparent',
      body: 'relative !p-0',
    }"
  >
    <Link :href="link">
      <div class="absolute inset-0 z-0 size-full rounded-xl bg-gradient-to-t from-neutral-800/30 to-transparent" />

      <img
        :srcset="item.srcset"
        :src="item.thumbnail"
        :alt="item.name"
        class="h-52 w-full rounded-xl object-fill group-hover:rounded-none"
        loading="lazy"
      />

      <div class="absolute inset-x-4 bottom-4 z-10 block group-hover:hidden">
        <div class="grid content-end gap-0.5">
          <h2 class="line-clamp-2 text-sm leading-tight font-medium tracking-tight text-neutral-100">{{ item.name }}</h2>
          <dl class="list text-xs font-light tracking-tight text-neutral-100">
            <dt class="sr-only">Type</dt>
            <dd class="truncate">{{ item.type }}</dd>
          </dl>
        </div>
      </div>
    </Link>

    <div class="absolute inset-x-0 bottom-0 z-20 hidden group-hover:block">
      <USlider
        :default-value="50"
        :ui="{
          root: 'flex h-8 items-end',
          track: 'h-1 rounded-none',
          range: 'rounded-none',
          thumb: 'hidden',
        }"
      />
    </div>
  </UCard>
</template>
