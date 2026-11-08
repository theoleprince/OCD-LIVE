# OCD LIVE

> **Votre produit mérite d'être vu.**
> Plateforme panafricaine de présentation commerciale en direct — OCD (Les Opportunités C'est Dehors) · Émissions diffusées sur le compte TikTok House of Challenge · Studio de Lomé, Togo.
> Lancement commercial : 1er décembre 2026.

Application Laravel monolithique : site public premium, parcours de réservation en 8 étapes, calendrier
des disponibilités, gestion des paiements en attente, tableau de bord d'administration, notifications.

---

## Sommaire

1. [Stack technique](#1-stack-technique)
2. [Fonctionnalités](#2-fonctionnalités)
3. [Installation locale (Windows + PowerShell)](#3-installation-locale-windows--powershell)
4. [Comptes et données de démonstration](#4-comptes-et-données-de-démonstration)
5. [Tests](#5-tests)
6. [Déploiement Railway](#6-déploiement-railway)
7. [Architecture](#7-architecture)
8. [Paiements : ce qui reste à brancher](#8-paiements--ce-qui-reste-à-brancher)
9. [Sécurité](#9-sécurité)
10. [Rapport de tests et limitations](#10-rapport-de-tests-et-limitations)

---

## 1. Stack technique

| Élément | Version |
|---|---|
| PHP | 8.4 (minimum 8.4 : Symfony 8) |
| Laravel | 13 |
| Livewire | 4 (Alpine.js inclus) |
| Tailwind CSS | 4 (via `@tailwindcss/vite`) |
| Vite | 8 |
| Base de données | MySQL 8 / MariaDB 10.4+ (SQLite en mémoire pour les tests) |

Langue de l'application : français. Fuseau horaire de référence : `Africa/Lome` (GMT, sans heure d'été).

## 2. Fonctionnalités

### Site public
- Landing page : hero, deux cartes **Présentiel – Lomé** / **À distance – Afrique et international**, concept,
  « Comment ça marche ? », six packages (lus en base), « Pourquoi OCD Live ? », FAQ, contact, pied de page.
- Pages *Conditions générales* et *Politique de confidentialité* (à faire valider juridiquement).
- SEO : `<title>`, meta description, Open Graph, URL canonique, `/sitemap.xml`, `/robots.txt` dynamique
  (indexation bloquée hors production), `noindex` sur la connexion et l'administration.

### Parcours de réservation (`/reserver`, composant Livewire `BookingWizard`)
1. **Mode** : présentiel ou distance.
2. **Package** : les six offres actives avec leurs prestations et modalités.
3. **Date** : calendrier mensuel (premier mois : décembre 2026) avec jours *disponibles*, *complets*, *indisponibles*.
4. **Horaire** : créneaux calculés selon la durée du package, les horaires d'ouverture, la marge de préparation
   entre deux émissions, les blocages et le délai minimum de prévenance. Pour un client à distance, l'heure
   locale du participant est affichée.
5. **Informations personnelles** : nom, prénom, pays, ville, téléphone, WhatsApp, e-mail ; pour la distance :
   fuseau horaire, type de connexion, besoin d'assistance.
6. **Informations commerciales** : business, secteur, description, nature de l'offre, téléphone et adresse
   commerciaux, réseaux sociaux, logo, photos, nombre de produits, produits et quantités ;
   questions spécifiques (préparation, conservation, présentation) pour l'alimentaire.
7. **Récapitulatif** + acceptation des CGV.
8. **Paiement** : la réservation est créée au statut `PENDING_PAYMENT` avec un paiement `PENDING`.
   Les instructions de règlement sont affichées. **Aucun paiement n'est simulé.**

> L'ordre a été ajusté (Package avant Date) : les créneaux dépendent de la durée du package, il faut donc la
> connaître avant d'afficher le calendrier. Les liens `/reserver?mode=remote` et `/reserver?package=starter-live`
> présélectionnent les choix.

**Anti double réservation** : `BookingService::create()` ouvre une transaction SQL, verrouille la journée
(`SELECT … FOR UPDATE` sur `availability_slots`), revérifie la disponibilité côté serveur puis insère.
Deux demandes simultanées sur la même date sont sérialisées ; la seconde reçoit « créneau indisponible »
et revient à l'étape Horaire.

### Administration (`/admin`)
- **Tableau de bord** : total, du jour, en attente, confirmées, revenus réellement encaissés (paiements `PAID`
  hors démo), clients, entreprises, répartition par package, prochaines émissions, assistances à distance.
- **Réservations** : tableau filtrable (référence, client, business, pays, mode, package, date, heure, montant,
  statut, paiement) + fiche détaillée : coordonnées, besoins à distance, business, produits, fichiers transmis,
  paiements, notes internes, lien de connexion, historique.
- **Workflow** : enregistrer un paiement reçu (avec case de certification) → `PENDING_REVIEW` → confirmer →
  `CONFIRMED` (e-mail au client) → `COMPLETED`. Annulation à tout moment (libère le créneau).
  La confirmation est **impossible** sans paiement encaissé.
- **Clients** : recherche, fiche avec entreprises, produits et historique ; export CSV (admin, journalisé).
- **Packages** : créer, modifier (prix, description, durée, prestations, modalités, tarif validé ou non),
  activer/désactiver, archiver (suppression logique), restaurer.
- **Calendrier** : vue mensuelle ; ouvrir/fermer une date, modifier ses horaires, bloquer un créneau
  (refusé s'il chevauche une réservation), consulter les réservations du jour.
- **Notifications** : nouvelles réservations, marquage lu/non lu.
- **Paramètres** : jours et horaires d'ouverture, marge, pas des créneaux, date d'ouverture des réservations,
  horizon, délai minimum, durée de blocage sans paiement, coordonnées publiques, e-mail de notification.
- **Journal** des opérations administratives (connexion, paiements, statuts, packages, calendrier, exports).

Rôles : `admin` (tout) et `assistant` (réservations, clients, calendrier, notifications ; pas de packages,
paramètres, journal, export ni remboursement).

### Notifications
- Nouvelle réservation → notification **base de données** (visible dans le dashboard) + **e-mail** à chaque
  membre actif de l'équipe (+ e-mail facultatif configuré dans les paramètres). Contenu : référence, client,
  pays, ville, business, package, montant, mode, date, heure, téléphone, WhatsApp, e-mail, coordonnées commerciales.
- Accusé de réception au client (demande enregistrée, non confirmée).
- E-mail de **confirmation** au client uniquement lorsque l'administration confirme.
- Une panne d'e-mail est journalisée et ne fait jamais perdre une réservation.

## 3. Installation locale (Windows + PowerShell)

Prérequis : PHP ≥ 8.4 avec les extensions `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip` ;
Composer 2 ; Node.js ≥ 20.19 ; MySQL/MariaDB.

> Sur ce poste, PHP 8.4 portable est installé dans `D:\dev\tools\php84` (le PHP 8.1 de XAMPP est trop ancien
> pour Laravel 13) et MariaDB est celui de XAMPP (`C:\xampp1\mysql`).

```powershell
# 0. Utiliser PHP 8.4 dans cette session PowerShell
$env:Path = "D:\dev\tools\php84;" + $env:Path
php -v

# 1. Démarrer MariaDB (XAMPP) si nécessaire, puis créer la base
Start-Process -FilePath "C:\xampp1\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp1\mysql\bin\my.ini","--standalone" -WindowStyle Hidden
& "C:\xampp1\mysql\bin\mysql.exe" -uroot -e "CREATE DATABASE IF NOT EXISTS ocd_live CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Dépendances
cd D:\dev\projets\OCD-LIVE
php C:\composer\composer.phar install
npm install

# 3. Configuration
Copy-Item .env.example .env      # si .env n'existe pas encore
php artisan key:generate

# 4. Base de données + packages + admin (+ démo si SEED_DEMO=true dans .env)
php artisan migrate
php artisan db:seed

# 5. Lancer (deux terminaux)
npm run dev
php artisan serve
# → http://localhost:8000          (site)
# → http://localhost:8000/connexion (administration)
```

Build de production des assets : `npm run build`.

Si `composer` échoue avec « Could not authenticate against github.com », le jeton GitHub enregistré dans
`%APPDATA%\Composer\auth.json` est expiré. Supprimez-le (`composer config -g --unset github-oauth.github.com`)
ou utilisez un dossier Composer vierge : `$env:COMPOSER_HOME = "D:\dev\tools\composer-home"`.

## 4. Comptes et données de démonstration

Aucun mot de passe n'est écrit dans le dépôt.

```powershell
# Crée un administrateur ; mot de passe = ADMIN_PASSWORD s'il est défini, sinon généré et affiché UNE fois
php artisan ocd:create-admin --email=admin@ocdlive.com

# Une assistante commerciale
php artisan ocd:create-admin --email=assistante@ocdlive.com --name="Assistante Lomé" --role=assistant

# Régénérer un mot de passe oublié
php artisan ocd:create-admin --email=admin@ocdlive.com --reset
```

Données de démonstration (`SEED_DEMO=true` puis `php artisan db:seed`, ou `php artisan db:seed --class=DemoSeeder`) :
4 clients et entreprises **fictifs** préfixés `[DÉMO]`, e-mails en `@exemple.invalid`, références `OCD-DEMO-xxxx`,
badge « DÉMO » dans l'administration, **aucun paiement encaissé**. Elles occupent de vrais créneaux
(1er–4 décembre 2026) : **supprimez-les avant le lancement** avec `php artisan ocd:purge-demo`.

Autres commandes :

| Commande | Rôle |
|---|---|
| `php artisan ocd:expire-unpaid [--dry-run]` | Annule les réservations non payées après le délai de blocage (planifiée toutes les heures) |
| `php artisan ocd:purge-demo` | Supprime toutes les données de démonstration |

## 5. Tests

```powershell
php artisan test
```

Les tests utilisent SQLite en mémoire (aucun impact sur la base MySQL). Voir le [rapport](#10-rapport-de-tests-et-limitations).

## 6. Déploiement Railway

Le dépôt contient `railway.json` (builder **Railpack**, migration au pré-déploiement, healthcheck `/up`).
Railpack détecte Laravel, installe les dépendances Composer et npm, exécute `npm run build`, et sert
`public/` (document root) sur le port `$PORT` fourni par Railway, derrière HTTPS.

### 6.1 Commandes PowerShell

```powershell
# Pré-requis : dépôt Git poussé sur GitHub
cd D:\dev\projets\OCD-LIVE
git init; git add .; git commit -m "OCD Live v1"
git branch -M main
git remote add origin https://github.com/<compte>/ocd-live.git
git push -u origin main

# CLI Railway
npm install -g @railway/cli
railway login
railway init                      # crée le projet « ocd-live »
railway add --database mysql      # ajoute le service MySQL
railway add --repo <compte>/ocd-live   # service web relié au dépôt GitHub (ou via le dashboard)
railway service                   # sélectionner le service web

# Clé d'application générée localement (ne jamais la versionner)
php artisan key:generate --show

# Variables du service web (remplacer les valeurs entre <>)
railway variables `
  --set "APP_NAME=OCD LIVE" `
  --set "APP_ENV=production" `
  --set "APP_DEBUG=false" `
  --set "APP_KEY=<base64:...>" `
  --set 'APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}' `
  --set "APP_LOCALE=fr" --set "APP_FALLBACK_LOCALE=fr" `
  --set "DB_CONNECTION=mysql" `
  --set 'DB_HOST=${{MySQL.MYSQLHOST}}' `
  --set 'DB_PORT=${{MySQL.MYSQLPORT}}' `
  --set 'DB_DATABASE=${{MySQL.MYSQLDATABASE}}' `
  --set 'DB_USERNAME=${{MySQL.MYSQLUSER}}' `
  --set 'DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}' `
  --set "LOG_CHANNEL=stderr" --set "LOG_LEVEL=info" `
  --set "SESSION_DRIVER=database" --set "SESSION_ENCRYPT=true" --set "SESSION_SECURE_COOKIE=true" `
  --set "CACHE_STORE=database" --set "QUEUE_CONNECTION=sync" `
  --set "FILESYSTEM_DISK=local" `
  --set "ADMIN_EMAIL=<admin@ocdlive.com>"

# Stockage persistant des fichiers envoyés (logos, photos)
railway volume add --mount-path /app/storage/app

# Domaine public HTTPS
railway domain

# Déployer
railway up            # ou simplement git push (déploiement automatique depuis GitHub)
railway logs

# Les migrations et les 6 packages sont appliqués automatiquement au pré-déploiement (railway.json).
# Après le premier déploiement : créer le compte administrateur (mot de passe affiché une fois)
railway ssh -- php artisan ocd:create-admin
```

Les guillemets simples autour de `${{...}}` empêchent PowerShell d'interpréter `$`.
Le nom `MySQL` dans les références doit correspondre au nom du service base de données dans Railway.

### 6.2 Points importants
- **Migrations** : `php artisan migrate --force` uniquement (pré-déploiement). Jamais de `migrate:fresh`,
  `migrate:refresh`, `db:wipe` ou `migrate:rollback` en production.
- **Uploads** : stockés sur le disque privé `storage/app/private/bookings/{id}/` avec des noms aléatoires,
  servis uniquement via une route authentifiée. Le volume Railway rend ce dossier persistant.
- **Logs** : `LOG_CHANNEL=stderr` → visibles dans `railway logs` et le dashboard.
- **Santé** : `/up` (utilisé par le healthcheck Railway).
- **HTTPS** : terminé par Railway ; l'application fait confiance au proxy et force le schéma `https`.
- **E-mails** : configurer un fournisseur SMTP (`MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`,
  `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`). Sans cela, les notifications restent visibles dans le dashboard.
- **Tâche planifiée** (facultatif) : créer un service « cron » sur le même dépôt avec la commande de démarrage
  `php artisan schedule:run` et le cron `0 * * * *` pour libérer les créneaux non payés.
- `APP_DEBUG=false` : les erreurs affichent une page générique, le détail part dans les logs.

## 7. Architecture

```
app/
  Enums/            BookingStatus, PaymentStatus, ParticipationMode, UserRole
  Models/           Customer, Business, BusinessProduct, Package, Booking, Payment,
                    BookingAttachment, AvailabilitySlot, SlotBlock, Setting, AdminActivityLog, User
  Services/
    AvailabilityService   calcul des jours / créneaux (horaires, marge, blocages, réservations)
    BookingService        création transactionnelle + verrou + fichiers + notifications
    BookingWorkflow       transitions de statut et paiements (actions administratives)
  Payments/         PaymentGateway (interface), ManualPaymentGateway, PaymentManager
  Livewire/         BookingWizard (parcours de réservation)
  Http/Controllers/ PublicController, Auth/LoginController, Admin/*
  Notifications/    NewBookingNotification, BookingReceivedNotification, BookingConfirmedNotification
  Console/Commands/ ocd:create-admin, ocd:expire-unpaid, ocd:purge-demo
config/ocd.php      pays/fuseaux, secteurs, paramètres par défaut, limites d'upload
config/payments.php pilotes de paiement
```

Tables : `users`, `customers`, `businesses`, `business_products`, `packages`, `bookings`, `payments`,
`booking_attachments`, `availability_slots`, `slot_blocks`, `notifications`, `settings`,
`admin_activity_logs` (+ tables Laravel : sessions, cache, jobs). Clés étrangères, index sur les colonnes
filtrées, suppression logique sur clients, entreprises, packages et réservations.

## 8. Paiements : ce qui reste à brancher

V1 : pilote `manual`. Le client règle hors ligne (Mobile Money, espèces) et l'équipe enregistre le paiement.
Pour brancher un agrégateur (CinetPay, FedaPay, PayDunya, Stripe…) :
1. créer une classe implémentant `App\Payments\PaymentGateway` (`initiate()` retourne l'URL de paiement) ;
2. la déclarer dans `config/payments.php` et renseigner les clés dans les variables d'environnement ;
3. ajouter une route webhook qui **vérifie la signature** du prestataire avant d'appeler
   `BookingWorkflow::recordPayment()` ;
4. afficher le bouton de paiement à l'étape 8 du parcours.

## 9. Sécurité
- Authentification par session, `regenerate()` à la connexion, 5 tentatives max / 5 min par e-mail + IP,
  throttle global sur la route de connexion, comptes désactivables.
- Rôles via le middleware `role:` ; routes admin protégées par `auth` + rôle.
- CSRF sur tous les formulaires (Livewire inclus), échappement Blade systématique (XSS),
  Eloquent / requêtes paramétrées (injections SQL).
- Validation serveur de chaque étape et de chaque action admin ; pot de miel et limitation (6 demandes / 10 min / IP)
  sur l'envoi de réservation.
- Uploads : images JPG/PNG/WebP uniquement, 4 Mo max, 6 photos max, disque privé, noms aléatoires,
  `X-Content-Type-Options: nosniff`.
- En-têtes : `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS en HTTPS, `noindex` + `no-store` sur l'admin.
- Export CSV protégé contre l'injection de formules, réservé aux admins et journalisé.
- `.env` exclu du dépôt ; aucun secret ni mot de passe dans le code.

## 10. Rapport de tests et limitations

### 10.1 Tests automatisés — `php artisan test` : **58 tests, 58 réussis** (7 novembre 2026)

| # | Exigence | Fichier | Couvert par |
|---|---|---|---|
| 1 | Création d'un client | `CustomerAndBusinessTest` | client créé, client existant réutilisé sans doublon |
| 2 | Création d'une entreprise | `CustomerAndBusinessTest` | entreprise, produits, quantités, champs alimentaires, réseaux |
| 3 | Affichage des packages | `PackageDisplayTest` | 6 packages, prix, tarif validé, désactivation masquée |
| 4 | Création d'une réservation | `BookingCreationTest` | parcours Livewire complet, référence `OCD-2026-NNNN`, logo stocké en privé |
| 5 | Vérification des créneaux | `AvailabilityTest` | durées 1 h 30 / 6 h, marge, jours fermés/ouverts, blocages, « complet », délai minimum |
| 6 | Prévention des doubles réservations | `DoubleBookingTest` | même créneau, chevauchement + marge, créneau libéré après annulation, créneau pris pendant la saisie |
| 7 | Paiements en attente | `PaymentWorkflowTest` | `PENDING` à la création, confirmation impossible sans paiement, échec, revenus, expiration |
| 8 | Accès administrateur | `AdminAccessTest` | toutes les pages admin, rôle assistante, connexion, limitation des tentatives |
| 9 | Modification des packages | `PackageManagementTest` | prix, durée, prestations, création, activation, archivage, restauration, validation |
| 10 | Notifications | `NotificationTest` | contenu de la notification admin, dashboard, e-mail de confirmation, panne d'e-mail |
| 11 | Affichage mobile | `PublicPagesTest` | viewport, menu mobile, boutons ; SEO, sitemap, robots, `/up` |
| 12 | Accès non autorisé | `AdminAccessTest` | 9 routes privées redirigées, actions POST refusées, fichiers privés, compte désactivé |

### 10.2 Vérifications manuelles effectuées sur MySQL (MariaDB 10.4)
- Migrations + seeders (packages, admin, démo) exécutés sur MySQL.
- Toutes les pages publiques et les 14 pages d'administration répondent 200 après connexion réelle.
- **Test de concurrence réel** : 5 processus PHP réservent simultanément le même créneau → 1 réservation
  acceptée, 4 refusées. Ce test a révélé et permis de corriger deux défauts invisibles sous SQLite
  (longueur de la référence temporaire ; instantané REPEATABLE READ de MySQL contournant le verrou).
- E-mail « NOUVELLE RÉSERVATION OCD LIVE » généré (pilote `log`).
- Build front `npm run build` OK (CSS ≈ 57 Ko, 9,5 Ko gzip).

Non vérifié : rendu visuel dans un vrai navigateur mobile (aucun navigateur automatisé disponible sur ce poste —
le test n° 11 vérifie le HTML responsive, pas le rendu), envoi SMTP réel, déploiement Railway effectif
(nécessite votre compte Railway).

### 10.3 Limitations et points à valider
- **Paiement en ligne non branché** : Mobile Money / carte nécessitent un contrat et des clés d'agrégateur
  (voir §8). Aujourd'hui l'équipe enregistre manuellement les paiements reçus.
- **Diffusion** : aucune intégration automatique avec Facebook/TikTok/YouTube ou un outil de visioconférence ;
  un champ « lien de connexion / diffusion » est saisi à la main dans la fiche réservation.
- **Tarifs** : seul Starter Live (15 000 FCFA / 1 h 30) est marqué « validé » ; les 5 autres sont des
  propositions modifiables dans l'administration. Durées de créneau retenues pour Visibility Plus,
  Influence Boost et Partner of the Month : 2 h (modifiable). Partner of the Month : la réservation porte
  sur la première session, les suivantes se planifient selon le contrat.
- Le cahier des charges écrit « Bovanne » et « Bouvane » : l'orthographe **Bouvane** a été retenue partout.
- **Coordonnées** (téléphone, WhatsApp, e-mail, adresse) : valeurs provisoires à remplacer dans
  *Administration → Paramètres*.
- **Images** : les uploads ne sont pas redimensionnés ni compressés (limite 4 Mo par image).
- **CGV / confidentialité** : textes de base à faire valider par un juriste.
- **Expiration des réservations non payées** : automatique seulement si un service cron Railway exécute
  `php artisan schedule:run` ; sinon lancer `php artisan ocd:expire-unpaid` manuellement.
- E-mails envoyés de façon synchrone (pas de worker de file d'attente) : simple à déployer ; une panne SMTP
  est journalisée sans bloquer la réservation.
- Gestion des comptes de l'équipe en ligne de commande (`ocd:create-admin`), pas encore d'écran dédié.
