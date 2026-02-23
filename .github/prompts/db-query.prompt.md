---
mode: agent
description: Write a database query using Meralda framework patterns. For local development only — never connects to remote databases.
---

# Database Query (Local Dev Only)

⚠️ **Development use only.** Never run against a remote or production database.  
Connection details come exclusively from `src/app/cfg/db.php`. Never hardcode credentials.

## Step 1 — Read credentials

Open `src/app/cfg/db.php` and extract:

```
host, db (database name), user, pass, port
```

## Step 2 — Locate the MySQL/MariaDB executable

Common Windows paths:
- WAMP: `E:\wamp64\bin\mariadb\mariadb<version>\bin\mysql.exe`
- XAMPP: `C:\xampp\mysql\bin\mysql.exe`

If unsure, search:

```powershell
Get-ChildItem -Path "C:\","E:\","D:\" -Filter "mysql.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 5 FullName
```

## Step 3 — Run a query

```powershell
& "PATH_TO_MYSQL.EXE" -h <host> -P <port> -u <user> -p<pass> <db> -e "SELECT ..."
```

**Never** use `-p <pass>` (with a space) — use `-p<pass>` (no space).

## Framework query patterns (PHP)

Refer to `docs/ai/database-query-patterns.md` for in-code patterns. Common examples:

```php
// Fetch all items via manager
$items = $manager->get_items_arr();

// Fetch single item by ID
$item = $manager->get_item_by_id($id);

// Custom WHERE clause
$items = $manager->get_items_arr_where("column = ?", [$value]);
```

## Safety rules enforced by this skill

- ❌ Do NOT access a host other than `localhost` or `127.0.0.1`.
- ❌ Do NOT log, print, or commit credentials.
- ❌ Do NOT run `DROP`, `TRUNCATE`, or `DELETE` without explicit user confirmation.
- ✅ Always limit exploratory `SELECT` results with `LIMIT 20`.
