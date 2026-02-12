import React from 'react'
import { apiJson } from '../lib/api'

type CsrfResponse = { token: string }

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function LoginPage() {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [loading, setLoading] = React.useState(false)
  const [error, setError] = React.useState<string | null>(null)
  const [me, setMe] = React.useState<MeResponse | null>(null)

  async function refreshMe(): Promise<MeResponse | null> {
    try {
      const user = await apiJson<MeResponse>('/api/me')
      setMe(user)
      return user
    } catch {
      setMe(null)
      return null
    }
  }

  React.useEffect(() => {
    refreshMe()
  }, [])

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setError(null)
    setLoading(true)

    try {
      // 1) récupérer CSRF token pour form_login
      const csrf = await apiJson<CsrfResponse>('/api/csrf-token')

      // 2) POST /login (form_login) en x-www-form-urlencoded
      const body = new URLSearchParams()
      body.set('_username', email)
      body.set('_password', password)
      body.set('_csrf_token', csrf.token)

      // POST vers /login avec credentials pour que le cookie de session soit envoyé
      const loginRes = await fetch('/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        credentials: 'include',
        body,
      })

      // Symfony redirige toujours après le login (succès ou échec)
      // On vérifie la session avec /api/me
      // Important: attendre un peu pour que le cookie soit bien défini
      await new Promise(resolve => setTimeout(resolve, 100))

      const user = await refreshMe()
      if (!user) {
        throw new Error('Identifiants incorrects (email ou mot de passe).')
      }

      if (!user.roles.includes('ROLE_ETUDIANT')) {
        await fetch('/logout', { method: 'GET', credentials: 'include' })
        throw new Error('Accès refusé: compte non étudiant.')
      }

      setPassword('')

      // Redirection après login
      window.location.assign('/qcms')
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erreur inconnue')
      setMe(null)
    } finally {
      setLoading(false)
    }
  }

  async function onLogout() {
    setError(null)
    setLoading(true)
    try {
      await fetch('/logout', { method: 'GET', credentials: 'include' })
      await refreshMe()
    } finally {
      setLoading(false)
    }
  }

  return (
    <div style={{ maxWidth: 420, margin: '40px auto', fontFamily: 'system-ui, sans-serif' }}>
      <h1>Connexion</h1>

      {me ? (
        <div style={{ background: '#f6f6f6', padding: 12, borderRadius: 8, marginBottom: 16 }}>
          <div>
            <strong>Connecté :</strong> {me.email}
          </div>
          <div>
            <strong>Rôles :</strong> {me.roles.join(', ')}
          </div>
          <button onClick={onLogout} disabled={loading} style={{ marginTop: 12 }}>
            Se déconnecter
          </button>
        </div>
      ) : (
        <div style={{ color: '#666', marginBottom: 16 }}>Non connecté</div>
      )}

      <form onSubmit={onSubmit} style={{ display: 'grid', gap: 12 }}>
        <label style={{ display: 'grid', gap: 6 }}>
          Email
          <input
            type="email"
            value={email}
            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)}
            required
            autoComplete="email"
          />
        </label>

        <label style={{ display: 'grid', gap: 6 }}>
          Mot de passe
          <input
            type="password"
            value={password}
            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)}
            required
            autoComplete="current-password"
          />
        </label>

        {error && <div style={{ color: 'crimson' }}>{error}</div>}

        <button type="submit" disabled={loading}>
          {loading ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>

      <p style={{ marginTop: 16, color: '#666', fontSize: 14 }}>
        En dev: Vite proxy /login et /api vers le backend sur 0.0.0.0:8000.
      </p>
    </div>
  )
}
