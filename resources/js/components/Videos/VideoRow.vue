<script setup lang="ts">
import { show } from '@/routes/videos'
import type { Video } from '@/types'
import type { DropdownMenuItem } from '@nuxt/ui'
import VideoTags from './VideoTags.vue'

withDefaults(
  defineProps<{
    item: Video
    actions?: DropdownMenuItem[]
  }>(),
  {
    actions: () => [],
  },
)
</script>

<template>
  <li
    class="group/row flex items-center gap-3 rounded-lg border border-transparent p-2 transition-colors hover:border-(--glass-border) hover:bg-(--glass)"
  >
    <ULink
      :to="show.url(item.id)"
      :aria-label="`Play ${item.title}`"
      class="relative aspect-video w-24 shrink-0 overflow-hidden rounded-lg bg-muted sm:w-32"
    >
      <img
        v-if="item.thumb"
        :src="item.thumb"
        :srcset="item.thumb_srcset ?? undefined"
        sizes="8rem"
        alt=""
        class="size-full object-cover"
        loading="lazy"
        decoding="async"
      />
    </ULink>

    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
      <ULink
        :to="show.url(item.id)"
        class="line-clamp-2 text-sm leading-snug font-medium wrap-anywhere text-highlighted capitalize"
      >
        {{ item.title }}
      </ULink>

      <div class="flex flex-wrap items-center gap-x-1 text-sm text-muted">
        <template v-if="item.viewed">
          <span>Watched</span>
          <span
            v-if="item.tags?.length"
            class="opacity-40 select-none"
            aria-hidden="true"
          >
            ·
          </span>
        </template>

        <VideoTags :items="item.tags" />
      </div>
    </div>

    <span
      v-if="item.timestamp"
      class="shrink-0 text-sm text-muted tabular-nums"
    >
      {{ item.timestamp }}
    </span>

    <UDropdownMenu
      v-if="actions.length"
      :items="actions"
      :content="{ align: 'end' }"
    >
      <UButton
        icon="i-lucide-ellipsis"
        :aria-label="`More actions for ${item.title}`"
        color="neutral"
        variant="ghost"
        size="sm"
        class="shrink-0 rounded-full text-muted group-hover/row:bg-(--glass-strong) group-hover/row:text-highlighted"
      />
    </UDropdownMenu>
  </li>
</template>
