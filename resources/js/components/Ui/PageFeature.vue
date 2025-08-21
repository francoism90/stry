<script setup lang="ts">
interface Props {
  title: string | null
}

defineProps<Props>()
</script>

<template>
  <div class="flex flex-col">
    <h1 class="line-clamp-2 font-serif text-lg font-semibold tracking-tight">
      {{ title }}
    </h1>

    <slot />
  </div>
</template>
