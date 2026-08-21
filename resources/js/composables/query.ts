import type { OptionItem, QueryFilter, QueryValue } from '@/types'
import { router, usePage } from '@inertiajs/vue3'
import { computed, provide, reactive, toValue, watchEffect, type InjectionKey, type MaybeRefOrGetter } from 'vue'

export type QueryContext = ReturnType<typeof useQuery>

// FilterToolbar (scope/sort) and GlobalSearch live in separate parts of the
// layout tree, so they share one useQuery() instance via provide/inject
// instead of each keeping its own copy of the query state.
export const QueryInjectionKey: InjectionKey<QueryContext> = Symbol('query')

export function useQuery(options: {
  filter?: MaybeRefOrGetter<QueryFilter | undefined>
  query?: MaybeRefOrGetter<QueryValue | undefined>
  sort?: MaybeRefOrGetter<QueryValue | undefined>
  only?: string[]
  reset?: string[]
}) {
  const form = reactive({
    filter: {} as QueryFilter,
    query: null as QueryValue,
    sort: null as QueryValue,
    page: { number: 1 },
  })

  // Layouts persist across visits, so re-sync the form whenever the
  // page-provided filter/sort/query change instead of only on first mount.
  watchEffect(() => {
    form.filter = toValue(options.filter) ?? {}
    form.query = toValue(options.query) ?? null
    form.sort = toValue(options.sort) ?? null
  })

  const onSubmit = () => {
    router.reload({
      data: form,
      reset: toValue(options.reset) ?? ['items', 'page'],
      only: toValue(options.only) ?? ['items', 'page', 'filter', 'sort', 'query'],
    })
  }

  return {
    form,
    onSubmit,
  }
}

const firstValue = (items: OptionItem[] | undefined) => {
  if (!items || items.length === 0) return null
  return items[0].value
}

export function provideQuery(props: {
  scopes?: OptionItem[]
  sorters?: OptionItem[]
  filter?: QueryFilter
  sort?: QueryValue
  query?: QueryValue
}) {
  const search = computed(() => usePage().props.search)

  const query = useQuery({
    filter: () => props.filter ?? { scope: firstValue(props.scopes) },
    sort: () => props.sort ?? firstValue(props.sorters),
    query: () => props.query ?? search.value ?? null,
  })

  provide(QueryInjectionKey, query)

  return query
}
