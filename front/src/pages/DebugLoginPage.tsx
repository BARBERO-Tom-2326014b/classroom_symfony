import React from 'react'
import { apiJson } from '../lib/api'

export default function DebugLoginPage() {
  const [email, setEmail] = React.useState('')
  const [password, setPassword] = React.useState('')
  const [result, setResult] = React.useState<any>(null)
  const [sessionInfo, setSessionInfo] = React.useState<any>(null)

  async function checkCredentials() {
    try {
      const res = await apiJson('/api/debug/check-credentials', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      })
      setResult(res)
    } catch (err) {
      setResult({ error: String(err) })
    }
  }

  async function checkSession() {
    try {
      const res = await apiJson('/api/debug/session')
      setSessionInfo(res)
    } catch (err) {
      setSessionInfo({ error: String(err) })
    }
  }

  React.useEffect(() => {
    checkSession()
  }, [])

  return (
    <div style={{ maxWidth: 600, margin: '40px auto', fontFamily: 'monospace' }}>
      <h1>Debug Login</h1>

      <div style={{ marginBottom: 20, padding: 12, background: '#f0f0f0', borderRadius: 4 }}>
        <h3>Session Info</h3>
        <pre>{JSON.stringify(sessionInfo, null, 2)}</pre>
        <button onClick={checkSession}>Rafraîchir session</button>
      </div>

      <div style={{ display: 'grid', gap: 12, marginBottom: 20 }}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />
        <button onClick={checkCredentials}>Vérifier identifiants</button>
      </div>

      {result && (
        <div style={{ padding: 12, background: result.success ? '#d4edda' : '#f8d7da', borderRadius: 4 }}>
          <h3>Résultat</h3>
          <pre>{JSON.stringify(result, null, 2)}</pre>
        </div>
      )}
    </div>
  )
}
