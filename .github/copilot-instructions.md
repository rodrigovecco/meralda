# GitHub Copilot Instructions — Meralda Framework

## What this repo is

**Meralda** is a custom PHP web-application framework.  
Its source code lives in `src/` and is organized into three top-level areas:

| Directory | Purpose |
|-----------|---------|
| `src/mwap/` | Framework core + official modules (Git submodule) |
| `src/app/` | Project-specific configuration & overrides |
| `src/public_html/` | Web-accessible entry points (admin, get, service, …) |

Configuration files are always in `src/app/cfg/` (e.g. `db.php`, `sysmail.php`).  
The AI docs in `docs/ai/` are the authoritative reference for architecture decisions.

---

## Architecture patterns to follow

### 1. Manager + Item pattern

Every database-backed resource uses a **manager class** and an **item class**.  
Always extend the framework base classes:

```php
// Manager — src/mwap/modules/<vendor>/<feature>/man.php
class mwap_<vendor>_<feature>_man extends mwmod_mw_manager_man {
    function __construct($ap) {
        $this->init("<feature>", $ap, "<db_table>");
    }
    function create_item($tblitem) {
        return new mwap_<vendor>_<feature>_item($tblitem, $this);
    }
}

// Item — src/mwap/modules/<vendor>/<feature>/item.php
class mwap_<vendor>_<feature>_item extends mwmod_mw_manager_item {
    function get<Field>() {
        return $this->get_data_field("<column_name>");
    }
}
```

Key rules:
- `init($code, $ap, $tblname)` connects the manager to an existing table.
- Items only add getters for columns actually used at call sites.
- Expose managers via lazy loaders (`__get_priv_*()`) or `create_submanager_*()` — never instantiate twice.

### 2. AJAX / XML endpoints

Every AJAX command becomes a method named `execfrommain_getcmd_sxml_<cmdname>()` inside a subinterface class that extends `mwmod_mw_ui_sub_uiabs`.

URL structure:  
`/get/<main-ui-code>/sxml/ui/<subinterface-code>/<command>.xml`

- Subinterface codes use **hyphens** to represent nesting (`admin-users`).
- The framework routes the request automatically — no manual routing code needed.
- The method should build and return an XML response using the framework's XML helpers.

### 3. Module structure

New modules belong in `src/mwap/modules/<vendor>/<module>/`.  
Typical layout:

```
src/mwap/modules/<vendor>/<module>/
    man.php       ← manager class
    item.php      ← item class
    ui/
        sub/      ← subinterface files
```

Use the namespace convention: `mwap_<vendor>_<module>_*`.

### 4. Initialization chain

Do not modify the entry-point chain unless you understand the full flow:

```
public_html/admin/index.php
  → init.php
  → src/app/init.php
  → src/mwap/preinit.php   (loads cfg/*.php)
  → src/mwap/afterinit.php
```

### 5. Database credentials

Always read connection details from `src/app/cfg/db.php`.  
**Never hardcode credentials.**  
**Only connect to localhost/127.0.0.1** in development — refuse remote DB access.

---

## PHPDoc standards

Every class must have a class-level docblock.  
Use `@property-read` for private properties exposed via `__get_priv_*()`:

```php
/**
 * Short description.
 *
 * @property-read string        $code  Unique manager code.
 * @property-read SomeManager   $items_man  Lazy-loaded child manager.
 */
class mwap_vendor_feature_man extends mwmod_mw_manager_man {
```

All public/protected methods need `@param` and `@return` tags.

---

## Coding conventions

- File names are lowercase and use underscores.
- Class names mirror file locations: `mwap_<vendor>_<module>_<role>`.
- No framework files should be edited directly (`src/mwap/` is a submodule).  
  Override behavior in `src/app/` or project-level module classes instead.
- PHP `<?php` opening tag only — never the short `<?` form.
- Indent with 4 spaces (no tabs).

---

## Setup reminders (local dev only)

- Clone with `git clone --recurse-submodules`.
- Copy `example/demo/app/` → `src/app/` and configure `src/app/cfg/db.php`.
- Install the SQL schema from `docs/db/mwphplib.sql`.
- Web-server document root must point to `src/public_html/`.

---

## Key reference docs

| Topic | File |
|-------|------|
| First installation | `docs/ai/project-setup-first-installation.md` |
| System initialization chain | `docs/ai/system-initialization-guide.md` |
| Manager + Item pattern | `docs/ai/manager-item-pattern.md` |
| AJAX/XML endpoints | `docs/ai/ajax-xml-endpoints-pattern.md` |
| Database access (dev only) | `docs/ai/database-access-guide.md` |
| PHPDoc style | `docs/ai/phpdoc-documentation-guide.md` |
| Module customization | `docs/ai/project-customization-detaching-and-modules.md` |
| DB query patterns | `docs/ai/database-query-patterns.md` |
| UI base classes | `docs/ai/user-interfaces/base-ui-classes.md` |
| DataGrid UI | `docs/ai/user-interfaces/datagrid.md` |
