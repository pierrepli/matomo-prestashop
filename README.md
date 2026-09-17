# Vanilla Matomo Tracking

Module Matomo pour PrestaShop, sans cookie, avec tracking e-commerce
complet — en JavaScript 100% natif, sans aucune dépendance jQuery.

## Pourquoi ce module

La plupart des modules Matomo pour PrestaShop injectent leur script
directement via `$(document).ready(...)`. Sur les thèmes qui chargent
jQuery tardivement (par exemple Hummingbird v2), ce script s'exécute
avant que jQuery existe et plante avec `Uncaught ReferenceError: $ is
not defined` — silencieusement, sans casser la page, donc le bug passe
souvent inaperçu jusqu'à ce qu'on aille chercher pourquoi une fonctionnalité
liée au tracking ne remonte pas de données.

Ce module n'utilise aucune dépendance JS externe : uniquement du
JavaScript natif (`addEventListener`, `prestashop.on`), compatible avec
n'importe quel thème, quel que soit l'ordre de chargement de jQuery.

## Fonctionnalités

- Tag Matomo de base, asynchrone
- Suivi sans cookie (`disableCookies`), activable/désactivable en config
- Tracking e-commerce complet :
  - vue produit (`setEcommerceView`) sur les fiches produit
  - ajout panier (`addEcommerceItem` + `trackEcommerceCartUpdate`), via
    l'événement natif `prestashop.on('updateCart')`
  - commande validée (`trackEcommerceOrder`), avec garde-fou
    `sessionStorage` pour éviter le double comptage en cas de rechargement
    de la page de confirmation
- Les employés back-office connectés ne sont jamais tracés (évite le
  bruit des prévisualisations)
- Configuration entièrement en back-office (URL Matomo, Site ID,
  activation des cookies et de l'e-commerce)

## Prérequis

- PrestaShop 1.7.5 ou supérieur
- Une instance Matomo (auto-hébergée ou cloud), avec l'URL et le Site ID

## Installation

1. Téléchargez ou clonez ce dépôt, puis compressez le dossier
   `vanillamatomo/` en `vanillamatomo.zip` (le zip doit contenir le
   dossier `vanillamatomo/` à la racine, pas son contenu directement).
2. Back-office PrestaShop : **Modules > Gestionnaire de modules >
   Importer un module**, uploadez le zip.
3. Ouvrez la configuration du module et renseignez l'URL de votre
   instance Matomo et le Site ID.

## Limites connues

- Le suivi panier dépend de l'événement `prestashop.on('updateCart')`,
  standard sur les thèmes PrestaShop 1.6+ ; un thème très custom qui ne
  déclenche pas cet événement ne remontera pas le suivi panier (le
  pageview et le suivi de commande restent inchangés).
- Seule la catégorie par défaut du produit est envoyée sur
  `setEcommerceView` (pas les catégories secondaires).
- La détection de la fiche produit repose sur
  `$this->context->controller instanceof ProductController` ; un thème
  qui surcharge `ProductController` sous un autre nom de classe devra
  adapter cette vérification.

## Licence

MIT — voir [LICENSE](LICENSE).
