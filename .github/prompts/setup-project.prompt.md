---
mode: agent
description: Set up a new Meralda-based project from scratch — clone, configure, and initialise the database.
---

# New Meralda Project Setup

Follow these steps in order to create a fully working local environment.  
Full details are in `docs/ai/project-setup-first-installation.md`.

## 1. Clone with submodules

```bash
git clone --recurse-submodules https://github.com/rodrigovecco/meralda.git <project-name>
cd <project-name>
```

If you already cloned without the flag:

```bash
git submodule update --init --recursive
```

## 2. Copy the application configuration template

```powershell
# Windows
Copy-Item -Path "example/demo/app" -Destination "src/app" -Recurse
```

```bash
# Linux / macOS
cp -r example/demo/app src/app
```

## 3. Configure the database connection

Edit `src/app/cfg/db.php` and set:

```php
$cfg_db = [
    'host' => 'localhost',
    'db'   => '<your_db_name>',
    'user' => '<your_db_user>',
    'pass' => '<your_db_password>',
    'port' => 3306,
];
```

**Never use the root database user for the application.**

## 4. Create the database and import the schema

```bash
mysql -u root -e "CREATE DATABASE <your_db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root <your_db_name> < docs/db/mwphplib.sql
```

## 5. Create a dedicated DB user

```sql
CREATE USER '<user>'@'localhost' IDENTIFIED BY '<password>';
GRANT ALL PRIVILEGES ON <your_db_name>.* TO '<user>'@'localhost';
FLUSH PRIVILEGES;
```

## 6. Configure the web server

- Set the document root to `src/public_html/`.
- Example nginx config: `docs/ngix/example.conf`.
- Test by navigating to `http://localhost/admin/`.

## 7. (Optional) Detach from the Meralda remote

To turn the clone into your own independent project:

```bash
git remote remove origin
git remote add origin https://github.com/<you>/<your-project>.git
git push -u origin main
```

> Refer to `docs/ai/project-customization-detaching-and-modules.md` for the full detach workflow.
