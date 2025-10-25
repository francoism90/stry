<script setup lang="ts">
import { show } from '@/actions/App/Web/Videos/Controllers/VideoController'
import type { Video } from '@/types'
import { Link } from '@inertiajs/vue3'
import 'vidstack/bundle'
import { computed, ref } from 'vue'

interface Props {
  item: Video
}

const props = defineProps<Props>()

const preview = ref<boolean>(false)
const tags = computed(() => props.item.tags?.slice(0, 4).map((tag) => tag.name) || [])
</script>

<template>
  <UCard
    variant="solid"
    :ui="{
      root: 'group h-56 max-h-56 min-h-56 rounded-xl bg-transparent',
      body: 'relative !p-0',
    }"
  >
    <Link
      class="block"
      :href="show.url(props.item.id)"
      @mouseenter.prevent="preview = true"
      @mouseleave.prevent="preview = false"
      @touchstart.passive="preview = true"
      @touchend.passive="preview = false"
    >
      <div class="absolute inset-0 z-0 size-full bg-linear-to-t from-neutral-900/70 to-transparent" />

      <img
        :src="item.thumb"
        :alt="item.title"
        class="h-56 max-h-56 w-full object-fill"
        loading="lazy"
        decoding="async"
        fetchpriority="high"
      />

      <div class="absolute inset-x-4 bottom-4 z-10 block group-hover:hidden">
        <div class="grid content-end gap-0.5">
          <h2 class="line-clamp-2 text-sm leading-tight font-medium tracking-tight text-neutral-100">{{ item.title }}</h2>
          <dl class="details text-xs font-light tracking-tight text-neutral-100">
            <dt class="sr-only">Duration</dt>
            <dd>{{ item.timestamp }}</dd>

            <template v-if="tags.length">
              <dt class="sr-only">Tags</dt>
              <dd class="truncate">{{ tags.join(', ') }}</dd>
            </template>

            <template v-if="item.captions">
              <dt class="sr-only">Captions</dt>
              <dd>CC</dd>
            </template>
          </dl>
        </div>
      </div>

      <div
        v-if="preview && item.preview?.length"
        class="absolute inset-0 z-10 h-56 w-full group-hover:block"
      >
        <media-player
          .src="item.preview"
          .playsInline="true"
          .keyDisabled="true"
          .autoPlay="true"
          .muted="true"
          .loop="true"
          class="default-video h-56 max-h-56"
        >
          <media-provider />
        </media-player>
      </div>
    </Link>
  </UCard>
</template>
