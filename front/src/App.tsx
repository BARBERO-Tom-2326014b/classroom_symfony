import './App.css'
import HomeChoicePage from './pages/HomeChoicePage'
import LoginEtudiantPage from './pages/LoginEtudiantPage'
import LoginProfPage from './pages/LoginProfPage'
import RegisterEtudiantRedirectPage from './pages/RegisterEtudiantRedirectPage'
import QcmsPage from './pages/QcmsPage'
import QcmTakePage from './pages/QcmTakePage'
import VideosPage from './pages/VideosPage'

function App() {
  const path = window.location.pathname

  if (path === '/') {
    return <HomeChoicePage />
  }

  if (path === '/login/etudiant') {
    return <LoginEtudiantPage />
  }

  // Optionnel: compat si quelqu'un tape /login sur le front
  if (path === '/login') {
    return <LoginEtudiantPage />
  }

  if (path === '/login/prof') {
    return <LoginProfPage />
  }

  if (path === '/register/etudiant') {
    return <RegisterEtudiantRedirectPage />
  }

  if (path === '/qcms') {
    return <QcmsPage />
  }

  if (path === '/videos') {
    return <VideosPage />
  }

  if (path.startsWith('/qcms/')) {
    return <QcmTakePage />
  }

  // fallback
  return <HomeChoicePage />
}

export default App
