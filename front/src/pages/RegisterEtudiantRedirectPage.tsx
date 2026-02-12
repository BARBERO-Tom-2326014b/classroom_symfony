import React from 'react'

// Pour le moment, l'inscription est gérée côté Symfony/Twig.
// Cette page sert uniquement à proposer une navigation cohérente depuis le front.
export default function RegisterEtudiantRedirectPage() {
  React.useEffect(() => {
    window.location.assign('http://0.0.0.0:8000/register')
  }, [])

  return (
    <div className="app-shell">
      <div className="card" style={{ maxWidth: 520, margin: '0 auto' }}>
        <div className="card-body">
          <div style={{ fontWeight: 900, marginBottom: 8 }}>Redirection…</div>
          <div className="muted">Ouverture du formulaire d’inscription (backend).</div>
          <div style={{ marginTop: 12 }}>
            <a className="badge" href="http://0.0.0.0:8000/register">Continuer</a>
          </div>
        </div>
      </div>
    </div>
  )
}
