<script setup lang="ts">
import { useMedia } from '@/composables/media'
import type { Media } from '@/types'

defineProps<{
  item: Media
}>()

const { getStreamInfo } = useMedia()
</script>

<template>
  <UModal
    :title="item.file_name"
    :ui="{ footer: 'justify-end' }"
  >
    <slot>
      <UButton
        icon="i-lucide-eye"
        color="neutral"
        variant="ghost"
        size="sm"
      />
    </slot>

    <template #body>
      <dl class="divide-y divide-default">
        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">Name</dt>
          <dd class="text-sm font-medium">{{ item.name }}</dd>
        </div>

        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">File name</dt>
          <dd class="max-w-xs truncate text-sm font-medium">{{ item.file_name }}</dd>
        </div>

        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">Collection</dt>
          <dd class="text-sm font-medium">{{ item.collection_name }}</dd>
        </div>

        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">MIME type</dt>
          <dd class="text-sm font-medium">{{ item.mime_type }}</dd>
        </div>

        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">File size</dt>
          <dd class="text-sm font-medium">{{ item.file_size }}</dd>
        </div>

        <div class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0">
          <dt class="text-sm text-muted">Disk</dt>
          <dd class="text-sm font-medium">{{ item.disk }}</dd>
        </div>

        <div
          v-if="getStreamInfo(item).length"
          class="flex justify-between gap-4 py-2 first:pt-0 last:pb-0"
        >
          <dt class="text-sm text-muted">Stream info</dt>
          <dd class="text-sm font-medium">{{ getStreamInfo(item).join(' · ') }}</dd>
        </div>
      </dl>
    </template>

    <template #footer="{ close }">
      <div class="flex justify-end gap-2">
        <UButton
          label="Close"
          color="neutral"
          variant="soft"
          @click.prevent="close"
        />

        <UButton
          v-if="item.url"
          as="a"
          :href="item.url"
          target="_blank"
          rel="noopener noreferrer"
          label="View / Download"
          icon="i-lucide-download"
          variant="soft"
          color="neutral"
        />
      </div>
    </template>
  </UModal>
</template>
