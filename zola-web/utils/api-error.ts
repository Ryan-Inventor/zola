import type { ApiErrorResponse, AuthUser, LoginSuccessResponse } from '~/types/auth'

export function getApiErrorMessage(error: unknown, fallback = 'Une erreur est survenue.'): string {
  if (error && typeof error === 'object' && 'data' in error) {
    const data = (error as { data?: ApiErrorResponse }).data
    if (data?.message) {
      return data.message
    }
  }

  return fallback
}
