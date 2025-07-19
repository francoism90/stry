<script setup lang="ts">
import NavMenu from '@/components/Ui/NavMenu.vue'
</script>

<template>
  <header class="sticky inset-x-0 top-0 z-30 flex h-20 min-h-20 bg-default">
    <UContainer>
      <NavMenu />
      <div class="h-[1px] bg-gradient-to-r from-neutral-500/0 via-primary-500/75 to-neutral-500/0"></div>
    </UContainer>
  </header>
</template>
