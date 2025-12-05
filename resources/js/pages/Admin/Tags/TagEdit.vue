<script setup lang="ts">
import { update } from '@/actions/App/Admin/Tags/Controllers/TagController'
import TagController from '@/actions/App/Client/Tags/Controllers/TagController'
import TagDeleteModal from '@/components/Tags/TagDeleteModal.vue'
import { useTags } from '@/composables/tags'
import TagLayout from '@/layouts/Admin/TagLayout.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { Tag, TagMenuItem } from '@/types'
import { capitalize } from '@/utils/case'
import type { SelectMenuItem } from '@nuxt/ui'
import { useForm } from 'laravel-precognition-vue-inertia'

const props = defineProps<{
  tag: Tag
  types: SelectMenuItem[]
}>()

defineOptions({ layout: [DashboardLayout, TagLayout] })

const { items, filter } = useTags(props.tag.related || [])
const toast = useToast()

const form = useForm('put', update.url(props.tag.id), {
  name: props.tag.name,
  type: props.tag.type,
  related: props.tag.related || [],
  description: props.tag.description || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
    onSuccess: () =>
      toast.add({
        title: 'Success',
        description: 'The tag has been updated.',
        icon: 'i-lucide-check',
        color: 'success',
      }),
  })
</script>

<template>
  <UForm
    id="general"
    :state="form"
    class="mx-auto flex w-full flex-col gap-6 sm:gap-9 lg:max-w-2xl lg:py-3"
    loading-auto
    @submit="onSubmit"
  >
    <UPageCard
      title="Tag details"
      variant="naked"
      orientation="horizontal"
    >
      <div class="flex items-center gap-2 lg:ms-auto">
        <UButton
          label="View tag"
          :to="TagController.url(tag.id)"
          color="neutral"
          variant="soft"
        />

        <UButton
          form="general"
          label="Save changes"
          type="submit"
          color="primary"
          variant="soft"
          loading-auto
        />
      </div>
    </UPageCard>

    <UPageCard variant="subtle">
      <UFormField
        label="Name"
        required
        :error="form.errors.name"
      >
        <UInput
          v-model="form.name"
          :model-modifiers="{ string: true, trim: true }"
          :ui="{ trailing: 'pe-1' }"
          autofocus
          autocapitalize="words"
        >
          <template #trailing>
            <UButton
              color="neutral"
              variant="link"
              size="sm"
              icon="i-lucide-wand-sparkles"
              aria-label="Capitalize"
              @click.prevent="form.name = capitalize(form.name)"
            />
          </template>
        </UInput>
      </UFormField>

      <USeparator />

      <UFormField
        label="Type"
        :error="form.errors.type"
      >
        <USelectMenu
          v-model="form.type"
          value-key="value"
          :items="types"
          class="w-full"
        />
      </UFormField>

      <USeparator />

      <UFormField
        label="Related tags"
        :error="form.errors.related"
      >
        <USelectMenu
          v-model="form.related as TagMenuItem[]"
          :model-modifiers="{ nullable: true }"
          :items="items as TagMenuItem[]"
          :ignore-filter="true"
          label-key="name"
          multiple
          class="w-full"
          placeholder="Add tags"
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
    </UPageCard>

    <UPageCard
      title="Delete tag"
      description="Once you delete a tag, there is no going back. Please be certain."
      class="bg-linear-to-tl from-error/10 from-5% to-default"
    >
      <template #footer>
        <TagDeleteModal :item="tag" />
      </template>
    </UPageCard>
  </UForm>
</template>
