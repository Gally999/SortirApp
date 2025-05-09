# 🧭 SortirApp – Projet Symfony 7.2

Une application de gestion de sorties entre participants (type meetup), développée avec Symfony 7.2 et Doctrine ORM par **Cécile Daguin** et **Grégory Nacher**.

---

## 🛠️ Technologies

- **Symfony 7.2**
- **PHP 8.3+**
- **Doctrine ORM**
- **Twig** pour les templates
- **Tailwind CSS**
- **MySQL**

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-utilisateur/sortirapp.git
cd sortirapp
```

### 2. Installer les dépendances

- Dépendances Symfony

```bash
composer install
```

- Dépendances Node
  - Pour une compilation unique

```bash
npm run build
```

- Pour du développement - recompile les assets à chaque changement

```bash
npm run watch
```

### 3. Configurer l'environnement

Créer un fichier `.env.local` à partir de `.env` :

```bash
cp .env .env.local
```

Puis configurez la base de données :

- Pour MariaDB

```ini
DATABASE_URL="mysql://root:@127.0.0.1:3306/SortirApp?serverVersion=10.4.28-MariaDB&charset=utf8mb4"
```

- Pour MySQL

```ini
DATABASE_URL="mysql://root:rootroot@127.0.0.1:3306/SortirApp?serverVersion=8.0.32&charset=utf8mb4"
```

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. (Optionnel) Charger les données de test

```bash
php bin/console doctrine:fixtures:load

```

### 🧪 Lancer le serveur de développement

```bash
symfony server:start -d
```

ou

```bash
symfony serve -d
```

### ✅ Fonctionnalités

#### Utilisateur

- [x] Gestion de son profil (avec photo de profil)

- [x] Création d'utilisateur [ADMIN]

- [x] Gestion d'utilisateur [ADMIN]

#### Sorties

- [x] Création de sorties

- [x] Inscriptions / désinscriptions

- [x] Clôture automatique selon la date limite

- [x] Filtrage multi-critères

- [x] Gestion des états via Enum

#### Autres

- [ ] Envoi d'emails

- [ ] Cron/scheduler pour automatisations

### 🔗 Ressources utiles

- [Symfony Docs](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/)
- [Twig](https://twig.symfony.com/doc/3.x/index.html)
- [FakerPHP](https://fakerphp.org/)

### 📄 Licence

Projet réalisé dans le cadre d'une formation – librement réutilisable à des fins pédagogiques.
