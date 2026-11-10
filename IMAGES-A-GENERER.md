# Photographies à générer — Refonte OCD Live

Ce document liste tous les emplacements photo préparés dans le site (composant `<x-photo src="..." alt="...">`, voir `resources/views/components/photo.blade.php`). Tant qu'un fichier n'existe pas au chemin indiqué, un placeholder sobre (dégradé sable + icône) s'affiche automatiquement à sa place — dès que le fichier est déposé au bon endroit, la photo apparaît sans aucune modification de code.

**13 photos** sont nécessaires au total : elles déclinent les **7 scènes du brief** sur les différents emplacements du site, en veillant à ce qu'**aucun fichier ne soit réutilisé à deux endroits** (chaque emplacement a sa propre prise de vue, même quand le thème se ressemble).

## Contraintes communes à respecter sur chaque image

À intégrer systématiquement dans le prompt de génération :

- Personnes africaines noires, visages naturels et expressifs, peau à la texture réaliste (grain, reflets, micro-détails).
- Anatomie humaine correcte ; mains et doigts parfaitement naturels (point souvent défaillant en génération IA — à vérifier en priorité).
- Éclairage photographique crédible (pas de rendu plat ni sursaturé), ombres portées naturelles et cohérentes avec la source de lumière.
- Décor et équipements physiquement cohérents (câbles, caméras, micros, éclairages de studio réalistes).
- Profondeur de champ réaliste (léger flou d'arrière-plan façon objectif plein format, pas un flou gaussien artificiel).
- Rendu photographique professionnel : **aucun effet plastique, aucun rendu CGI/3D, aucune illustration**.
- Aucun texte lisible généré dans l'image, aucun faux logo.
- Format de sortie : **WebP** en priorité (fallback JPG accepté), compressé sans perte de qualité visible.

---

## 1. Hero — bannière immersive

**Chemin :** `public/images/hero/presentateur-studio-hero.webp`
**Ratio :** portrait, proche de 4:5 (bannière large à droite du hero sur desktop, pleine largeur sur mobile)
**Scène du brief :** n°1 — présentateur dans un studio audiovisuel

> Prompt : Photographie professionnelle plein format d'un présentateur africain noir, la trentaine, souriant avec assurance, assis face à une caméra de diffusion professionnelle dans un studio audiovisuel moderne. Il porte un micro-cravate, éclairage studio à trois points (clé, remplissage, contre-jour) visible en arrière-plan légèrement flou, écran de régie et caméras sur pied hors-champ. Ambiance chaleureuse orangée cohérente avec une identité de marque africaine moderne. Cadrage large, légère plongée pour un rendu immersif de bannière.

## 2. Concept — photo d'illustration

**Chemin :** `public/images/concept/entrepreneuse-boutique.webp`
**Ratio :** 4:3
**Scène du brief :** n°2 — entrepreneuse présentant ses produits dans sa boutique

> Prompt : Photographie professionnelle plein format d'une entrepreneuse africaine noire dans sa boutique, présentant avec le sourire un de ses produits tenu naturellement entre ses mains face à l'objectif. Décor de commerce africain moderne (étagères de produits, éclairage naturel de boutique), profondeur de champ réaliste avec arrière-plan légèrement flou. Lumière du jour chaleureuse complétée d'un éclairage d'appoint doux.

## 3. Services — Live en présentiel (studio de Lomé)

**Chemin :** `public/images/services/live-presentiel.webp`
**Ratio :** 4:3
**Scène du brief :** n°3 — commerçant participant à une émission commerciale en direct

> Prompt : Photographie professionnelle plein format d'un commerçant africain noir en plein direct dans un studio de diffusion, en train de présenter un produit face à la caméra, entouré de l'équipement de régie (caméra professionnelle au premier plan légèrement floutée, éclairage de studio). Expression naturelle et engageante, geste de présentation avec les mains bien rendu anatomiquement.

## 4. Services — Live à distance (panafricain)

**Chemin :** `public/images/services/live-distance.webp`
**Ratio :** 4:3
**Scène du brief :** n°5 — entrepreneur présentant ses produits via un smartphone

> Prompt : Photographie professionnelle plein format d'un entrepreneur africain noir filmant ou présentant ses produits à travers son smartphone monté sur un petit trépied, dans son commerce ou à domicile. Lumière naturelle de pièce, décor africain contemporain cohérent (mobilier, produits visibles en arrière-plan flou), expression concentrée et naturelle.

## 5. Services — Production de contenus promotionnels

**Chemin :** `public/images/services/production-contenus.webp`
**Ratio :** 4:3
**Scène du brief :** n°4 — équipe africaine de production audiovisuelle

> Prompt : Photographie professionnelle plein format d'une équipe africaine de production audiovisuelle (2 à 3 personnes noires, hommes et femmes) travaillant ensemble avec des caméras professionnelles sur épaule et trépied, dans un studio ou sur un lieu de tournage. Câblage et éclairage de production visibles et cohérents, ambiance de travail dynamique et professionnelle.

## 6. Services — Accompagnement commercial & relais

**Chemin :** `public/images/services/accompagnement-commercial.webp`
**Ratio :** 4:3
**Scène du brief :** n°7 — scène de présentation commerciale réunissant plusieurs entrepreneurs

> Prompt : Photographie professionnelle plein format réunissant plusieurs entrepreneurs africains noirs (3 à 4 personnes, mixte hommes/femmes) lors d'une scène de présentation commerciale collective, échangeant et souriant autour de leurs produits. Décor de studio ou d'espace d'exposition moderne, lumière douce et homogène.

---

## Galerie (`#galerie`) — 7 clichés, un par scène du brief

Variantes distinctes des scènes ci-dessus (angle, moment ou cadrage différents) pour alimenter la grille de galerie sans dupliquer les fichiers déjà utilisés ailleurs sur le site.

| # | Chemin | Scène du brief | Prompt (reprendre les contraintes communes + :) |
|---|--------|-----------------|--------------------------------------------------|
| 1 | `public/images/galerie/01-presentateur-camera.webp` | n°1 | Gros plan sur un présentateur africain noir de profil, micro-cravate, regardant l'écran de retour caméra dans un studio, lumière de studio chaude. |
| 2 | `public/images/galerie/02-entrepreneuse-produits.webp` | n°2 | Entrepreneuse africaine noire disposant soigneusement ses produits sur un présentoir de sa boutique, mains au premier plan, lumière naturelle. |
| 3 | `public/images/galerie/03-commercant-live.webp` | n°3 | Plan large d'un commerçant africain noir en direct, caméraman visible en amorce au premier plan, ambiance studio animée. |
| 4 | `public/images/galerie/04-equipe-production.webp` | n°4 | Technicien africain noir ajustant un projecteur de studio pendant qu'une collègue cadre une caméra sur trépied, câblage au sol visible. |
| 5 | `public/images/galerie/05-entrepreneur-smartphone.webp` | n°5 | Entrepreneur africain noir filmant un produit en gros plan avec son smartphone, dans son commerce, lumière de fin de journée. |
| 6 | `public/images/galerie/06-studio-equipement.webp` | n°6 | Vue d'ensemble d'un studio professionnel de livestream vide ou en préparation : projecteurs, micros sur perche, caméras, régie, aucune personne nécessaire. |
| 7 | `public/images/galerie/07-multi-entrepreneurs.webp` | n°7 | Groupe de 4 à 5 entrepreneurs africains noirs posant naturellement ensemble devant le studio ou un décor de présentation, ambiance conviviale et professionnelle. |

---

## Logos des moyens de paiement (optionnel)

La section « Moyens de paiement » de la page d'accueil utilise le composant `<x-payment-logo src="..." label="..." icon="...">` (voir `resources/views/components/payment-logo.blade.php`) : tant qu'aucun fichier n'existe au chemin indiqué, un badge sobre (icône + nom) s'affiche à la place. **Ce ne sont pas des photos à générer par IA** : ce sont des marques déposées tierces (Flooz, T-Money, Visa, Mastercard) — à récupérer uniquement depuis les kits de marque officiels de chaque fournisseur, jamais générées ou approximées.

| Moyen | Chemin attendu | Source |
|---|---|---|
| Flooz (Moov Africa Togo) | `public/images/paiement/flooz.png` | Kit de marque officiel Flooz |
| T-Money (Togocom) | `public/images/paiement/tmoney.png` | Kit de marque officiel Togocom |
| Visa | `public/images/paiement/visa.svg` | Kit de marque officiel Visa |
| Mastercard | `public/images/paiement/mastercard.svg` | Kit de marque officiel Mastercard |
| Virement bancaire | `public/images/paiement/virement.svg` | Icône générique (optionnel, un badge texte suffit) |

Les paiements en espèces n'ont pas de logo associé (badge texte + icône uniquement). Cette liste reprend les 4 catégories déjà enregistrées dans `App\Models\Payment::methodLabels()` (Mobile Money, Carte bancaire, Virement, Espèces) — Mobile Money et Carte bancaire étant détaillés par opérateur réel pour plus de clarté, sans ajouter de nouvelle capacité de paiement en ligne (le règlement reste coordonné manuellement via WhatsApp, cf. `app/Payments/ManualPaymentGateway.php`).

## Rappel des règles à ne jamais enfreindre

- Pas d'images génériques de banque d'images, pas de réutilisation d'un même fichier à deux emplacements.
- Pas de visages déformés, pas de mains mal rendues (vérifier systématiquement avant intégration).
- Pas de texte illisible ni de faux logos générés dans l'image.
- Le vrai logo OCD Live reste géré séparément via le composant `<x-logo>` (recoloré en orange) — ne jamais tenter de le faire apparaître dans une photo générée.
