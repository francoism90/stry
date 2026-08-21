<script setup lang="ts">
import UserDeleteModal from '@/components/Users/UserDeleteModal.vue'
import UserEditModal from '@/components/Users/UserEditModal.vue'
import type { User } from '@/types'

defineProps<{
  items?: User[] | undefined
}>()
</script>

<template>
  <div class="flex flex-col gap-3">
    <div
      v-if="items === undefined"
      class="flex flex-col gap-2"
    >
      <USkeleton
        v-for="i in 3"
        :key="i"
        class="h-14 w-full rounded-md"
      />
    </div>

    <UPageList
      v-else-if="items.length"
      divide
    >
      <UPageCard
        v-for="item in items"
        :key="item.id"
        variant="naked"
        class="py-3 first:pt-0 last:pb-0"
      >
        <div class="flex items-center justify-between">
          <UUser
            :name="item.name"
            :description="item.email"
            :avatar="{
              src: item.avatar ?? undefined,
              alt: item.name,
              loading: 'lazy',
              decoding: 'async',
              class: 'rounded-sm size-10 me-1',
            }"
          />

          <div class="z-10 flex items-center gap-2">
            <UBadge
              v-if="item.deleted_at"
              label="Deleted"
              color="error"
              variant="subtle"
              size="sm"
            />

            <UBadge
              v-else-if="!item.email_verified_at"
              label="Unverified"
              color="warning"
              variant="subtle"
              size="sm"
            />

            <UBadge
              v-if="item.state"
              :label="item.state.label"
              :color="item.state.color"
              variant="subtle"
              size="sm"
            />

            <UserEditModal
              v-if="!item.deleted_at"
              :item="item"
            />

            <UserDeleteModal
              v-if="!item.deleted_at"
              :item="item"
            />
          </div>
        </div>
      </UPageCard>
    </UPageList>
  </div>
</template>
