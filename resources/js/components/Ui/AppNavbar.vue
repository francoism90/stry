<script setup lang="ts">
import { router } from '@inertiajs/vue3'

const back = () => (window && window.history?.length > 1 ? window.history.back() : router.visit('/'))
</script>

<template>
  <UButton
    label="Back"
    @click="back"
    variant="outline"
    size="xs"
    color="neutral"
    class="px-3 py-2"
    icon="i-lucide-arrow-left"
  />
</template>
