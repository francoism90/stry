<script lang="ts" setup>
import NavBar from '@/components/Ui/NavBar.vue';
</script>

<template>
  <UApp>
    <div class="relative flex h-dvh min-h-dvh flex-col overflow-x-hidden">
      <NavBar />

      <div class="flex-1 contain-inline-size contain-layout contain-paint contain-style">
        <slot />
      </div>
    </div>
  </UApp>
</template>
