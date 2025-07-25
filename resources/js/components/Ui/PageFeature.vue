<script setup lang="ts">
interface Props {
  title?: string
  description?: string
}

defineProps<Props>()
</script>

<template>
  <section class="relative flex flex-col gap-1.5">
    <h1
      v-if="title?.length"
      class="line-clamp-2 font-serif font-semibold tracking-tight"
    >
      {{ title }}
    </h1>

    <div v-if="$slots.title">
      <slot name="title" />
    </div>

    <p
      v-if="description?.length"
      class="text-sm text-neutral-500"
    >
      {{ description }}
    </p>

    <div v-if="$slots.description">
      <slot name="description" />
    </div>
  </section>
</template>
