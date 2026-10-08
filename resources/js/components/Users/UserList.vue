<script setup lang="ts">
import ResourceRow from '@/components/Ui/ResourceRow.vue'
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
      class="flex flex-col gap-1"
      aria-hidden="true"
    >
      <div
        v-for="i in 3"
        :key="i"
        class="flex items-center gap-3 p-2"
      >
        <USkeleton class="size-10 shrink-0 rounded-full bg-(--glass)" />
        <div class="flex flex-1 flex-col gap-2">
          <USkeleton class="h-3.5 w-1/3 rounded-sm bg-(--glass)" />
          <USkeleton class="h-3 w-1/4 rounded-sm bg-(--glass)" />
        </div>
      </div>
    </div>

    <ul
      v-else-if="items.length"
      class="flex flex-col gap-1"
    >
      <ResourceRow
        v-for="item in items"
        :key="item.id"
        :title="item.name"
        :description="item.email"
        :class="{ 'opacity-60': item.deleted_at }"
      >
        <template #leading>
          <UAvatar
            :src="item.avatar ?? undefined"
            :alt="item.name"
            size="lg"
            class="shrink-0 bg-(--glass-strong) ring ring-(--glass-border)"
            loading="lazy"
          />
        </template>

        <template #meta>
          <UBadge
            v-if="item.deleted_at"
            label="Deleted"
            color="error"
            variant="subtle"
            size="sm"
            class="rounded-full"
          />

          <UBadge
            v-else-if="!item.email_verified_at"
            label="Unverified"
            color="warning"
            variant="subtle"
            size="sm"
            class="rounded-full"
          />

          <UBadge
            v-if="item.state"
            :label="item.state.label"
            :color="item.state.color"
            variant="subtle"
            size="sm"
            class="rounded-full"
          />
        </template>

        <template
          v-if="!item.deleted_at"
          #actions
        >
          <UserEditModal :item="item">
            <UButton
              icon="i-lucide-pencil"
              :aria-label="`Edit ${item.name}`"
              color="neutral"
              variant="ghost"
              size="sm"
              class="rounded-full text-muted hover:bg-(--glass-strong) hover:text-highlighted"
            />
          </UserEditModal>

          <UserDeleteModal :item="item">
            <UButton
              icon="i-lucide-trash-2"
              :aria-label="`Delete ${item.name}`"
              color="error"
              variant="ghost"
              size="sm"
              class="rounded-full"
            />
          </UserDeleteModal>
        </template>
      </ResourceRow>
    </ul>

    <UEmpty
      v-else
      icon="i-lucide-users"
      title="No users"
      description="Users you create appear here."
      variant="naked"
      class="py-24"
    />
  </div>
</template>
