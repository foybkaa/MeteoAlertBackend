# Système d'Alerte Météo par SMS

Application backend Symfony permettant d'importer des destinataires depuis un fichier CSV et d'envoyer des alertes météo par SMS à des particuliers basé sur le code INSEE.

## Caractéristiques principales

- Import de fichiers CSV contenant des destinataires (code INSEE et numéro de téléphone)
- Validation des données lors de l'import
- Endpoint REST pour déclencher l'envoi d'alertes météo par code INSEE
- Traitement asynchrone des SMS via Symfony Messenger
- Simulation d'envoi de SMS (logs)
- Gestion des migrations SQL sans ORM

## Installation

### Prérequis

- PHP 8.4 ou supérieur
- Symfony 6.4
- PostgreSQL
- Composer

### Étape 1 : Cloner le projet

```bash
git clone git@github.com:foybkaa/MeteoAlertBackend.git
cd MeteoAlertBackend
```

### Étape 2 : Installer les dépendances

```bash
composer install
```

### Étape 3 : Configurer l'environnement

Créez un fichier `.env.local` à la racine du projet et configurez vos variables d'environnement :

```
DATABASE_URL="pgsql://app_user:app_password@database:5432/meteo_sms"
API_KEY=votre_cle_api_secrete
```

### Étape 4 : Exécuter les migrations SQL

```bash
php bin/console sql-migrations:execute
```
### Alternative : Installation avec Docker

Le projet peut également être déployé avec Docker :

```bash
# Construire et démarrer les conteneurs
docker compose up -d

# Installer les dépendances
docker compose exec php composer install

# Exécuter les migrations
docker compose exec php bin/console sql-migrations:execute
```

Configuration Docker :
- Application web accessible sur http://localhost:8080
- Base de données PostgreSQL exposée sur le port 5432
- Configuration dans le fichier `docker-compose.yml`

Conteneurs :
- **php** : PHP 8.4 avec toutes les extensions requises
- **webserver** : Nginx pour servir l'application
- **database** : PostgreSQL 14

## Utilisation

### Importation de destinataires depuis un CSV

Pour importer des destinataires depuis un fichier CSV :

```bash
php bin/console import-csv-file /chemin/vers/fichier.csv
```
Avec Docker :
```bash
docker compose exec php bin/console import-csv-file /chemin/vers/fichier.csv
```
💡 Un fichier "list_destinataires.csv" est disponible dans le dossier data à la racine du projet

Format attendu du CSV :
```
insee,telephone
75001,0601020304
75002,0601020305
```

À la fin de l'import, un rapport sera affiché avec le nombre de lignes importées avec succès et le nombre d'erreurs.

### Utiliser l'endpoint d'alerte

L'endpoint `/alerter` permet d'envoyer des alertes météo à tous les destinataires associés à un code INSEE.

Exemple un client HTTP comme Postman :
- URL : `http://localhost:8000/alerter` - Docker `http://localhost:8080/alerter`
- Méthode : `POST`
- Paramètres : `insee=69123`
- Headers : `X-API-KEY: votre_cle_api_secrete`

### Démarrer le worker Messenger

Pour traiter les messages asynchrones (envoi de SMS) :

```bash
php bin/console messenger:consume
```
Avece docker:
```bash
docker compose exec php bin/console messenger:consume
```

### Vérifier les logs d'envoi des SMS

Les logs d'envoi des SMS simulés sont disponibles dans le fichier `var/log/dev.log`

## Structure du projet

- `src/Command/ImportCsvCommand.php` : Commande d'import CSV
- `src/Service/SmsService.php` : Service simulant l'envoi de SMS
- `src/Controller/AlertController.php` : Contrôleur pour l'endpoint d'alerte
- `src/Message/AlertMessage.php` : Message pour le bus Messenger
- `src/MessageHandler/AlertMessageHandler.php` : Handler pour le traitement des messages
- `migrations/` : Dossier contenant les fichiers SQL de migration

## Migrations SQL

Le projet utilise le bundle `Doelia/sql-migrations-bundle` pour gérer les migrations SQL sans ORM.

Les fichiers de migration sont stockés dans le dossier `migrations/` 

Pour ajouter une nouvelle migration, créez un fichier SQL dans le dossier `migrations/` avec un nom de format `YYYYMMDD_feature.sql`.

## Sécurité

L'endpoint `/alerter` est sécurisé par une clé d'API qui doit être fournie dans l'en-tête HTTP `X-API-KEY`

La clé d'API est définie dans le fichier `.env.local` via la variable `API_KEY`.
