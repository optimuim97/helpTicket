# Système de Gestion des Tickets (Helpdesk Tiqé)

Système complet de gestion de tickets helpdesk développé avec Laravel 12, Inertia.js et Vue 3.

## 🚀 Fonctionnalités

### Gestion des Tickets
- ✅ Création de tickets avec numérotation automatique (TCK-YYYY-NNNNN)
- ✅ Détection automatique des doublons (basée sur similarité de texte)
- ✅ Filtrage et recherche avancés
- ✅ Système de priorités (Urgent, Haut, Moyen, Bas)
- ✅ Statuts multiples (Nouveau, En cours, En attente, Résolu, Fermé)
- ✅ Types de tickets (Incident, Demande de service, Assistance)
- ✅ Canaux multiples (Téléphone, Email, Application, Autre)

### Gestion des Utilisateurs
- ✅ 3 rôles : Agent Helpdesk, Technicien, Superviseur
- ✅ Authentification sécurisée (Laravel Breeze)
- ✅ Gestion des utilisateurs (Superviseur uniquement)
- ✅ Autorisations basées sur les rôles (Spatie Laravel Permission)

### Collaboration
- ✅ Notes internes et publiques
- ✅ Pièces jointes (jusqu'à 10MB)
- ✅ Assignation de tickets
- ✅ Historique complet des modifications

### Notifications
- ✅ Notification à la création d'un ticket
- ✅ Notification lors d'une assignation
- ✅ Notification de changement de statut
- ✅ Notification de résolution
- ✅ Emails + notifications en base de données

### Rapports
- ✅ Performance des agents (tickets assignés, résolus, temps moyen)
- ✅ Statistiques globales (par statut, priorité, type, canal)
- ✅ Filtres par période (7 jours, 30 jours, 1 an)

## 🛠️ Stack Technique

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 + Inertia.js v2
- **Styling:** Tailwind CSS 4.0
- **Base de données:** MySQL
- **Packages clés:**
  - Spatie Laravel Permission v6.24.1 (RBAC)
  - Spatie Query Builder v6.4.4 (filtres)
  - Laravel Breeze v2.4.1 (authentification)
  - Intervention Image Laravel v1.5.7 (images)

## 📋 Prérequis

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL 8.0+

## 🔧 Installation

### 1. Cloner le projet
```bash
git clone <repository-url>
cd tiqé
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurer la base de données
Modifier `.env`:
```env
DB_CONNECTION=mysql
DB_DATABASE=tique_helpdesk
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Créer la base de données:
```sql
CREATE DATABASE tique_helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Migrations et seeds
```bash
php artisan migrate
php artisan db:seed
```

### 6. Créer le lien symbolique pour le stockage
```bash
php artisan storage:link
```

### 7. Compiler les assets
```bash
npm run dev
```

## 👤 Compte par défaut

Après le seeding, vous pouvez vous connecter avec:
- **Email:** admin@tique.helpdesk
- **Mot de passe:** password
- **Rôle:** Superviseur

## 📂 Structure du Projet

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── TicketController.php
│   ├── TicketNoteController.php
│   ├── TicketAttachmentController.php
│   ├── ReportController.php
│   └── UserController.php
├── Models/
│   ├── Ticket.php
│   ├── TicketType.php
│   ├── TicketChannel.php
│   ├── TicketPriority.php
│   ├── TicketStatus.php
│   ├── TicketNote.php
│   ├── TicketAttachment.php
│   ├── TicketHistory.php
│   └── TicketAssignment.php
├── Notifications/
│   ├── TicketCreatedNotification.php
│   ├── TicketAssignedNotification.php
│   ├── TicketStatusChangedNotification.php
│   └── TicketResolvedNotification.php
├── Observers/
│   └── TicketObserver.php
├── Policies/
│   └── TicketPolicy.php
└── Services/
    ├── TicketNumberGenerator.php
    └── DuplicateDetectionService.php

resources/js/Pages/
├── Dashboard.vue
├── Tickets/
│   ├── Index.vue
│   ├── Create.vue
│   ├── Show.vue
│   └── Edit.vue
├── Reports/
│   ├── AgentPerformance.vue
│   └── GlobalStatistics.vue
└── Users/
    ├── Index.vue
    ├── Create.vue
    └── Edit.vue
```

## 🔐 Autorisations

### Agent Helpdesk
- Créer et visualiser tous les tickets
- Modifier leurs propres tickets
- Ajouter des notes et pièces jointes

### Technicien
- Visualiser uniquement les tickets qui leur sont assignés
- Modifier les tickets assignés
- Fermer les tickets assignés
- Ajouter des notes et pièces jointes

### Superviseur
- Accès complet à tous les tickets
- Assigner les tickets aux techniciens
- Gérer les utilisateurs
- Accéder aux rapports
- Supprimer les tickets

## 📊 Fonctionnalités Avancées

### Détection de Doublons
Utilise un algorithme de similarité de texte (Levenshtein + similar_text) avec un seuil de 70% sur les 7 derniers jours.

### Historique Automatique
Chaque modification de ticket est automatiquement enregistrée via TicketObserver pour un audit complet.

### Numérotation Intelligente
Format TCK-YYYY-NNNNN avec compteur annuel auto-incrémenté et gestion des conditions de course.

## 🚀 Production

### Compiler les assets
```bash
npm run build
```

### Optimiser Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Configuration queue
Pour les notifications asynchrones:
```bash
php artisan queue:work
```

## 📧 Configuration Email

Modifier `.env` pour configurer l'envoi d'emails:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tique.helpdesk
MAIL_FROM_NAME="Helpdesk Tiqé"
```

## 🧪 Tests

```bash
php artisan test
```

## 📝 Licence

Ce projet est sous licence MIT.

## 👥 Développement

Développé par l'équipe Tiqé - Mars 2026

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
