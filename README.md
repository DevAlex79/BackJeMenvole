# Back « Je m'envole »

API REST du site e-commerce **Je m'envole** (catalogue, comptes, commandes,
formulaire de contact). Frontend Vue.js séparé.

- **Framework** : Laravel 11 (PHP 8.2+)
- **Authentification** : JWT (`tymon/jwt-auth`), guard `api` par défaut
- **Base de données** : MySQL (InnoDB obligatoire)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret          # renseigne JWT_SECRET

# Base : créer la base `je-m-envole` (phpMyAdmin) puis :
php artisan migrate:fresh --seed
```

Serveur de dev : `php artisan serve` (API sur `http://127.0.0.1:8000/api`).

> Le frontend attend l'API sur `http://127.0.0.1:8000` et tourne sur
> `http://127.0.0.1:5173` — voir `config/cors.php`.

## Rôles

Définis dans `app/Enums/RoleEnum.php` et stockés dans la table `roles` :

| id | Rôle           | Droits |
|----|----------------|--------|
| 1  | Client         | Parcourt le catalogue, gère ses commandes |
| 2  | Vendeur        | Gère ses propres produits et leur stock |
| 3  | Administrateur | Accès total |

Contrôle d'accès :

- `jwt.auth` — authentification (token requis)
- `role:x,y` — l'utilisateur doit avoir l'un des rôles (`App\Http\Middleware\EnsureRole`)
- Policies auto-découvertes : `ProductPolicy`, `UserPolicy` (propriété
  vérifiée objet par objet)

## Endpoints principaux

| Méthode | URI | Accès |
|---------|-----|-------|
| GET  | `/api/articles`, `/api/articles/{id}` | public |
| GET  | `/api/categories`, `/api/roles` | public |
| POST | `/api/register`, `/api/login` | public (throttle 10/min) |
| POST | `/api/messages` | public (throttle 5/min) |
| POST | `/api/logout`, `/api/refresh`, GET `/api/user-profile` | authentifié |
| GET/POST/PUT/DELETE | `/api/orders...` | authentifié (admin pour `/orders/all`, `/orders/archived`) |
| POST/PUT/DELETE | `/api/articles...` | rôle 2 ou 3 |
| POST/PUT/DELETE | `/api/roles...` | rôle 3 |
| GET | `/api/users` | rôle 3 |
| GET/PUT/DELETE | `/api/users/{id}` | le propriétaire ou un admin |

### Créer une commande

`POST /api/orders` — le client est celui du token, les prix sont
recalculés côté serveur :

```json
{
  "cart": [{ "id_product": 1, "quantity": 2 }],
  "shipment_type": "Colissimo",
  "shipment_price": 4.95
}
```

## Tests

```bash
php artisan test
```

Suite sur SQLite en mémoire (`phpunit.xml`). `Feature/` couvre
l'authentification, le catalogue, le stock et les commandes.

## Notes de maintenance

- Les migrations forment un jeu unique et rejouable
  (`migrate:fresh` fonctionne de zéro).
- Ne jamais lancer les seeders en production (comptes de démo).
- En production : `APP_DEBUG=false`, `JWT_SECRET` dédié, file d'attente
  autre que `sync` pour les e-mails.
