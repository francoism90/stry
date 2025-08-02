<script setup lang="ts">
import { update } from '@/actions/App/Web/Tags/Controllers/TagController'
import FlashAlert from '@/components/Ui/FlashAlert.vue'
import { useAppearance } from '@/composables/appearance'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import TagResource from '@/layouts/Tag/TagResource.vue'
import type { Tag } from '@/types'
import { router } from '@inertiajs/vue3'
import { useForm } from 'laravel-precognition-vue-inertia'

interface Props {
  tag: Tag
  types: string[]
}

defineOptions({ layout: [DefaultLayout, TagResource] })

const props = defineProps<Props>()

const { title } = useAppearance()

const form = useForm('put', update.url({ tag: props.tag.id }), props.tag)

const submit = async () =>
  form.submit({
    preserveState: true,
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

    <UFormField
      label="Name"
      name="name"
      required
      :error="form.errors.name"
    >
      <UInput
        v-model.trim="form.name"
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
            @click="form.name = title(form.name)"
          />
        </template>
      </UInput>
    </UFormField>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <UFormField
        label="Type"
        name="type"
        :error="form.errors.type"
      >
        <USelectMenu
          v-model="form.type"
          value-key="value"
          :items="types"
          class="w-full"
        />
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

    <UButton
      label="Save changes"
      type="submit"
      variant="soft"
      class="self-end"
      loading-auto
    />
  </UForm>
</template>
