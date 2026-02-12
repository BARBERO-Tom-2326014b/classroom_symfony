# Problème de connexion étudiant - Solution

## Diagnostic du problème

Le problème de connexion vient du fait que les cookies de session Symfony ne sont pas correctement partagés entre le frontend (localhost:5173) et le backend (0.0.0.0:8000) à cause du CORS et des limitations du serveur PHP built-in.

## Solution appliquée

### 1. Proxy Vite configuré
Le fichier `vite.config.ts` a été configuré pour proxyfier `/api`, `/login` et `/logout` vers le backend avec :
- `changeOrigin: false` pour que les cookies fonctionnent
- `cookieDomainRewrite: 'localhost'` pour réécrire le domaine des cookies

### 2. Code React modifié
- Utilisation de chemins **relatifs** (`/api/me`, `/login`) au lieu d'URLs absolues
- Retrait de `redirect: 'manual'` qui empêchait les cookies d'être définis
- Ajout d'un délai de 200ms après le login pour s'assurer que le cookie est bien défini

### 3. Configuration Symfony
- CORS configuré pour `/login` et `/logout`
- Configuration de session avec `cookie_samesite: lax`

## Comment tester

1. **Redémarrer le serveur Vite** (important pour que la config du proxy soit prise en compte):
   ```bash
   cd front
   npm run dev
   ```

2. **Vérifier que le backend tourne**:
   ```bash
   cd backend
   php -S 0.0.0.0:8000 -t public
   ```

3. **Tester avec la page de debug**:
   - Aller sur http://localhost:5173/debug-login
   - Entrer vos identifiants étudiant
   - Cliquer sur "Vérifier identifiants" pour voir si l'utilisateur existe
   - Regarder les infos de session

4. **Tester le login normal**:
   - Aller sur http://localhost:5173/login/etudiant
   - Se connecter avec un compte étudiant

## Vérifications à faire

### Vérifier qu'un compte étudiant existe dans la base de données:
```bash
cd backend
php bin/console doctrine:query:sql "SELECT id, email, roles FROM user WHERE roles LIKE '%ROLE_ETUDIANT%'"
```

### Si aucun compte étudiant n'existe, en créer un:
- Aller sur http://0.0.0.0:8000/register
- Ou utiliser la console Symfony pour créer un utilisateur

## Remarques importantes

- **Toujours utiliser `localhost:5173`** pour accéder au frontend (jamais `0.0.0.0:5173`)
- **Le proxy Vite** s'occupe de rediriger les requêtes vers le backend
- **Les cookies** sont maintenant correctement partagés grâce au proxy
- Si le problème persiste, vider le cache du navigateur et les cookies
