<script setup lang="ts">
withDefaults(
  defineProps<{
    count?: number
  }>(),
  {
    count: 4,
  },
)
</script>

<template>
  <div
    class="grid grid-cols-[repeat(auto-fill,minmax(min(12.5rem,100%),1fr))] gap-x-4 gap-y-8"
    aria-hidden="true"
  >
    <div
      v-for="i in count"
      :key="i"
      class="flex flex-col gap-3"
    >
      <USkeleton class="mt-3 aspect-video w-full rounded-xl bg-(--glass)" />
      <USkeleton class="h-3.5 w-3/5 rounded-sm bg-(--glass)" />
      <USkeleton class="h-3 w-2/5 rounded-sm bg-(--glass)" />
    </div>
  </div>
</template>
