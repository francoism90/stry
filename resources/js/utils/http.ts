import axios from 'axios'

export type ApiResponse<T> = { data: T; success?: boolean; status?: number }

export const http = axios.create({
  baseURL: import.meta.env.VITE_APP_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
})
