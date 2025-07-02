# KèsMwen

## Description

*KèsMwen* est une application web simple et intuitive permettant aux utilisateurs (étudiants, jeunes actifs, travailleurs indépendants) de suivre leurs revenus et leurs dépenses afin de mieux gérer leur budget personnel au quotidien.

## Fonctionnalités

* Création de compte utilisateur avec authentification.
* Ajout de revenus et de dépenses avec description, date et catégorie.
* Tableau de bord avec solde actuel, total des revenus et dépenses.
* Statistiques visuelles :

  * Graphique circulaire (répartition des dépenses par catégorie).
  * Courbe d'évolution des finances mois par mois.
* Mode sombre.
* Classement mensuel Top 10 des meilleurs économes.
* Système de badges pour les utilisateurs actifs.
* Support multilingue (Créole/Français).

## Objectifs du projet

* Offrir une solution locale, légère et accessible pour la gestion des finances personnelles.
* Aider les utilisateurs à suivre facilement leurs entrées et sorties d'argent.
* Encourager l'épargne et une meilleure discipline financière.

## Public cible

* Étudiants
* Jeunes actifs
* Travailleurs indépendants
* Toute personne souhaitant suivre ses dépenses au quotidien

## Stack technique

* **Frontend :** HTML, CSS, Bootstrap, JavaScript
* **Backend :** PHP
* **Base de données :** MySQL
* **Communication :** API REST simple

## Installation

1. **Cloner le dépôt :**

   ```bash
   git clone https://github.com/votre-utilisateur/kesmwen.git
   ```

2. **Configurer la base de données :**

   * Créer une base de données `kesmwen` dans MySQL.
   * Importer le fichier `database.sql` fourni dans le dossier `/database`.
   * Modifier le fichier `config.php` avec vos identifiants MySQL.

3. **Lancer le projet :**

   * Déposer le projet dans le dossier `htdocs` ou votre dossier de serveur local (XAMPP, Laragon, WAMP).
   * Accéder à `http://localhost/kesmwen` via votre navigateur.

## Dépendances

* PHP >= 7.4
* MySQL
* Bootstrap (CDN)
* Chart.js pour les graphiques

## Défis relevés

* Conception d'une interface mobile-friendly sans framework lourd.
* Mise en place d'APIs REST simples entre le frontend et le backend.
* Intégration de statistiques visuelles et de badges tout en gardant l'application légère.
* Expérience utilisateur agréable avec mode sombre.

## Contributions

Les contributions sont les bienvenues ! Vous pouvez ouvrir des issues pour signaler des bugs ou proposer des fonctionnalités, et soumettre des pull requests pour améliorer l'application.

## Licence

Ce projet est sous licence MIT.

---

Pour toute question ou besoin de collaboration, contactez-moi via \[votre email ou LinkedIn].
