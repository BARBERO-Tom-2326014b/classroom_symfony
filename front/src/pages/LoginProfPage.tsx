import React from 'react'
import { apiJson } from '../lib/api'

type CsrfResponse = { token: string }

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function LoginProfPage() {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [error, setError] = React.useState<string | null>(null)

  // ⚠️ En dev, on reste sur la même origine (localhost:5173) et on passe par le proxy Vite.
  // Cela évite les erreurs réseau / CORS.

  async function refreshMe(): Promise<MeResponse | null> {
    try {
      return await apiJson<MeResponse>('/api/me')
    } catch {
      return null
    }
  }

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setError(null)
    setLoading(true)

    try {
      const csrf = await apiJson<CsrfResponse>('/api/csrf-token')

      const body = new URLSearchParams()
      body.set('_username', email)
      body.set('_password', password)
      body.set('_csrf_token', csrf.token)

      await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'include',
        body,
        redirect: 'manual',
      })

      const user = await refreshMe()
      if (!user) {
        throw new Error('Identifiants incorrects (email ou mot de passe).')
      }

      if (!user.roles.includes('ROLE_PROFESSEUR')) {
        await fetch('/logout', { method: 'GET', credentials: 'include' })
        throw new Error('Accès refusé: compte non professeur.')
      }

      // Pour les professeurs: redirection demandée vers le backend /login
      window.location.assign('http://0.0.0.0:8000/login')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur inconnue')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="app-shell">
      <div className="card" style={{ maxWidth: 520, margin: '0 auto' }}>
        <div className="card-body">
          <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, alignItems: 'baseline' }}>
            <h1 style={{ marginTop: 0, marginBottom: 0 }}>Connexion professeur</h1>
            <a href="/" className="badge">← Accueil</a>
          </div>

          <form onSubmit={onSubmit} style={{ display: 'grid', gap: 12, marginTop: 16 }}>
            <label style={{ display: 'grid', gap: 6 }}>
              Email
              <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required autoComplete="email" />
            </label>

            <label style={{ display: 'grid', gap: 6 }}>
              Mot de passe
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
              />
            </label>

            {error && <div className="badge badge-danger">{error}</div>}

            <button type="submit" disabled={loading}>
              {loading ? 'Connexion…' : 'Se connecter'}
            </button>
          </form>

          <div style={{ marginTop: 14, display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 10 }}>
            <a href="http://localhost:5173/login/etudiant" className="muted">Je suis étudiant</a>
            <a href="http://0.0.0.0:8000/register" className="muted">Créer un compte (backend)</a>
          </div>
        </div>
      </div>
    </div>
  )
}
