<script setup lang="ts">
import { useGroups } from '@/composables/groups'
import type { Group } from '@/types'
import { computed } from 'vue'

const props = withDefaults(
  defineProps<{
    item: Pick<Group, 'type' | 'thumb'>
    size?: 'md' | 'lg'
  }>(),
  {
    size: 'md',
  },
)

const { groupCover, groupGradient, groupIcon } = useGroups()

const cover = computed(() => groupCover(props.item.type))
</script>

<template>
  <span
    class="flex flex-col items-center"
    :class="size === 'lg' ? 'gap-1' : 'gap-0.75'"
    aria-hidden="true"
  >
    <span
      class="w-[84%] rounded-t-md"
      :class="[cover.back, size === 'lg' ? 'h-1.5' : 'h-1.25']"
    />
    <span
      class="w-[92%] rounded-t-md"
      :class="[cover.middle, size === 'lg' ? 'h-1.5' : 'h-1.25']"
    />
    <span
      class="relative grid aspect-video w-full place-items-center overflow-hidden rounded-xl bg-linear-to-br"
      :class="groupGradient(item.type)"
    >
      <template v-if="item.thumb">
        <img
          :src="item.thumb"
          alt=""
          class="absolute inset-0 size-full object-cover transition-transform duration-300 group-hover/cover:scale-105"
          loading="lazy"
          decoding="async"
        />
        <span
          class="absolute inset-0 bg-linear-to-tl via-32% to-transparent to-62%"
          :class="cover.wash"
        />
      </template>

      <UIcon
        :name="groupIcon(item.type)"
        class="drop-shadow-md"
        :class="[
          item.thumb ? 'absolute end-3 bottom-3 size-6 text-white' : 'size-12 text-white/90',
          size === 'lg' && item.thumb ? 'size-7' : '',
        ]"
      />
    </span>
  </span>
</template>
