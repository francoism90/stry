<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

withDefaults(
  defineProps<{
    count?: number
  }>(),
  {
    count: 6,
  },
)

const titleWidths = ['w-7/10', 'w-11/20', 'w-4/5', 'w-3/5', 'w-13/20', 'w-3/4']
const metaWidths = ['w-9/20', 'w-2/5', 'w-7/20', 'w-1/2', 'w-3/10', 'w-2/5']

/**
 * Pages usually load faster than this, so the skeleton stays hidden instead of flashing in and out.
 */
const isVisible = ref(false)

let visibilityTimer: ReturnType<typeof setTimeout> | undefined

onMounted(() => {
  visibilityTimer = setTimeout(() => (isVisible.value = true), 300)
})

onBeforeUnmount(() => clearTimeout(visibilityTimer))
</script>

<template>
  <div
    class="grid grid-cols-[repeat(auto-fill,minmax(min(16.25rem,100%),1fr))] gap-x-4 gap-y-10 transition-opacity duration-300"
    :class="isVisible ? 'opacity-100' : 'opacity-0'"
    aria-hidden="true"
  >
    <div
      v-for="i in count"
      :key="i"
      class="flex flex-col gap-2"
    >
      <USkeleton class="aspect-video w-full rounded-lg bg-(--glass)" />
      <USkeleton
        class="h-3.5 rounded-sm bg-(--glass)"
        :class="titleWidths[(i - 1) % titleWidths.length]"
      />
      <USkeleton
        class="h-3 rounded-sm bg-(--glass)"
        :class="metaWidths[(i - 1) % metaWidths.length]"
      />
    </div>
  </div>
</template>
