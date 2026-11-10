# OCD LIVE MARKET — suivi d'implémentation

Document de reprise. Le plan détaillé validé par l'utilisateur est dans
`C:\Users\Theophane\.claude\plans\mutable-enchanting-engelbart.md` (lisible uniquement en
local sur ce poste Windows). Ce fichier résume l'état d'avancement réel dans le dépôt pour
reprendre le travail dans une nouvelle session si celle-ci s'arrête en cours de route.

## État au 2026-11-10 (mise à jour 3) — MODULE TERMINÉ

Tâches 1 à 6 terminées et vérifiées manuellement (migrations/seeders exécutées, pages
publiques testées via serveur local, soumission + détection de doublon testées via tinker,
routes admin.market.* enregistrées, commande `market:expire-lives` + planification testées).
Tâche 7 terminée : 94/94 tests verts (58 existants + 36 Market), `npm run build` OK, pages
existantes en 200, parcours manuel soumission → approbation → publication → expiration vérifié
sur MySQL local (données de test supprimées ensuite). Note : utiliser PHP 8.4
(`D:\dev\tools\php84`), le `php` du PATH est en 8.1.
Ancien statut : il restait la tâche 7 (suite de tests automatisés + vérification finale de
non-régression). `php artisan test` est resté à 58/58 à chaque étape intermédiaire.

Détails par tâche ci-dessous (conservés pour référence si besoin de ré-auditer une décision).

## État initial (avant mise à jour 2)

### ✅ Tâche 1 — Enums, modèles, migrations, seeders (TERMINÉE, testée)
- Enums : `app/Enums/{LiveSubmissionStatus,ModerationAction,MarketPermission}.php`,
  `UserRole.php` (ajout du rôle `Moderator` + méthode `marketPermissions()`).
- Modèles : `app/Models/{Country,City,Category,Seller,LiveSubmission,LiveSchedule,
  LiveValidation,LiveReport}.php`.
- Migrations : `database/migrations/2026_11_10_000001..000008_*` (countries → cities →
  categories → live_schedules → sellers → live_submissions → live_validations →
  live_reports). **Déjà exécutées** sur la base locale MySQL (`php artisan migrate --force`).
