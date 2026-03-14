<script setup lang="ts">
import GroupCard from '@/components/Groups/GroupCard.vue';
import type { Group } from '@/types';

defineProps<{
  items: Group[] | undefined
}>()
</script>

<template>
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
    <GroupCard
      v-for="item in items"
      :key="item.id"
      :item="item"
    />
  </div>
</template>
