<script setup lang="ts">
import { store } from '@/actions/App/Modules/Tags/Controllers/TagController'
import ResourceLayout from '@/layouts/App/ResourceLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineOptions({
  layout: [
    [AppLayout, { title: 'Create tag' }],
    [ResourceLayout, { id: 'tags.create' }],
  ],
})

const form = useForm(store(), {
  name: '',
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
              :ui="{ trailing: 'pe-1' }"
              autofocus
              autocapitalize="words"
              placeholder="Enter tag name"
            />
          </UFormField>

          <UButton
            label="Create tag"
            type="submit"
            variant="soft"
            color="primary"
            class="w-fit"
            loading-auto
          />
        </UPageCard>
      </UForm>
    </UPageBody>
  </UPage>
</template>
