---
mode: agent
description: Scaffold a new Manager + Item module for an existing database table in the Meralda framework.
---

# New Meralda Module

Scaffold a complete **Manager + Item** module following the Meralda framework conventions.

## Required information

Before generating files, collect the following (ask the user if any is missing):

| Variable | Description | Example |
|----------|-------------|---------|
| `<vendor>` | Vendor/project namespace prefix | `ventis` |
| `<module>` | Module code (singular noun) | `sales` |
| `<db_table>` | Existing database table name | `venta` |
| `<fields>` | Columns to expose as getters | `venta_code`, `venta_date` |

## Files to create

### `src/mwap/modules/<vendor>/<module>/man.php`

```php
<?php
/**
 * Manager for the <module> module.
 *
 * Handles data access for the `<db_table>` table.
 *
 * @property-read string $code  Unique manager code ("${module}").
 */
class mwap_<vendor>_<module>_man extends mwmod_mw_manager_man {

    /**
     * @param mixed $ap Application instance.
     */
    function __construct($ap) {
        $this->init("<module>", $ap, "<db_table>");
    }

    /**
     * @param  array $tblitem Raw table row.
     * @return mwap_<vendor>_<module>_item
     */
    function create_item($tblitem) {
        return new mwap_<vendor>_<module>_item($tblitem, $this);
    }
}
```

### `src/mwap/modules/<vendor>/<module>/item.php`

```php
<?php
/**
 * Item for the <module> module.
 *
 * Wraps a single row from the `<db_table>` table.
 */
class mwap_<vendor>_<module>_item extends mwmod_mw_manager_item {

    /**
     * @return int|string  Item primary key.
     */
    function get<Module>Id() {
        return $this->get_id();
    }

    // Add one getter per exposed field:
    // function get<FieldCamel>() { return $this->get_data_field("<column>"); }
}
```

## Rules

- **Do not** edit any file inside `src/mwap/` that belongs to the framework submodule.
- Expose the new manager via `__get_priv_*()` or `create_submanager_*()` in the appropriate application class.
- Follow PHPDoc standards from `docs/ai/phpdoc-documentation-guide.md`.
- Refer to `docs/ai/manager-item-pattern.md` for full examples.