- Seeders : `CountrySeeder` (54 pays UA, drapeau emoji calculé depuis l'ISO), `CitySeeder`
  (villes de démarrage), `CategorySeeder` (16 catégories), `LiveScheduleSeeder` (Matin/
  Après-midi/Soir). Appelés depuis `DatabaseSeeder`. **Déjà exécutés**, idempotence vérifiée
  (ré-exécution sans doublon).
- `config/ocd.php` : ajout de `market_publication_minutes` (90) et `market_notification_email`
  dans `defaults`.
- `php artisan test` : 58/58 tests existants toujours verts à ce stade.

### 🚧 Tâche 2 — Formulaire public de soumission (EN COURS)
Fait :
- `app/Exceptions/DuplicateSubmissionException.php`
- `app/Services/SellerSubmissionService.php` (résolution vendeur par phone+pays, détection
  de doublon actif par compte TikTok, génération référence `MKT-AAAA-NNNN` + `tracking_token`,
  stockage images sur le disque `public`, notification des modérateurs).
- `app/Notifications/NewLiveSubmissionNotification.php` (database + mail, référence
  `route('admin.market.submissions.show', ...)` **qui n'existe pas encore** — sera créée en
  tâche 5 ; ne pas déclencher cette notif avant que la route admin existe, sinon
  `RouteNotFoundException`).
- `app/Livewire/LiveSubmissionForm.php` + vue
  `resources/views/livewire/live-submission-form.blade.php` (formulaire une page, sections
  Vendeur / Commerce / Live / Consentements, honeypot, rate limit 5/30min).
- `app/Http/Controllers/MarketController.php` créé avec **toutes** les méthodes prévues
  (index, country, seller, submitForm, track, report) — anticipé sur les tâches 3 et 4 pour
  que les routes se résolvent correctement dès maintenant (le composant Livewire référence
  `route('market.index')` et `route('market.track', ...)`).
- Routes ajoutées dans `routes/web.php` (groupe `market.` : index, submit, suivi/{token},
  lives/{submission}/signaler, vendeur/{seller}, {country:slug} — **dans cet ordre**, les
  routes littérales doivent précéder le slug dynamique pays).
- Vues créées : `resources/views/market/submit.blade.php`,
  `resources/views/market/partials/live-card.blade.php`.

Reste à faire pour clore la tâche 2+3+4 (vues restantes, MarketController déjà prêt côté
logique) :
- `resources/views/market/track.blade.php` (page de suivi — textes de statut exacts §12 du
  cahier des charges, ne jamais afficher `internal_comment`).
- `resources/views/market/index.blade.php` (bannière, recherche, pays, catégories, grille de
  `live-card`, filtres GET, pagination, état vide : « Aucun LIVE disponible pour ces
  critères. Revenez bientôt découvrir de nouveaux vendeurs. »).
- `resources/views/market/country.blade.php` (drapeau, présentation, villes, catégories,
  lives actifs, nombre de vendeurs).
- `resources/views/market/seller.blade.php` (fiche vendeur facultative).
- Formulaire de signalement (bouton + petit formulaire POST vers `market.report`) à ajouter
  dans `live-card.blade.php` et/ou `market/seller.blade.php`.
- Ajouter l'entrée de nav « Market » dans `resources/views/public/partials/header.blade.php`
  (lien direct `route('market.index')`, pas une ancre).
- Étendre `PublicController::sitemap()` avec `/market` + une entrée par pays actif.
- `@section('noindex')` sur `market/track.blade.php`.

### ⏳ Tâche 5 — Modération administrative (PAS COMMENCÉE)
À faire : `Gate::define('market', ...)` dans `AppServiceProvider::boot()`,
`app/Services/ModerationWorkflow.php`, routes `admin.market.*` (dashboard, submissions
index/show/review/approve/reject/cancel, lives + remove, reports, et sous-groupe admin-only
countries/categories/schedules/moderators avec `Admin\Market\ModeratorController`), vues
`resources/views/admin/market/*`, entrée nav dans `layouts/admin.blade.php`.
**Important** : une fois cette route créée, vérifier que `NewLiveSubmissionNotification`
(déjà écrite) pointe bien vers le bon nom de route.

### ⏳ Tâche 6 — Expiration automatique (PAS COMMENCÉE)
`app/Console/Commands/ExpireLiveSubmissions.php` (`market:expire-lives --dry-run`) +
`Schedule::command(...)->everyFiveMinutes()->withoutOverlapping()` dans `routes/console.php`.
Le garde-fou côté requêtes publiques (`LiveSubmission::scopePublished()` filtrant
`expires_at > now()`) est **déjà en place** dans le modèle.

### ⏳ Tâche 7 — Tests + non-régression (PAS COMMENCÉE)
`tests/Feature/Market/{SubmissionTest,PublicDirectoryTest,TrackingTest,ModerationTest,
ExpirationTest}.php`, puis `php artisan test` complet + `npm run build` + vérification
manuelle du parcours (soumission → modération → publication → expiration).

## Pour reprendre
1. Lire ce fichier + le plan détaillé (chemin ci-dessus) pour le contexte complet (décisions
   d'architecture validées : disque `public` pour les images, filtres Blade+GET sans API JSON,
   emoji drapeaux, rôle `Moderator`, permissions `MarketPermission`, formulaire une page).
2. Vérifier l'état réel du code (ce fichier peut être légèrement en retard sur le dépôt) avec
   `git status` / `git diff` avant de continuer.
3. Terminer les vues Market publiques listées ci-dessus, puis passer à la tâche 5 (admin),
   6 (expiration), 7 (tests).
4. Lancer `php artisan test` après chaque bloc pour vérifier l'absence de régression sur les
   58 tests existants.
