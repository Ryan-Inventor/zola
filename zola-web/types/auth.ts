export type UserRole = 'admin' | 'owner' | 'superviseur'

export type UserStatus = 'pending' | 'active' | 'suspended'

export interface AuthUser {
  id: number
  name: string
  phone: string
  email: string | null
  role: UserRole
  status: UserStatus
}

export interface LoginSuccessResponse {
  data: {
    token: string
    user: AuthUser
  }
}

export interface ApiErrorResponse {
  error: string
  message: string
  details?: Record<string, unknown> | unknown[]
}
