<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Link } from '@inertiajs/vue3'
import 'vidstack/bundle'
import { computed } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const tags = computed(() => props.item.tags?.slice(0, 4).map((tag) => tag.name) || [])
const link = computed(() => show.url(props.item.id))
</script>

<template>
  <UCard
    variant="solid"
    :ui="{
      root: 'group h-52 max-h-52 min-h-52 rounded-xl bg-transparent',
      body: 'relative !p-0',
    }"
  >
    <Link
      class="block"
      :href="link"
    >
      <div class="absolute inset-0 z-0 size-full bg-gradient-to-t from-neutral-800/50 to-transparent" />

      <img
        :srcset="item.srcset"
        :src="item.thumbnail"
        :alt="item.name"
        class="h-52 w-full object-fill"
        loading="lazy"
      />

      <div class="absolute inset-x-4 bottom-4 z-10 block group-hover:hidden">
        <div class="grid content-end gap-0.5">
          <h2 class="line-clamp-2 text-sm leading-tight font-medium tracking-tight text-neutral-100">{{ item.name }}</h2>
          <dl class="details text-xs font-light tracking-tight text-neutral-100">
            <dt class="sr-only">Duration</dt>
            <dd>{{ item.timestamp }}</dd>

            <template v-if="tags.length">
              <dt class="sr-only">Tags</dt>
              <dd class="truncate">{{ tags.join(', ') }}</dd>
            </template>
          </dl>
        </div>
      </div>

      <div class="absolute inset-0 z-0 hidden h-52 w-full bg-neutral-800 group-hover:block">
        <media-player
          .src="item.preview"
          .playsInline="true"
          .keyDisabled="true"
          .autoPlay="true"
          .muted="true"
          .loop="true"
          class="player max-h-52"
        >
          <media-provider />
        </media-player>
      </div>
    </Link>
  </UCard>
</template>
