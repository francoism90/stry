import { index } from '@/actions/App/Api/Tags/Controllers/TagController'
import type { Tag } from '@/types'
import { isEmpty } from '@/utils/helpers'
import { http } from '@/utils/http'
import { type QueryParams } from '@/wayfinder'
import { readonly, ref, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useTags(selected: MaybeRefOrGetter<Tag[]> = [], query: MaybeRefOrGetter<QueryParams> = {}) {
  const state = ref<Tag[]>([])

  const fetch = async (query?: QueryParams) => http.get<Tag[]>(index.url({ query }))

  watchEffect(async () => {
    const items = toValue(selected)
    const params = toValue(query)

    state.value = !isEmpty(params) ? (await fetch(params)).data : items
  })

  return {
    state: readonly(state),
  }
}
