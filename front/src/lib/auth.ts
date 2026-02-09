import { apiJson } from './api'

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

// Par défaut on veut ROLE_ETUDIANT. Si ton projet ne l'utilise pas encore,
// mets `fallbackRoles` pour autoriser un autre rôle.
export async function requireRole(requiredRole: string, fallbackRoles: string[] = []): Promise<MeResponse> {
  const me = await apiJson<MeResponse>('/api/me')

  const roles = me.roles ?? []
  const ok = roles.includes(requiredRole) || fallbackRoles.some((r) => roles.includes(r))

  if (!ok) {
    // pas habilité: on force logout pour nettoyer la session dans le navigateur
    try {
      await fetch('/logout', { method: 'GET', credentials: 'include' })
    } catch {
      // ignore
    }

    // Aide debug (visible côté console)
    console.warn('[auth] Accès refusé. Roles:', roles)

    throw new Error('Accès refusé')
  }

  return me
}
