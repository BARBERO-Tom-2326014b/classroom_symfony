import React from 'react'

type Props = {
  title: string
}

export default function TopBar({ title }: Props) {
  const [loading, setLoading] = React.useState(false)

  async function onLogout() {
    setLoading(true)
    try {
      await fetch('/logout', { method: 'GET', credentials: 'include' })
    } finally {
      // Quoiqu'il arrive, on revient au login
      window.location.assign('/')
    }
  }

  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
      <div>
        <h1 style={{ margin: 0 }}>{title}</h1>
      </div>
      <button type="button" onClick={onLogout} disabled={loading}>
        {loading ? 'Déconnexion…' : 'Déconnexion'}
      </button>
    </div>
  )
}
