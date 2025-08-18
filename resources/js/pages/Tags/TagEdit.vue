<script setup lang="ts">
import { update } from '@/actions/App/Web/Tags/Controllers/TagController'
import TagDeleteModal from '@/components/Tag/TagDeleteModal.vue'
import FlashAlert from '@/components/Ui/FlashAlert.vue'
import { useAppearance } from '@/composables/appearance'
import { useTagInput } from '@/composables/taginput'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagResource from '@/layouts/Tag/TagResource.vue'
import type { Tag, TagMenuItem } from '@/types'
import { router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  tag: Tag
  types: string[]
}

defineOptions({ layout: [DefaultLayout, TagResource] })

const props = defineProps<Props>()

const { title } = useAppearance()
const { data, query } = useTagInput(props.tag.related || [])

const form = useForm('put', update.url({ tag: props.tag.id }), props.tag)

const submit = async () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => router.reload({ except: ['flash'] }),
  })
</script>

<template>
  <UForm
    :state="form"
    @submit.prevent="submit"
    class="flex flex-col gap-4 pt-6"
  >
    <FlashAlert />

    {{ form.related }}

    <UFormField
      label="Name"
      name="name"
      required
      :error="form.errors.name"
    >
      <UInput
        v-model.trim="form.name"
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
        label="Tags"
        name="tags"
        :error="form.errors.tags"
      >
        <USelectMenu
          v-model="form.related as TagMenuItem[]"
          :items="data as TagMenuItem[]"
          label-key="name"
          multiple
          class="w-full"
          placeholder="Related tags..."
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
    </div>

    <UFormField
      label="Description"
      name="description"
      :error="form.errors.description"
    >
      <UTextarea
        v-model="form.description"
        class="w-full"
        :ui="{
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
