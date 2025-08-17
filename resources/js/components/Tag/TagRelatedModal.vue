<script setup lang="ts">
import { store } from '@/actions/App/Web/Tags/Controllers/TagRelatedController'
import { useTagInput } from '@/composables/taginput'
import type { Tag, TagMenuItem } from '@/types'
import { router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'
import FlashAlert from '../Ui/FlashAlert.vue'

interface Props {
  item: Tag
}

const props = defineProps<Props>()

const { data, query } = useTagInput([])

const form = useForm('post', store.url({ tag: props.item.id }), props.item)

const submit = async () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => router.reload({ except: ['flash'] }),
  })
</script>

<template>
  <UModal
    :title="item.name"
    :description="item.category"
    :ui="{ footer: 'justify-end' }"
  >
    <UButton
      label="Attach tags"
      variant="soft"
    />

    <template #body>
      <UForm
        :state="form"
        @submit.prevent="submit"
        class="flex flex-col gap-4"
      >
        <FlashAlert />

        <UFormField
          name="related"
          :error="form.errors.related"
        >
          <USelectMenu
            v-model="form.related as TagMenuItem[]"
            :items="data as TagMenuItem[]"
            label-key="name"
            multiple
            placeholder="Attach tags..."
            class="w-full flex-1"
            @update:search-term="(value) => query({ search: value })"
          >
            <template #item-label="{ item }">
              {{ item.name }}

              <span class="text-muted">
                {{ item.category }}
              </span>
            </template>
          </USelectMenu>
        </UFormField>
      </UForm>
    </template>

    <template #footer="{ close }">
      <UButton
        label="Cancel"
        color="neutral"
        variant="soft"
        @click.prevent="close"
      />

      <UButton
        label="Save changes"
        variant="soft"
        color="primary"
        loading-auto
        @click.prevent="submit"
      />
    </template>
  </UModal>
</template>
