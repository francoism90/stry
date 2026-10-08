<script setup lang="ts">
import { show } from '@/actions/Modules/Web/Tags/Controllers/TagController'
import TagEditModal from '@/components/Tags/TagEditModal.vue'
import { useAppearance } from '@/composables/appearance'
import { tagIcon } from '@/composables/tags'
import type { Tag } from '@/types'
import { computed } from 'vue'

const props = defineProps<{
  item: Tag
}>()

const { dynamicColor } = useAppearance()

const videoCount = computed(() => props.item.videos ?? 0)

const videoCountLabel = computed(
  () => `${Intl.NumberFormat().format(videoCount.value)} ${videoCount.value === 1 ? 'video' : 'videos'}`,
)
</script>

<template>
  <div
    class="group/card relative h-32 overflow-hidden rounded-xl border border-(--glass-border) bg-muted transition-[border-color] hover:border-white/25"
  >
    <img
      v-if="item.thumb"
      :src="item.thumb"
      alt=""
      class="absolute inset-0 size-full object-cover transition-transform duration-300 group-hover/card:scale-105"
      loading="lazy"
      decoding="async"
    />

    <div
      v-else
      class="absolute inset-0 opacity-70"
      :style="{ background: `linear-gradient(135deg, ${dynamicColor(item.slug)}, transparent)` }"
      aria-hidden="true"
    />

    <div
      class="pointer-events-none absolute inset-0 bg-linear-to-t from-black/85 via-black/30 via-65% to-black/40"
      aria-hidden="true"
    />

    <ULink
      :to="show.url(item.id)"
      :aria-label="`${item.name}, ${item.type ?? 'tag'}, ${videoCountLabel}`"
      class="absolute inset-0"
    >
      <span
        v-if="item.type"
        class="absolute start-2.5 top-2.5 flex items-center gap-1 rounded-full border border-white/20 bg-black/45 py-0.75 ps-1.5 pe-2 text-xs font-medium text-white capitalize backdrop-blur-md backdrop-saturate-160"
      >
        <UIcon
          :name="tagIcon(item.type)"
          class="size-3.5 shrink-0"
        />
        {{ item.type }}
      </span>

      <span class="absolute inset-x-3 bottom-2.5 flex flex-col gap-0.5">
        <span class="truncate text-base font-semibold text-white capitalize">{{ item.name }}</span>
        <span class="text-xs text-white/80">{{ videoCountLabel }}</span>
      </span>
    </ULink>

    <TagEditModal :item="item">
      <UButton
        icon="i-lucide-pencil"
        aria-label="Edit tag"
        color="neutral"
        variant="solid"
        size="xs"
        class="absolute end-2 top-2 z-10 rounded-full border border-white/20 bg-black/45 text-white opacity-0 backdrop-blur-md transition-opacity group-hover/card:opacity-100 hover:bg-black/60 focus-visible:opacity-100"
      />
    </TagEditModal>
  </div>
</template>
