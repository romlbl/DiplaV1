# Dipla

Dipla est une plateforme web de découverte de commerces de proximité. Elle permet à des particuliers de trouver des produits et services proposés par des entreprises locales, avec une logique de recherche géolocalisée (distance, itinéraire) plutôt qu'un simple annuaire en ligne.

Développé entre 2022 et 2024, le site est entièrement responsive (mobile, tablette, desktop). Le projet a été entièrement terminé et mis en ligne, avant d'être abandonné faute de temps pour continuer sa maintenance et son évolution. Ce dépôt est conservé comme trace du travail réalisé.

> Le site n'étant plus hébergé, des vidéos de démonstration du site ainsi que de la base de données sont disponibles à la fin du fichier

---

## Sommaire

- [Principe](#principe)
- [Fonctionnalités](#fonctionnalités)
- [Architecture technique](#architecture-technique)
- [Base de données](#base-de-données)
- [Bibliothèques et services externes](#bibliothèques-et-services-externes)
- [Statut du projet](#statut-du-projet)
- [Démo](#démo)

---

## Principe

Dipla met en relation deux types d'utilisateurs :

- **Particulier** : recherche des produits/services autour de lui, consulte les fiches produits, met des articles en favoris, pose des questions et laisse des avis.
- **Entreprise (commerce local)** : publie ses produits/services, répond aux questions des clients et suit ses statistiques de visibilité (vues, favoris).

L'objectif était de donner une meilleure visibilité aux commerces de proximité en s'appuyant sur la géolocalisation, plutôt que sur un simple moteur de recherche par mots-clés.

---

## Fonctionnalités
> Le site est **responsive** : toutes les pages (recherche, fiche produit, comptes, dashboard entreprise) s'adaptent aux écrans mobile, tablette et desktop.
> 
### Authentification & comptes
- Double parcours de connexion et d'inscription : particulier / entreprise.
- Inscription avec géolocalisation (autocomplete d'adresse + carte interactive).
- Adresse facultative pour un particulier, obligatoire pour une entreprise (pour être localisable).
- Mot de passe oublié (réinitialisation par token).
- Suppression de compte en cascade (avis, discussions, favoris, historique supprimés avec le compte).

### Espace personnel
- Consultation et édition du profil (nom, mail, adresse).
- **Favoris** : liste des produits enregistrés.
- **Avis** : historique des avis postés, avec suppression multiple.
- **Questions** : historique des questions posées, avec suppression multiple.
- **Historique de consultation** : les 7 derniers produits consultés (purge automatique du plus ancien), avec possibilité de tout effacer.

### Espace entreprise 
- Gestion des informations de l'entreprise, avec option pour appliquer un changement d'adresse à tous ses produits en cas de déménagement.
- **Statistiques** : nombre total de vues et de mises en favoris sur l'ensemble des produits.
- **Ajout / édition de produits et services** : nom, prix, adresse, type (produit ou service), mots-clés, description.
- **Upload d'images multiples** avec recadrage (crop) et rotation intégrés (image principale + jusqu'à 3 images secondaires, formats portrait/paysage).
- Recherche en direct dans ses propres produits publiés.
- Gestion des questions reçues avec réponse directe depuis le dashboard.

### Recherche 
- **Recherche plein texte floue** (tolérante aux fautes de frappe), indexée sur les mots-clés et le contenu des produits.
- **Filtres** : curseur de prix maximum et curseur de distance maximum .
- **Recherche géolocalisée** : depuis la position enregistrée, une adresse saisie (avec autocomplete) ou un point choisi sur une carte interactive.
- Trois modes de recherche :
  - Recherche classique par mot-clé (avec filtre géographique optionnel dans un rayon de 200 km).
  - **« À proximité »** : produits dans un rayon de 20 km autour de l'utilisateur.
  - **« Découvrir »** : découverte aléatoire de produits (dans un rayon de 200 km si géolocalisé, sinon aléatoire global, limité à 1000 résultats).
- Bascule d'affichage entre **Produits** et **Services**.

### Fiche produit
- Galerie d'images (image principale + jusqu'à 2 images secondaires).
- Note moyenne affichée en étoiles (1 à 5).
- Adresse du commerce, description, date de publication.
- **Itinéraire** : calcul du temps de trajet et de la distance jusqu'au produit, avec affichage de carte à la demande.
- **Produits liés** : suggestions basées sur les mots-clés partagés avec le produit consulté.
- Ajout automatique à l'historique de consultation de l'utilisateur connecté.
- Onglets **Avis** et **Questions/Réponses** chargés dynamiquement (AJAX), directement sur la fiche produit.

### Interactions sociales
- Publication d'un avis (objet, note de 1 à 5 étoiles, contenu) — réservé aux utilisateurs connectés.
- Publication de questions, avec système de réponses (une réponse est rattachée à la question d'origine).
- Affichage dynamique sans rechargement de page.
- Invitation à se connecter si l'utilisateur n'est pas identifié.

### Pages complémentaires
- Page de contact / signalement.
- Mentions légales et politique de confidentialité.
- Page d'accueil avec slider d'images, mise en avant de catégories/produits, accès rapide aux recherches « à proximité » et « découvrir ».

---

## Architecture technique

Le site a été développé entièrement en PHP, JavaScript et CSS, sans framework backend ni frontend :

- **Backend** : PHP procédural (pas de framework), une page = un fichier PHP, avec des includes façon template (en-tête, footer, fragments réutilisables pour les avis, questions, articles...).
- **Base de données** : MySQL, accès via PDO (requêtes préparées).
- **Frontend / interactivité** : jQuery pour l'ensemble du JavaScript interactif, pas de framework front.
- **CSS** : feuilles de style maison .
- **Déploiement** : hébergé sur Hostinger pendant 1 an puis abandonné.

### Organisation du code
```
Dipla/
├── config/                  → fragments PHP réutilisables (articles, avis, question, déconnexion, database...)
├── javascript/               → scripts jQuery (recherche live, favoris, interactions AJAX...)
├── nvx_mdp/                  → parcours mot de passe oublié (token)
├── main.php                  → page d'accueil
├── recherche.php             → moteur de recherche
├── page_artcl.php            → fiche produit
├── interaction.php           → endpoints AJAX (avis, questions/réponses)
├── compte.php                → espace personnel utilisateur
├── entreprise.php            → dashboard entreprise
├── identification.php / connexion.php   → connexion utilisateur / entreprise
├── register.php / registerEntrps.php    → inscription utilisateur / entreprise
├── modification.php / modificationUser.php → endpoints de modification de profil
├── contact.php                → formulaire de contact
├── mentions.php / politique.php → mentions légales / politique de confidentialité
└── style.css                  → feuille de style principale
```

---

## Base de données

Base MySQL, structurée autour de 7 tables principales :

| Table | Rôle | Champs clés |
|---|---|---|
| **utilisateurs** | Comptes particuliers | `id_user`, `nom`, `mail`, `mdp`, `addrs`, `latitude`, `longitude`, `date` |
| **entreprise** | Comptes commerces | `id_entre`, `nom_entre`, `mail_entre`, `mdp_entre`, `addrs_entre`, `lat_entre`, `lng_entre`, `date` |
| **produits** | Produits/services publiés | `id_item`, `id_entre`, `titre`, `prix`, `contenu`, `keyWords`, `addrs`, `latitude`, `longitude`, `note`, `date`, `type` (produit/service) |
| **avis** | Avis clients | `id_user`, `id_item`, `objet`, `contenu`, `note` (1 à 5), `date` |
| **discussion** | Questions/réponses (threadées) | `id_item`, `id_user`, `id_ans` (référence à la question si c'est une réponse), `contenu`, `date` |
| **favoris** | Produits mis en favoris | `id_user`, `id_item` |
| **historique** | Historique de consultation (FIFO, 7 max) | `id_user`, `id_item`, `date` |

---

## Bibliothèques et services externes

| Besoin | Solution utilisée |
|---|---|
| Recherche plein texte + floue | [TNTSearch](https://github.com/teamtnt/tntsearch) (PHP) |
| Recherche géographique par rayon | TNTGeoSearch (extension géospatiale de TNTSearch) |
| Carte, autocomplete d'adresse, géocodage, itinéraire | [HERE Maps JS API](https://developer.here.com/) |
| Recadrage d'image à l'upload | [Cropper.js](https://fengyuanchen.github.io/cropperjs/) |
| Icônes | Line Awesome |
| Slider d'images (page d'accueil) | [Slick](https://kenwheeler.github.io/slick/) |
| Authentification | Système maison, mots de passe hashés en MD5 |

---

## Statut du projet

Le projet a été développé jusqu'au bout et mis en ligne : toutes les fonctionnalités listées ci-dessus étaient fonctionnelles en production.

Le projet a été abandonné faute de temps pour en assurer la maintenance et l'évolution. Il n'est plus maintenu activement.

Une refonte complète est envisagée pour moderniser la stack technique (framework backend, base de données PostgreSQL, hashage sécurisé des mots de passe, responsive design revu, etc.), mais ce dépôt reflète uniquement la version originale telle qu'elle a été développée et déployée.

---

## Démo

- Vidéo de démonstration du site : https://youtu.be/2aKsb7X2yh4
- Vidéo de démonstration de la base de données : https://youtu.be/ACGGJiSxdRc
- Vidéo de démonstration de l'espace entreprise : https://youtu.be/FpZHXxhP_pY
- Vidéo de démonstration de l'espace utilisateur : https://youtu.be/8S2gLFNo3tY

