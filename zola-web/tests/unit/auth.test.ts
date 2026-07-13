import { describe, expect, it } from 'vitest'
import { getApiErrorMessage } from '~/utils/api-error'

describe('getApiErrorMessage', () => {
  it('extracts the API message from a fetch error', () => {
    const error = {
      data: {
        error: 'UNAUTHORIZED',
        message: 'Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe.',
      },
    }

    expect(getApiErrorMessage(error)).toBe(
      'Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe.',
    )
  })

  it('returns the fallback when no message is available', () => {
    expect(getApiErrorMessage({}, 'Erreur réseau')).toBe('Erreur réseau')
  })
})
