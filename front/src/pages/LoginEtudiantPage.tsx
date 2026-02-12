import React from 'react'
import { apiJson } from '../lib/api'

type CsrfResponse = { token: string }

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function LoginEtudiantPage() {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [error, setError] = React.useState<string | null>(null)

  const backendBase = 'http://0.0.0.0:8000'
  const frontBase = 'http://localhost:5173'

  async function refreshMe(): Promise<MeResponse | null> {
    try {
      // ⚠️ URL absolue backend pour éviter toute ambiguïté d'origine
      return await apiJson<MeResponse>(`${backendBase}/api/me`)
    } catch {
      return null
    }
  }

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setError(null)
    setLoading(true)

    try {
      // 1) CSRF token (backend)
      const csrf = await apiJson<CsrfResponse>(`${backendBase}/api/csrf-token`)

      // 2) POST /login (backend)
      const body = new URLSearchParams()
      body.set('_username', email)
      body.set('_password', password)
      body.set('_csrf_token', csrf.token)

      await fetch(`${backendBase}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        credentials: 'include',
        body,
        redirect: 'manual',
      })

      // 3) vérifier la session
      const user = await refreshMe()
      if (!user) throw new Error('Identifiants incorrects (email ou mot de passe).')

      if (!user.roles.includes('ROLE_ETUDIANT')) {
        await fetch(`${backendBase}/logout`, { method: 'GET', credentials: 'include' })
        throw new Error('Accès refusé: compte non étudiant.')
      }

      // 4) aller sur l'espace étudiant (front)
      window.location.assign(`${frontBase}/qcms`)
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
            <h1 style={{ marginTop: 0, marginBottom: 0 }}>Connexion étudiant</h1>
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
            <a href="http://0.0.0.0:8000/register" className="muted">Créer un compte étudiant</a>
            <a href="http://0.0.0.0:8000/login" className="muted">Je suis professeur</a>
          </div>
        </div>
      </div>
    </div>
  )
}
