<script setup lang="ts">
import { edit, update } from '@/actions/App/Web/Videos/Controllers/VideoController'
import FlashAlert from '@/components/Ui/FlashAlert.vue'
import Page from '@/components/Ui/Page.vue'
import PageBody from '@/components/Ui/PageBody.vue'
import PageFeature from '@/components/Ui/PageFeature.vue'
import type { Video } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import type { SelectMenuItem, TabsItem } from '@nuxt/ui'
import { reactivePick } from '@vueuse/core'
import { useForm } from 'laravel-precognition-vue-inertia'
import { ref } from 'vue'

interface Props {
  item: Video
  tags: SelectMenuItem[] | null
}

const props = defineProps<Props>()

const items = ref<TabsItem[]>([
  {
    label: 'General',
    slot: 'general' as const,
  },
  {
    label: 'Playlists',
    slot: 'playlists' as const,
  },
])

const form = useForm('put', update.url({ video: props.item.id }), reactivePick(props.item, 'name', 'episode', 'season', 'part', 'summary', 'released', 'tags'))

const onSearch = async (search: string) => router.get(edit.url({ video: props.item.id }), { search }, { preserveState: true, preserveScroll: true })

const submit = async () =>
  form.submit({
    preserveScroll: true,
    onSuccess: () => router.reload({ except: ['flash'] }),
  })
</script>

<template>
  <Head :title="item.name" />

  <Page>
    <PageBody>
      <PageFeature
        :title="item.name"
        :description="item.summary"
      />

      <UTabs
        :items="items"
        variant="link"
        class="w-full gap-4"
      >
        <template #general>
          <UForm
            :state="form"
            @submit.prevent="submit"
            class="flex flex-col gap-4"
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
              />
            </UFormField>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
              <UFormField
                label="Episode"
                name="episode"
                :error="form.errors.episode"
              >
                <UInput
                  v-model.trim="form.episode"
                  placeholder="1"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                label="Season"
                name="season"
                :error="form.errors.season"
              >
                <UInput
                  v-model.trim="form.season"
                  placeholder="1"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                label="Part"
                name="part"
                :error="form.errors.part"
              >
                <UInput
                  v-model.trim="form.part"
                  placeholder="1"
                  class="w-full"
                />
              </UFormField>

              <UFormField
                label="Released"
                name="released_at"
                :error="form.errors.released"
              >
                <UInput
                  v-model.trim="form.released"
                  placeholder="YYYY-MM-DD"
                  class="w-full"
                />
              </UFormField>
            </div>

            <UFormField
              label="Tags"
              name="tags"
              :error="form.errors.tags"
            >
              <USelectMenu
                v-model="form.tags"
                :items="tags"
                label-key="name"
                multiple
                class="w-full"
                @update:search-term="onSearch"
              >
                <template #item-label="{ item }">
                  {{ item.name }}

                  <span class="text-muted">
                    {{ item.type }}
                  </span>
                </template>
              </USelectMenu>
            </UFormField>

            <UFormField
              label="Summary"
              name="summary"
              :error="form.errors.summary"
            >
              <UTextarea
                v-model="form.summary"
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

        <template #playlists>
          <UForm
            :state="form"
            @submit.prevent="submit"
            class="flex flex-col gap-4"
          >
            <UButton
              label="Change password"
              type="submit"
              variant="soft"
              class="self-end"
            />
          </UForm>
        </template>
      </UTabs>
    </PageBody>
  </Page>
</template>
