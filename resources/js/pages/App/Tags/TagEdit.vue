<script setup lang="ts">
import { update } from '@/actions/App/Web/Tags/Controllers/TagController'
import TagDeleteModal from '@/components/Tags/TagDeleteModal.vue'
import { useTags } from '@/composables/tags'
import TagLayout from '@/layouts/App/TagLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import type { Tag, TagMenuItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  tag: Tag
  types: SelectMenuItem[]
}>()

defineOptions({ layout: [DefaultLayout, TagLayout] })

const { items, filter } = useTags(props.tag.related || [])

const form = useForm('put', update.url(props.tag.id), {
  name: props.tag.name,
  type: props.tag.type || null,
  related: props.tag.related || [],
  description: props.tag.description || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <Head :title="tag.name" />

  <UPageBody>
    <UForm
      :state="form"
      class="flex flex-col py-3"
      loading-auto
      @submit="onSubmit"
    >
      <UPageCard
        variant="subtle"
        orientation="vertical"
        :ui="{
          body: 'flex w-full flex-col gap-3',
        }"
      >
        <template #body>
          <UFormField
            label="Name"
            required
            :error="form.errors.name"
          >
            <UInput
              v-model="form.name"
              :model-modifiers="{ string: true, trim: true }"
              autofocus
              autocapitalize="words"
            />
          </UFormField>

          <USeparator />

          <UFormField
            label="Type"
            required
            :error="form.errors.type"
          >
            <USelect
              v-model="form.type"
              :items="types"
              label-key="label"
              value-key="value"
            />
          </UFormField>

          <USeparator />

          <UFormField
            label="Related tags"
            :error="form.errors.related"
          >
            <USelectMenu
              v-model="form.related as TagMenuItem[]"
              :items="items as TagMenuItem[]"
              :ignore-filter="true"
              label-key="name"
              multiple
              class="w-full"
              placeholder="Add related tags"
              @update:search-term="(value: string) => filter({ query: { search: value } })"
            >
              <template #item-label="{ item }">
                {{ item.name }}

                <span class="text-muted">
                  {{ item.category }}
                </span>
              </template>
            </USelectMenu>
          </UFormField>

          <USeparator />

          <UFormField
            label="Description"
            :error="form.errors.description"
          >
            <UTextarea
              v-model="form.description"
              :model-modifiers="{ nullable: true, string: true, trim: true }"
              :rows="5"
              autoresize
              placeholder="Enter markdown"
              class="w-full"
            />
          </UFormField>
        </template>

        <template #footer>
          <UButton
            label="Save changes"
            type="submit"
            color="primary"
            variant="soft"
            loading-auto
          />
        </template>
      </UPageCard>
    </UForm>

    <UPageCard
      variant="subtle"
      orientation="vertical"
      :ui="{
        root: 'ring-error/25 from-error/5 bg-linear-to-r to-transparent',
        body: 'flex flex-col gap-3',
      }"
    >
      <template #body>
        <div class="flex flex-col gap-2">
          <p class="text-error text-sm font-semibold">Delete tag</p>
          <p class="text-muted text-sm">Permanently remove this tag and all associated data.</p>

          <TagDeleteModal :item="tag">
            <UButton
              label="Delete tag"
              icon="i-lucide-trash"
              color="error"
              variant="soft"
              size="sm"
              class="w-fit"
            />
          </TagDeleteModal>
        </div>
      </template>
    </UPageCard>
  </UPageBody>
</template>
