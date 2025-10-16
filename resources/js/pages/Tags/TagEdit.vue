<script setup lang="ts">
import { update } from '@/actions/App/Web/Tags/Controllers/TagController'
import TagDeleteModal from '@/components/Tag/TagDeleteModal.vue'
import { useAppearance } from '@/composables/appearance'
import { useTagInput } from '@/composables/taginput'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagResource from '@/layouts/Tag/TagResource.vue'
import type { Tag, TagMenuItem } from '@/types'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  tag: Tag
  types: string[]
}

defineOptions({ layout: [DefaultLayout, TagResource] })

const props = defineProps<Props>()

const { data, query } = useTagInput(props.tag.related || [])
const { title } = useAppearance()
const toast = useToast()

const form = useForm('put', update.url({ tag: props.tag.id }), props.tag)

const onSubmit = async () => {
  await form.submit({
    preserveState: true,
    replace: true,
  })

  toast.add({
    title: 'Tag updated!',
    description: 'Your changes have been saved successfully.',
  })
}
</script>

<template>
  <UForm
    :state="form"
    @submit="onSubmit"
    class="flex flex-col gap-4"
  >
    <UFormField
      label="Name"
      name="name"
      required
      :error="form.errors.name"
    >
      <UInput
        v-model="form.name"
        :model-modifiers="{ string: true, trim: true }"
        autofocus
        autocapitalize="words"
        class="w-full"
        :ui="{ trailing: 'pe-1' }"
      >
        <template #trailing>
          <UButton
            color="neutral"
            variant="link"
            size="sm"
            icon="i-lucide-wand-sparkles"
            aria-label="Format name"
            @click.prevent="form.name = title(form.name)"
          />
        </template>
      </UInput>
    </UFormField>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <UFormField
        label="Type"
        name="type"
        required
        :error="form.errors.type"
      >
        <USelectMenu
          v-model="form.type"
          value-key="value"
          :items="types"
          class="w-full"
        />
      </UFormField>

      <UFormField
        label="Related"
        name="related"
        :error="form.errors.related"
      >
        <USelectMenu
          v-model="form.related as TagMenuItem[]"
          :items="data as TagMenuItem[]"
          :ignore-filter="true"
          label-key="name"
          multiple
          class="w-full"
          placeholder="Related tags..."
          @update:search-term="(value: string) => query({ search: value })"
        >
          <template #item-label="{ item }">
            {{ item.name }}

            <span class="text-muted">
              {{ item.category }}
            </span>
          </template>
        </USelectMenu>
      </UFormField>
    </div>

    <UFormField
      label="Description"
      name="description"
      :error="form.errors.description"
    >
      <UTextarea
        v-model="form.description"
        :model-modifiers="{ nullable: true, string: true, trim: true }"
        :ui="{
          root: 'w-full',
          base: 'h-32',
        }"
      />
    </UFormField>

    <div class="flex gap-2 self-end">
      <TagDeleteModal :item="tag" />

      <UButton
        label="Save changes"
        type="submit"
        variant="soft"
        loading-auto
      />
    </div>
  </UForm>
</template>
