<script setup lang="ts">
import { update } from '@/actions/Modules/Web/Tags/Controllers/TagController'
import TagDeleteModal from '@/components/Tags/TagDeleteModal.vue'
import FormModal from '@/components/Ui/FormModal.vue'
import { useTags } from '@/composables/tags'
import type { Tag, TagMenuItem } from '@/types'
import { useForm } from '@inertiajs/vue3'
import type { TabsItem } from '@nuxt/ui'

const props = withDefaults(
  defineProps<{
    item: Tag
    trigger?: boolean
  }>(),
  {
    trigger: true,
  },
)

const open = defineModel<boolean>('open')

const tabs: TabsItem[] = [
  { label: 'General', icon: 'i-lucide-file-text', slot: 'general' },
  { label: 'Danger zone', icon: 'i-lucide-triangle-alert', slot: 'manage' },
]

const { items, types, filter } = useTags(props.item.related || [])

const form = useForm(update(props.item.id), {
  name: props.item.name,
  type: props.item.type,
  related: props.item.related || [],
  description: props.item.description || null,
  avatar: null as File | null,
  remove_avatar: false,
})

const onSubmit = (close: () => void) => {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('avatar', 'remove_avatar')
      close()
    },
  }

  // Files can only be sent as multipart POST, so spoof the PATCH method for uploads.
  if (form.avatar) {
    form.transform((data) => ({ ...data, _method: 'patch' })).post(update.url(props.item.id), options)

    return
  }

  form.transform((data) => data).submit(options)
}
</script>

<template>
  <FormModal
    v-model:open="open"
    :title="`Edit ${item.name}`"
    :processing="form.processing"
    :tabs="tabs"
    @submit="onSubmit"
  >
    <template
      v-if="trigger"
      #default
    >
      <slot>
        <UButton
          icon="i-lucide-pencil"
          :aria-label="`Edit ${item.name}`"
          color="neutral"
          variant="ghost"
          size="sm"
        />
      </slot>
    </template>

    <template #general>
      <UForm
        :state="form"
        class="flex flex-col gap-4"
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
          />
        </UFormField>

        <UFormField
          label="Type"
          required
          :error="form.errors.type"
        >
          <USelectMenu
            v-model="form.type"
            value-key="value"
            :items="types"
            placeholder="Select a type"
            class="w-full"
          />
        </UFormField>

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
            @update:search-term="(value: string) => filter({ query: { query: value } })"
          >
            <template #item-label="{ item }">
              {{ item.name }}

              <span class="text-muted">
                {{ item.category }}
              </span>
            </template>
          </USelectMenu>
        </UFormField>

        <UFormField
          label="Picture"
          description="Without a picture, a thumbnail from one of the tag's videos is shown."
          :error="form.errors.avatar"
        >
          <div
            v-if="item.avatar && !form.avatar && !form.remove_avatar"
            class="relative mt-2 overflow-hidden rounded-lg"
          >
            <img
              :src="item.avatar"
              :alt="`Picture of ${item.name}`"
              class="aspect-video w-full object-cover"
            />

            <UButton
              label="Remove"
              icon="i-lucide-trash"
              color="neutral"
              variant="solid"
              size="xs"
              class="absolute end-2 top-2 bg-black/60 text-white backdrop-blur-sm hover:bg-black/75"
              @click="form.remove_avatar = true"
            />
          </div>

          <UFileUpload
            v-else
            v-model="form.avatar"
            accept="image/*"
            icon="i-lucide-image"
            label="Drop a picture here"
            description="JPG, PNG, WebP or AVIF, up to 10 MB"
            class="mt-2 aspect-video w-full"
          />
        </UFormField>

        <UFormField
          label="Description"
          :error="form.errors.description"
        >
          <UTextarea
            v-model="form.description"
            :model-modifiers="{ nullable: true, string: true, trim: true }"
            :rows="3"
            autoresize
            placeholder="Enter markdown (optional)"
            class="w-full"
          />
        </UFormField>
      </UForm>
    </template>

    <template #manage>
      <div class="flex flex-col gap-2">
        <p class="text-sm font-semibold text-error">Delete tag</p>
        <p class="text-sm text-muted">This may permanently remove this tag and all associated data.</p>

        <TagDeleteModal :item="item">
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
  </FormModal>
</template>
