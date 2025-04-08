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

Choisissez **une** des deux méthodes suivantes "Docker" ou "En local"

---

### Méthode 1 : Installation avec Docker (Recommandé)

Utilise Docker pour créer les environnements nécessaires (PHP, Nginx, PostgreSQL).

**Prérequis :**
- Docker
- Docker Compose

**Étapes :**

1.  **Cloner le projet :**
    ```bash
    git clone git@github.com:foybkaa/MeteoAlertBackend.git
    cd MeteoAlertBackend
    ```

2.  **Configurer `.env.local` pour Docker :**
    Copiez le fichier d'exemple :
    ```bash
    cp .env .env.local
    ```
    Ouvrez `.env.local` et modifiez les variables.  `DATABASE_URL`  et `API_KEY`
    ```dotenv
    # Exemple .env.local pour Docker
    DATABASE_URL="pgsql://app_user:app_password@database:5432/meteo_sms?serverVersion=16&charset=utf8"
    API_KEY=votre_cle_api_secrete_ici
    ```

3.  **Démarrer les conteneurs Docker :**
    ```bash
    docker compose up -d --build
    ```

4.  **Installer les dépendances (via Docker) :**
    ```bash
    docker compose exec php composer install
    ```

5.  **Lancer les migrations SQL (via Docker) :**
    ```bash
    docker compose exec php bin/console sql-migrations:execute
    ```

6.  L'application est accessible sur [`http://localhost:8080`](http://localhost:8080).

---

### Méthode 2 : Installation Locale (sur votre machine)


**Prérequis :**
- PHP 8.4 ou supérieur
- Composer
- PostgreSQL
- Symfony CLI

**Étapes :**

1.  **Cloner le projet :**
    ```bash
    git clone git@github.com:foybkaa/MeteoAlertBackend.git
    cd MeteoAlertBackend
    ```

2.  **Installer les dépendances :**
    ```bash
    composer install
    ```

3.  **Configurer `.env.local` pour le local :**
    Copiez le fichier d'exemple :
    ```bash
    cp .env .env.local
    ```
    Ouvrez `.env.local` et **modifiez la `DATABASE_URL`** pour pointer vers votre base de données locale. **Définissez aussi votre `API_KEY`**.
    ```dotenv
    # Exemple .env.local pour installation locale
    DATABASE_URL="pgsql://VOTRE_UTILISATEUR_BDD:VOTRE_MOT_DE_PASSE@127.0.0.1:5432/meteo_sms?serverVersion=16&charset=utf8"
    API_KEY=votre_cle_api_secrete_ici
    ```
    **Important :** Assurez-vous que la base de données (`meteo_sms` dans l'exemple) et l'utilisateur existent sur votre PostgreSQL local.

4.  **Lancer les migrations SQL :**
    ```bash
    php bin/console sql-migrations:execute
    ```

5.  **Lancer le serveur Symfony :**
    ```bash
    symfony server:start
    ```
    L'application est accessible sur [`http://127.0.0.1:8000`](http://127.0.0.1:8000).

## Utilisation

### Importation de destinataires depuis un CSV

Pour importer des destinataires depuis un fichier CSV :

Avec Docker :
```bash
docker compose exec php bin/console import-csv-file /chemin/vers/fichier.csv
```

En locale :

```bash
php bin/console import-csv-file /chemin/vers/fichier.csv
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

L'endpoint `/alerter` permet d'envoyer des alertes météo à tous les destinataires associés à un code INSEE. Pour l'utiliser avec un client HTTP comme Postman :

1.  **URL :** `http://localhost:8080/alerter`

2.  **Méthode :** `POST`.

3.  **Headers :** Ajoutez les deux en-têtes suivants :
    *   `X-API-KEY:` `votre_cle_api_secrete`
    *   `Content-Type` : `application/json` 

4.  **Body :**
    *   Sélectionnez l'option "raw".
    *   Choisissez le format "JSON" dans la liste déroulante.
    *   Dans la zone de texte, mettez le JSON contenant le code INSEE, comme ceci :

    ```json
    {
        "insee": "69123" 
    }
    ```
    *(Remplacez `"69123"` par le code INSEE souhaité)*.

### Démarrer le worker Messenger

Pour traiter les messages asynchrones (envoi de SMS) :

Avec docker:
```bash
docker compose exec php bin/console messenger:consume
```
En local
```bash
php bin/console messenger:consume
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
