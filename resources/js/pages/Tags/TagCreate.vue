<script setup lang="ts">
import { store } from '@/actions/App/Modules/Tags/Controllers/TagController'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'

defineProps<{
  types: SelectMenuItem[]
}>()

defineOptions({
  layout: [
    [AppLayout, { title: 'Create tag' }],
    [ResourceLayout, { id: 'tags.create' }],
  ],
})

const form = useForm(store(), {
  name: null,
  type: null,
  description: null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <Head title="Create tag" />

  <UPage>
    <UPageHeader
      title="Create a new tag"
      description="Tags are used to categorize and organize content within the application."
      headline="Tags"
    />

    <UPageBody>
      <UForm
        :state="form"
        loading-auto
        class="space-y-4"
        @submit="onSubmit"
      >
        <UPageCard
          title="Tag details"
          description="Provide the necessary information to create a new tag."
          variant="naked"
          class="mx-auto w-full max-w-2xl"
        >
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
              placeholder="Enter tag name"
            />
          </UFormField>

          <UFormField
            label="Type"
            required
            :error="form.errors.type"
          >
            <USelect
              v-model="form.type"
              :items="types"
              placeholder="Select tag type"
              label-key="label"
              value-key="value"
            />
          </UFormField>

          <UFormField
            label="Description"
            :error="form.errors.description"
          >
            <UTextarea
              v-model="form.description"
              :model-modifiers="{ nullable: true, string: true, trim: true }"
              :rows="5"
              autoresize
              placeholder="Tag description (optional - markdown)"
            />
          </UFormField>

          <UButton
            label="Create tag"
            type="submit"
            variant="soft"
            class="w-fit"
            loading-auto
          />
        </UPageCard>
      </UForm>
    </UPageBody>
  </UPage>
</template>
