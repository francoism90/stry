<script setup lang="ts">
import { show } from '@/actions/App/Web/Groups/Controllers/GroupController'
import type { Group } from '@/types'
import { computed } from 'vue'

const props = defineProps<{
  item: Group
}>()

type GroupStyle = {
  gradient: string
  icon: string
}

const groupStyles: Record<string, GroupStyle> = {
  custom: {
    gradient: 'from-violet-500 to-purple-700',
    icon: 'i-lucide-folder',
  },
  liked: {
    gradient: 'from-rose-500 to-pink-700',
    icon: 'i-lucide-heart',
  },
  mixer: {
    gradient: 'from-indigo-500 to-blue-700',
    icon: 'i-lucide-shuffle',
  },
  saved: {
    gradient: 'from-sky-500 to-cyan-700',
    icon: 'i-lucide-bookmark',
  },
  viewed: {
    gradient: 'from-emerald-500 to-green-700',
    icon: 'i-lucide-history',
  },
}

const style = computed<GroupStyle>(
  () =>
    groupStyles[props.item.type ?? ''] ?? {
      gradient: 'from-neutral-500 to-neutral-700',
      icon: 'i-lucide-layers',
    },
)
</script>

<template>
  <ULink
    :to="show.url(item.id)"
    class="group focus-visible:ring-ring flex flex-col gap-3 rounded-xl transition focus-visible:ring-2 focus-visible:outline-none"
  >
    <div
      class="relative aspect-square w-full overflow-hidden rounded-xl bg-linear-to-br"
      :class="style.gradient"
    >
      <div class="absolute inset-0 flex items-center justify-center">
        <UIcon
          :name="style.icon"
          class="size-16 text-white/90 drop-shadow-md transition-transform duration-300 group-hover:scale-110"
        />
      </div>

      <div class="absolute inset-0 bg-black/10 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
    </div>

    <div class="flex flex-col gap-0.5 px-0.5">
      <span class="line-clamp-1 text-sm font-semibold">
        {{ item.title ?? item.type }}
      </span>
      <span class="text-muted text-xs">
        {{ Intl.NumberFormat().format(item.videos ?? 0) }} {{ (item.videos ?? 0) === 1 ? 'video' : 'videos' }}
      </span>
    </div>
  </ULink>
</template>
