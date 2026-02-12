export default function HomeChoicePage() {
  return (
    <div className="app-shell">
      <div className="card" style={{ maxWidth: 720, margin: '0 auto' }}>
        <div className="card-body">
          <h1 style={{ marginTop: 0, marginBottom: 8 }}>Bienvenue sur EduLearn</h1>
          <div className="muted" style={{ marginBottom: 18 }}>
            Choisis ton espace pour continuer.
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
            <a href="http://localhost:5173/login/etudiant" className="list-item" style={{ display: 'block' }}>
              <div style={{ fontWeight: 900, marginBottom: 6 }}>🎓 Connexion étudiant</div>
              <div className="muted" style={{ fontSize: 13 }}>Accéder aux QCM, documents et vidéos.</div>
            </a>

            <a href="http://0.0.0.0:8000/login" className="list-item" style={{ display: 'block' }}>
              <div style={{ fontWeight: 900, marginBottom: 6 }}>🧑‍🏫 Connexion professeur</div>
              <div className="muted" style={{ fontSize: 13 }}>Gérer les ressources et générer des QCM.</div>
            </a>

            <a href="http://0.0.0.0:8000/register" className="list-item" style={{ display: 'block', gridColumn: '1 / -1' }}>
              <div style={{ fontWeight: 900, marginBottom: 6 }}>✨ Créer un compte étudiant</div>
              <div className="muted" style={{ fontSize: 13 }}>Inscription rapide (compte étudiant uniquement).</div>
            </a>
          </div>

          <div className="muted" style={{ marginTop: 16, fontSize: 12 }}>
            Astuce: tu peux toujours revenir ici via la déconnexion.
          </div>
        </div>
      </div>
    </div>
  )
}
