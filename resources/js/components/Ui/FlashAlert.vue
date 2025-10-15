<script setup lang="ts">
import { useFlash } from '@/composables/flash'

const { title } = useFlash()
</script>

<template>
  <div
    v-if="title"
    class="sr-only"
  >
    {{ title }}
  </div>
</template>
