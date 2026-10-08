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
    class="grid grid-cols-[repeat(auto-fill,minmax(min(13.75rem,100%),1fr))] gap-4"
    aria-hidden="true"
  >
    <USkeleton
      v-for="i in count"
      :key="i"
      class="h-32 rounded-xl border border-(--glass-border) bg-(--glass)"
    />
  </div>
</template>
