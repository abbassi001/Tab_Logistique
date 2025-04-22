# 📦 TAB LOGISTIQUE - Système de Tracking de Colis

Un système complet de suivi logistique développé avec Symfony 7.5.2  pour la gestion et le tracking de colis.

## 📋 Description

TAB LOGISTIQUE est une application web permettant aux entreprises de logistique de suivre le parcours des colis de bout en bout. L'application gère les informations des expéditeurs, des destinataires, des entrepôts, des transports et permet le suivi en temps réel du statut des colis.

## 🛠️ Technologies

- **Framework**: Symfony 6.x
- **Base de données**: PostgreSQL
- **Front-end**: Twig, Bootstrap, JavaScript
- **Dépendances**: Composer, Doctrine ORM

## 🏗️ Architecture

Le système est basé sur une architecture MVC (Modèle-Vue-Contrôleur) avec les entités suivantes:

- **Entités principales**: 
  - `Colis`: Élément central du système
  - `Warehouse`: Gestion des entrepôts
  - `Departement`: Organisation interne

- **Entités utilisateurs/documents**:
  - `Expediteur`: Informations sur l'envoyeur
  - `Destinataire`: Informations sur le récepteur
  - `Employe`: Personnel de l'entreprise
  - `Photo`: Images des colis
  - `DocumentSupport`: Documents administratifs

- **Entités de processus**:
  - `Transport`: Moyens d'acheminement
  - `Statut`: Suivi des étapes logistiques

- **Tables de jonction**:
  - `ColisTransport`: Relation entre colis et moyens de transport

## ✅ Fonctionnalités

- Enregistrement et gestion des colis
- Suivi du statut des colis en temps réel
- Gestion des expéditeurs et destinataires
- Planification et suivi des transports
- Upload de photos et documents
- Interface d'administration complète
- Gestion des employés et des accès

## 🚀 Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-username/tab-logistique.git
   cd tab-logistique
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configurer la base de données**
   ```bash
   # Créer un fichier .env.local avec vos paramètres
   DATABASE_URL="postgresql://tabuser:password@127.0.0.1:5432/tab_logistique?serverVersion=15&charset=utf8"
   ```

4. **Créer la base de données**
   ```bash
   php bin/console doctrine:database:create
   ```

5. **Exécuter les migrations**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Lancer le serveur de développement**
   ```bash
   symfony server:start
   ```

## 📊 Modèle de données

L'application est basée sur un modèle entité-association complexe qui permet de suivre l'ensemble du processus logistique:

- Relations 1:n entre expéditeurs et colis
- Relations 1:n entre entrepôts et colis
- Relations n:m entre colis et transports
- Relations 1:n entre départements et employés

## 📆 Roadmap

- ✅ Conception du modèle de données
- ✅ Implémentation des entités Symfony
- ✅ Génération des CRUD pour toutes les entités
- ⬜ Intégration de l'upload des fichiers pour photos et documents
- ⬜ Système d'authentification et de sécurité
- ⬜ Dashboard d'administration avancé
- ⬜ API RESTful pour intégration externe
- ⬜ Tests unitaires et fonctionnels
- ⬜ Déploiement en production

## 🔒 Sécurité

Le système implémente plusieurs niveaux d'accès:
- **Administrateurs**: Accès complet au système
- **Gestionnaires**: Gestion des colis et statuts
- **Opérateurs**: Mise à jour des statuts
- **Clients**: Consultation du suivi des colis

## 🤝 Contribution

Les contributions sont les bienvenues! Pour contribuer:

1. Fork le projet
2. Créez votre branche de fonctionnalité (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -m 'Ajout d'une nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence [MIT](LICENSE)

## 👨‍💻 Auteur

[Adam Abbas ABBAS] - [abbasadoumabbs02@gmail.com]

---

© 2025 TAB LOGISTIQUE. Tous droits réservés.