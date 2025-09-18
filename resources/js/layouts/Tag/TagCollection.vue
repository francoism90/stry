<script setup lang="ts">
import TagCard from '@/components/Tag/TagCard.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageSection from '@/components/Ui/PageSection.vue'
import { usePagination } from '@/composables/pagination'
import type { Tags } from '@/types'
import { Deferred, router, usePage, WhenVisible } from '@inertiajs/vue3'

interface Props {
  items?: Tags
}

defineProps<Props>()

const { hasPages, nextPage } = usePagination()

const fetch = async () => router.get(usePage().props.path, { page: nextPage.value })
</script>

<template>
  <PageBody>
    <slot />

    <Deferred data="items">
      <template #fallback>
        <div class="sr-only">Loading items...</div>
      </template>

      <PageSection>
        <div
          v-if="items?.data?.length"
          class="grid grid-cols-1 gap-4 py-2 sm:grid-cols-2 md:grid-cols-3"
        >
          <TagCard
            v-for="item in items.data"
            :key="item.id"
            :item
          />
        </div>

        <div
          v-else
          class="py-4 text-center text-sm text-gray-500 dark:text-gray-400"
        >
          No items found.
        </div>

        <WhenVisible
          :always="hasPages"
          :buffer="200"
          :params="{
            only: ['items'],
            data: hasPages ? { page: nextPage } : {},
          }"
        >
          <template #fallback>
            <div class="sr-only">Loading more...</div>
          </template>

          <div
            v-if="hasPages"
            class="flex h-12 w-full items-center justify-center"
          >
            <UButton
              label="Load more"
              variant="soft"
              loading-auto
              @click.prevent="fetch"
            />
          </div>
        </WhenVisible>
      </PageSection>
    </Deferred>
  </PageBody>
</template>
