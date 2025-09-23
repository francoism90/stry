<script setup lang="ts">
interface Props {
  title: string | undefined
}

defineProps<Props>()
</script>

<template>
  <UPageFeature
    class="line-clamp-2 font-serif text-lg font-semibold tracking-tight"
    :title="title"
  />
</template>
