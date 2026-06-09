# Déploiement Docker — Tiqé Helpdesk

Image autonome (Nginx + PHP-FPM + queue worker + scheduler) prête à être reverse-proxyée derrière le serveur de softara.tech.

## Architecture
- **app** : image multi-stage `php:8.2-fpm-alpine` avec Nginx + PHP-FPM + Supervisor (queue + scheduler tournent dedans).
- **db** : MySQL 8.0 dans le même réseau interne, données dans un volume nommé.
- **volumes** : `db_data` (DB), `app_storage` (uploads), `app_logs`.

## Premier déploiement

```bash
# Sur le serveur softara.tech
git clone <repo> tique && cd tique

# 1. Préparer l'env
cp .env.docker.example .env
nano .env   # APP_URL, APP_KEY (laisser vide la 1ère fois), DB_PASSWORD, DB_ROOT_PASSWORD, SMTP…

# 2. Générer la clé d'app (une seule fois)
docker compose run --rm app php artisan key:generate --show
# → coller la valeur dans .env (APP_KEY=base64:…)

# 3. Build + démarrage
docker compose up -d --build

# 4. Vérifier
docker compose logs -f app
docker compose ps
```

L'entrypoint exécute automatiquement :
- attente de MySQL prêt
- `php artisan migrate --force`
- `php artisan db:seed --force` si `RUN_SEEDERS=1` (annuaire + référentiels)
- `config/route/view/event:cache`

## Reverse proxy (sous-domaine tique.softara.tech)

L'app écoute sur `${APP_PORT:-8080}` côté hôte. Pointe ton reverse proxy (Nginx / Traefik / Caddy) du sous-domaine vers `http://127.0.0.1:8080`.

### Exemple Nginx hôte
```nginx
server {
    listen 80;
    server_name tique.softara.tech;
    return 301 https://$host$request_uri;
}
server {
    listen 443 ssl http2;
    server_name tique.softara.tech;

    ssl_certificate     /etc/letsencrypt/live/tique.softara.tech/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tique.softara.tech/privkey.pem;

    client_max_body_size 25M;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host              $host;
        proxy_set_header X-Real-IP         $remote_addr;
        proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 120;
    }
}
```

### Certificat
```bash
sudo certbot --nginx -d tique.softara.tech
```

## Mises à jour (déploiement continu)
```bash
git pull
docker compose build app
docker compose up -d app   # rolling : nginx/queue/scheduler redémarrent dans le même conteneur
```

L'entrypoint relance automatiquement les migrations. Met `RUN_SEEDERS=0` dans `.env` après le 1er boot pour éviter de re-seeder à chaque déploiement.

## Commandes utiles

```bash
# Logs
docker compose logs -f app
docker compose logs -f db

# Shell
docker compose exec app bash

# Artisan dans le conteneur
docker compose exec app php artisan tinker
docker compose exec app php artisan migrate:status
docker compose exec app php artisan db:seed --class=AnnuaireSeeder

# Backup DB
docker compose exec db sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" tique_helpdesk' > backup-$(date +%F).sql

# Restauration DB
docker compose exec -T db sh -c 'exec mysql -uroot -p"$MYSQL_ROOT_PASSWORD" tique_helpdesk' < backup.sql

# Vider tous les caches
docker compose exec app php artisan optimize:clear
```

## Sécurité

- ⚠️ **Change `DB_PASSWORD` et `DB_ROOT_PASSWORD`** avant le premier `up`.
- Ne committe jamais ton `.env` de prod (le `.dockerignore` l'exclut déjà de l'image).
- L'image n'expose pas MySQL à l'hôte (pas de `ports:` sur le service `db`) — seul l'app peut s'y connecter via le réseau interne `tique`.
- `APP_DEBUG=false` forcé dans le compose.
- HTTPS doit être terminé par le reverse proxy hôte.

## Performance

L'image active OPcache + JIT (`opcache.ini`). Pour une charge importante, ajoute un service Redis dans `docker-compose.yml` et passe `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` à `redis`.

## Désactivation des seeders après le 1er boot

Edite `.env` sur le serveur :
```
RUN_SEEDERS=0
```
Puis : `docker compose up -d app`.
