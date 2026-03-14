# Architecture Modulaire - Guide de Réutilisation

## Vue d'ensemble

Cette documentation décrit l'architecture modulaire mise en place pour permettre la réutilisation du code dans d'autres applications Laravel + Inertia.js + Vue 3.

## Structure des Couches

### 1. Configuration (Config Layer)
**Localisation**: `config/`

#### Menu System
- **Fichier**: `config/menu.php`
- **Description**: Configuration centralisée des menus avec support des permissions, rôles, dropdowns, et séparateurs
- **Exportable**: ✅ Oui
- **Dépendances**: Aucune

### 2. Services (Business Logic Layer)
**Localisation**: `app/Services/`

#### Services créés:
- **MenuService.php**: Gestion et filtrage des menus
  - Méthodes: getMenuForUser(), filterMenuItems(), isActive(), buildNavigationTree(), getBreadcrumbs()
  - Exportable: ✅ Oui
  
- **UserService.php**: Gestion des utilisateurs
  - Méthodes: getAllUsers(), createUser(), updateUser(), deleteUser(), assignPermissions()
  - Exportable: ✅ Oui (nécessite Spatie Permission)
  
- **RoleService.php**: Gestion des rôles
  - Méthodes: getAllRoles(), createRole(), updateRole(), deleteRole(), assignPermissions()
  - Exportable: ✅ Oui (nécessite Spatie Permission)
  
- **ServiceManagementService.php**: Gestion des services/départements
  - Méthodes: getAllServices(), createService(), updateService(), deleteService(), toggleActiveStatus()
  - Exportable: ✅ Avec adaptation (dépend du modèle Service)

### 3. DTOs (Data Transfer Objects)
**Localisation**: `app/DTOs/`

#### Structure:
```
app/DTOs/
├── BaseDTO.php (classe abstraite)
├── Users/
│   ├── UserDTO.php
│   ├── UserCreateDTO.php
│   └── UserUpdateDTO.php
├── Roles/
│   ├── RoleDTO.php
│   ├── RoleCreateDTO.php
│   └── RoleUpdateDTO.php
├── Services/
│   ├── ServiceDTO.php
│   ├── ServiceCreateDTO.php
│   └── ServiceUpdateDTO.php
└── Settings/
    └── AppSettingDTO.php
```

#### Avantages:
- ✅ Validation structurée
- ✅ Conversion automatique (toArray, fromArray, fromModel)
- ✅ Type-safe data transfer
- ✅ Réutilisable dans n'importe quelle application

#### Exportabilité: ✅ Très haute (indépendant du modèle métier)

### 4. Form Requests (Validation Layer)
**Localisation**: `app/Http/Requests/`

#### Structure:
```
app/Http/Requests/
├── Users/
│   ├── StoreUserRequest.php
│   └── UpdateUserRequest.php
├── Roles/
│   ├── StoreRoleRequest.php
│   └── UpdateRoleRequest.php
└── Services/
    ├── StoreServiceRequest.php
    └── UpdateServiceRequest.php
```

#### Caractéristiques:
- Validation centralisée avec messages en français
- Méthode authorize() avec vérification des permissions
- Méthode toDTO() pour conversion vers DTO
- Messages d'erreur personnalisés

#### Exportabilité: ✅ Haute (nécessite adaptation des règles métier)

### 5. Controllers (Orchestration Layer)
**Localisation**: `app/Http/Controllers/`

#### Controllers refactorisés:
- **UserController.php**: 
  - Injection de UserService
  - Utilise StoreUserRequest / UpdateUserRequest
  - Méthodes minimalistes (orchestration uniquement)
  
- **RoleController.php**:
  - Injection de RoleService
  - Utilise StoreRoleRequest / UpdateRoleRequest
  
- **ServiceController.php**:
  - Injection de ServiceManagementService
  - Utilise StoreServiceRequest / UpdateServiceRequest

#### Pattern:
```php
public function store(StoreUserRequest $request): RedirectResponse
{
    try {
        $this->userService->createUser($request->toDTO());
        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
    }
}
```

### 6. Components (Presentation Layer)
**Localisation**: `resources/js/Components/`

#### Component créé:
- **MenuRenderer.vue**: Composant Vue réutilisable pour afficher n'importe quel menu
  - Props: items (Array), type (String: desktop/mobile/user)
  - Support: dropdowns, séparateurs, liens avec patterns actifs
  - Exportable: ✅ Très haute

## Flux de Données

