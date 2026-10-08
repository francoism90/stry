<script setup lang="ts">
defineProps<{
  title: string
  description?: string | null
}>()
</script>

<template>
  <li
    class="flex items-center gap-3 rounded-lg border border-transparent p-2 transition-colors hover:border-(--glass-border) hover:bg-(--glass)"
  >
    <slot name="leading" />

    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
      <span class="truncate text-sm font-medium text-highlighted">{{ title }}</span>

      <span
        v-if="description"
        class="truncate text-sm text-muted"
      >
        {{ description }}
      </span>
    </div>

    <div
      v-if="$slots.meta"
      class="hidden shrink-0 items-center gap-1.5 sm:flex"
    >
      <slot name="meta" />
    </div>

    <div
      v-if="$slots.actions"
      class="flex shrink-0 items-center gap-1"
    >
      <slot name="actions" />
    </div>
  </li>
</template>
