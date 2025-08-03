<script setup lang="ts">
import TagCard from '@/components/Tag/TagCard.vue'
import Page from '@/components/Ui/Page.vue'
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

const fetch = () => router.get(usePage().props.location, { page: nextPage.value })
</script>

<template>
  <Page>
    <PageBody>
      <slot />

      <Deferred data="items">
        <template #fallback>
          <div class="sr-only">Loading items...</div>
        </template>

        <PageSection>
          <div
            v-if="items?.data?.length"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3"
          >
            <TagCard
              v-for="item in items.data"
              :key="item.id"
              :item
            />
          </div>
        </PageSection>

        <WhenVisible
          :always="hasPages"
          :buffer="100"
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
            class="flex h-20 w-full items-center justify-center"
          >
            <UButton
              label="Load more"
              variant="soft"
              @click="fetch"
            />
          </div>
        </WhenVisible>
      </Deferred>
    </PageBody>
  </Page>
</template>