```
Request → Form Request (validation) → DTO (conversion) → Service (logic) → Model (DB)
                                                           ↓
Response ← Controller (orchestration) ← DTO (serialization) ←
```

## Guide d'Exportation

### Pour exporter le système de menu vers une autre app:
1. Copier `config/menu.php` et adapter les routes/permissions
2. Copier `app/Services/MenuService.php` (sans modification)
3. Copier `resources/js/Components/MenuRenderer.vue` (sans modification)
4. Injecter MenuService dans HandleInertiaRequests
5. Utiliser MenuRenderer dans les layouts

### Pour exporter un module complet (exemple: Users):
1. Copier `app/DTOs/BaseDTO.php`
2. Copier `app/DTOs/Users/` (adapter les propriétés si nécessaire)
3. Copier `app/Http/Requests/Users/` (adapter validation et messages)
4. Copier `app/Services/UserService.php` (adapter selon le modèle)
5. Copier `app/Http/Controllers/UserController.php` (adapter routes)

## Avantages de cette Architecture

### 1. Séparation des Préoccupations
- Configuration isolée de la logique
- Business logic dans les Services
- Validation dans les Form Requests
- Présentation dans les Components

### 2. Testabilité
- Services testables unitairement
- DTOs testables indépendamment
- Form Requests testables avec validation

### 3. Réutilisabilité
- Modules exportables vers d'autres projets
- Code découplé du modèle métier
- Adapté pour microservices

### 4. Maintenabilité
- Code centralisé (menu dans un seul fichier config)
- Logique métier isolée des controllers
- Validation centralisée

### 5. Scalabilité
- Ajout de nouveaux modules facile (copier le pattern)
- Extensions possibles sans modifier le core
- Architecture prête pour API (DTOs déjà en place)

## Prochaines Étapes

### Améliorations possibles:
1. ✅ Créer DTOs pour Tickets (optionnel)
2. ✅ Refactoriser TicketController avec TicketService
3. ✅ Ajouter Repository pattern pour isolation DB
4. ✅ Créer API REST avec les DTOs existants
5. ✅ Ajouter tests unitaires pour Services
6. ✅ Implémenter cache pour MenuService

### Modules à documenter:
- [ ] Permissions management
- [ ] Settings management
- [ ] Ticket system

## Dépendances

### Packages requis:
- Laravel 11+
- Inertia.js 2.0+
- Vue 3
- Spatie Laravel Permission 6+
- Tailwind CSS 3+

### Composants Vue réutilisés:
- NavLink.vue
- Dropdown.vue
- DropdownLink.vue

## Conventions de Code

### Naming:
- Services: `{Entity}Service.php` (UserService, RoleService)
- DTOs: `{Entity}DTO.php`, `{Entity}CreateDTO.php`, `{Entity}UpdateDTO.php`
- Requests: `Store{Entity}Request.php`, `Update{Entity}Request.php`
- Components: PascalCase avec `.vue` (MenuRenderer.vue)

### Méthodes:
- Services: `get{Entities}()`, `create{Entity}()`, `update{Entity}()`, `delete{Entity}()`
- DTOs: `toArray()`, `fromArray()`, `fromModel()`, `toDTO()`
- Requests: `rules()`, `messages()`, `attributes()`, `toDTO()`

## Changelog

### v1.0.0 - 2026-03-14
- ✅ Menu system modulaire créé
- ✅ Infrastructure DTOs complète
- ✅ Infrastructure Form Requests complète
- ✅ Infrastructure Services complète
- ✅ Controllers refactorisés (User, Role, Service)
- ✅ MenuRenderer component créé
- ✅ HandleInertiaRequests intégré avec MenuService
- ✅ AuthenticatedLayout refactorisé

### Fichiers créés:
- 1 fichier config (menu.php)
- 4 Services (MenuService, UserService, RoleService, ServiceManagementService)
- 1 BaseDTO + 10 DTOs spécifiques
- 6 Form Requests
- 1 Component Vue (MenuRenderer)
- 3 Controllers refactorisés

### Lignes de code:
- Supprimées: ~200 lignes (duplication dans layouts et controllers)
- Ajoutées: ~1400 lignes (infrastructure réutilisable)
- Net: +1200 lignes de code modulaire et testable

## Support

Pour toute question sur l'architecture ou l'exportation vers d'autres projets, référez-vous à ce document.

### Contact
- Documentation: Ce fichier
- Exemples: Voir UserController, RoleController, ServiceController
