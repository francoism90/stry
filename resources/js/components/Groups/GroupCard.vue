<script setup lang="ts">
import { show } from '@/actions/App/Web/Groups/Controllers/GroupController'
import { useGroups } from '@/composables/groups'
import type { Group } from '@/types'
import { computed } from 'vue'

const props = defineProps<{
  item: Group
}>()

const { groupIcon, groupGradient } = useGroups()

const style = computed(() => ({
  gradient: groupGradient(props.item.type),
  icon: groupIcon(props.item.type),
}))
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
      <span class="line-clamp-1 text-sm font-semibold capitalize">
        {{ item.title ?? item.type }}
      </span>
      <span class="text-xs text-muted">
        {{ Intl.NumberFormat().format(item.videos ?? 0) }} {{ (item.videos ?? 0) === 1 ? 'video' : 'videos' }}
      </span>
    </div>
  </ULink>
</template>
