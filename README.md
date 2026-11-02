[Lire ce README en français](README.fr.md)
# Dipla

Dipla is a web platform for discovering local businesses. It lets individuals find products and services offered by local companies, using a geolocated search approach (distance, route) rather than a simple online directory.

Developed between 2022 and 2024, the site is fully responsive (mobile, tablet, desktop). The project was fully completed and put online, before being abandoned for lack of time to continue its maintenance and development. This repository is kept as a record of the work done.

> Since the site is no longer hosted, demo videos of the site and of the database are available at the end of the file

---

## Table of contents

- [Concept](#concept)
- [Features](#features)
- [Technical architecture](#technical-architecture)
- [Database](#database)
- [Libraries and external services](#libraries-and-external-services)
- [Project status](#project-status)
- [Demo](#demo)

---

## Concept

Dipla connects two types of users:

- **Individual**: searches for products/services around them, browses product pages, adds items to favorites, asks questions and leaves reviews.
- **Business (local shop)**: publishes its products/services, answers customers' questions and tracks its visibility statistics (views, favorites).

The goal was to give local businesses better visibility by relying on geolocation, rather than on a simple keyword search engine.

---

## Features
> The site is **responsive**: all pages (search, product page, accounts, business dashboard) adapt to mobile, tablet and desktop screens.
> 
### Authentication & accounts
- Dual login and sign-up paths: individual / business.
- Sign-up with geolocation (address autocomplete + interactive map).
- Address optional for an individual, mandatory for a business (to be locatable).
- Forgotten password (reset via token).
- Cascade account deletion (reviews, discussions, favorites, history deleted along with the account).

### Personal area
- View and edit profile (name, email, address).
- **Favorites**: list of saved products.
- **Reviews**: history of posted reviews, with multiple deletion.
- **Questions**: history of questions asked, with multiple deletion.
- **Browsing history**: the last 7 products viewed (automatic purge of the oldest), with the option to clear everything.

### Business area 
- Management of the company's information, with an option to apply an address change to all its products in case of relocation.
- **Statistics**: total number of views and favorites across all products.
- **Adding / editing products and services**: name, price, address, type (product or service), keywords, description.
- **Multiple image upload** with built-in cropping and rotation (main image + up to 3 secondary images, portrait/landscape formats).
- Live search within its own published products.
- Management of received questions with direct reply from the dashboard.

### Search 
- **Fuzzy full-text search** (typo-tolerant), indexed on product keywords and content.
- **Filters**: maximum price slider and maximum distance slider .
- **Geolocated search**: from the saved position, a typed address (with autocomplete) or a point chosen on an interactive map.
- Three search modes:
  - Classic keyword search (with optional geographic filter within a 200 km radius).
  - **"Nearby"**: products within a 20 km radius around the user.
  - **"Discover"**: random product discovery (within a 200 km radius if geolocated, otherwise global random, limited to 1000 results).
- Display toggle between **Products** and **Services**.

### Product page
- Image gallery (main image + up to 2 secondary images).
- Average rating displayed as stars (1 to 5).
- Business address, description, publication date.
- **Route**: calculation of travel time and distance to the product, with map display on demand.
- **Related products**: suggestions based on keywords shared with the viewed product.
- Automatic addition to the logged-in user's browsing history.
- **Reviews** and **Questions/Answers** tabs loaded dynamically (AJAX), directly on the product page.

### Social interactions
- Posting a review (subject, rating from 1 to 5 stars, content) — reserved for logged-in users.
- Posting questions, with a reply system (a reply is attached to the original question).
- Dynamic display without page reload.
- Invitation to log in if the user is not identified.

### Additional pages
- Contact / reporting page.
- Legal notice and privacy policy.
- Home page with image slider, highlighted categories/products, quick access to the "nearby" and "discover" searches.

---

## Technical architecture

The site was developed entirely in PHP, JavaScript and CSS, with no backend or frontend framework:

- **Backend**: procedural PHP (no framework), one page = one PHP file, with template-style includes (header, footer, reusable fragments for reviews, questions, articles...).
- **Database**: MySQL, accessed via PDO (prepared statements).
- **Frontend / interactivity**: jQuery for all the interactive JavaScript, no front-end framework.
- **CSS**: custom stylesheets .
- **Deployment**: hosted on Hostinger for 1 year, then abandoned.

### Code organization
```
Dipla/
├── config/                  → reusable PHP fragments (articles, reviews, question, logout, database...)
├── javascript/               → jQuery scripts (live search, favorites, AJAX interactions...)
├── nvx_mdp/                  → forgotten password flow (token)
├── main.php                  → home page
├── recherche.php             → search engine
├── page_artcl.php            → product page
├── interaction.php           → AJAX endpoints (reviews, questions/answers)
├── compte.php                → user personal area
├── entreprise.php            → business dashboard
├── identification.php / connexion.php   → user / business login
├── register.php / registerEntrps.php    → user / business sign-up
├── modification.php / modificationUser.php → profile modification endpoints
├── contact.php                → contact form
├── mentions.php / politique.php → legal notice / privacy policy
└── style.css                  → main stylesheet
```

---

## Database

MySQL database, structured around 7 main tables:

| Table | Role | Key fields |
|---|---|---|
| **utilisateurs** | Individual accounts | `id_user`, `nom`, `mail`, `mdp`, `addrs`, `latitude`, `longitude`, `date` |
| **entreprise** | Business accounts | `id_entre`, `nom_entre`, `mail_entre`, `mdp_entre`, `addrs_entre`, `lat_entre`, `lng_entre`, `date` |
| **produits** | Published products/services | `id_item`, `id_entre`, `titre`, `prix`, `contenu`, `keyWords`, `addrs`, `latitude`, `longitude`, `note`, `date`, `type` (product/service) |
| **avis** | Customer reviews | `id_user`, `id_item`, `objet`, `contenu`, `note` (1 to 5), `date` |
| **discussion** | Questions/answers (threaded) | `id_item`, `id_user`, `id_ans` (reference to the question if it is an answer), `contenu`, `date` |
| **favoris** | Products added to favorites | `id_user`, `id_item` |
| **historique** | Browsing history (FIFO, 7 max) | `id_user`, `id_item`, `date` |

---

## Libraries and external services

| Need | Solution used |
|---|---|
| Full-text + fuzzy search | [TNTSearch](https://github.com/teamtnt/tntsearch) (PHP) |
| Geographic radius search | TNTGeoSearch (geospatial extension of TNTSearch) |
| Map, address autocomplete, geocoding, route | [HERE Maps JS API](https://developer.here.com/) |
| Image cropping on upload | [Cropper.js](https://fengyuanchen.github.io/cropperjs/) |
| Icons | Line Awesome |
| Image slider (home page) | [Slick](https://kenwheeler.github.io/slick/) |
| Authentication | Custom system, passwords hashed with MD5 |

---

## Project status

The project was developed to completion and put online: all the features listed above were working in production.

The project was abandoned for lack of time to ensure its maintenance and development. It is no longer actively maintained.

A complete rebuild is being considered to modernize the tech stack (backend framework, PostgreSQL database, secure password hashing, revised responsive design, etc.), but this repository only reflects the original version as it was developed and deployed.

---

## Demo

- Site demo video: https://youtu.be/2aKsb7X2yh4
- Database demo video: https://youtu.be/ACGGJiSxdRc
- Business area demo video: https://youtu.be/FpZHXxhP_pY
- User area demo video: https://youtu.be/8S2gLFNo3tY
