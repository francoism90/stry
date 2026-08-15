import type { QueryFilter, QueryValue } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { computed, reactive, toValue, type MaybeRefOrGetter } from 'vue'

export function useQuery(options: {
  filter?: MaybeRefOrGetter<QueryFilter | undefined>
  query?: MaybeRefOrGetter<QueryValue | undefined>
  sort?: MaybeRefOrGetter<QueryValue | undefined>
  only?: string[]
  reset?: string[]
}) {
  const search = computed(() => usePage().props.search)

  const form = reactive({
    filter: toValue(options.filter) ?? {},
    query: toValue(options.query ?? search.value) ?? '',
    sort: toValue(options.sort) ?? null,
    page: { number: 1 },
  })

  const onSubmit = () => {
    router.reload({
      data: form,
      only: toValue(options.only) ?? ['page', 'items', 'search', 'filter', 'sort', 'query'],
      reset: toValue(options.reset) ?? ['page', 'items'],
    })
  }

  return {
    form,
    search,
    onSubmit,
  }
}
