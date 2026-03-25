<script setup lang="ts">
import { show, update } from '@/actions/App/Web/Groups/Controllers/GroupController'
import GroupDeleteModal from '@/components/Groups/GroupDeleteModal.vue'
import AppHeader from '@/components/Ui/AppHeader.vue'
import type { Group } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{
  group: Group
}>()

const form = useForm(update(props.group.id), {
  name: props.group.name ?? props.group.title,
  content: props.group.content || null,
})

const onSubmit = () =>
  form.submit({
    preserveState: true,
    replace: true,
  })
</script>

<template>
  <Head :title="group.title" />

  <UDashboardPanel id="collection-edit">
    <template #header>
      <AppHeader />
    </template>

    <template #body>
      <UPage class="mx-auto w-full max-w-6xl px-4 sm:px-6">
        <UPageHeader
          :title="group.title"
          :links="[{ label: 'View collection', icon: 'i-lucide-eye', to: show.url(group.id) }]"
        />

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
                  label="Content"
                  :error="form.errors.content"
                >
                  <UTextarea
                    v-model="form.content"
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
                <p class="text-error text-sm font-semibold">Delete collection</p>
                <p class="text-muted text-sm">Permanently remove this collection and all associated data.</p>

                <GroupDeleteModal :item="group">
                  <UButton
                    label="Delete collection"
                    icon="i-lucide-trash"
                    color="error"
                    variant="soft"
                    size="sm"
                    class="w-fit"
                  />
                </GroupDeleteModal>
              </div>
            </template>
          </UPageCard>
        </UPageBody>
      </UPage>
    </template>
  </UDashboardPanel>
</template>
