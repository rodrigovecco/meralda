---
mode: agent
description: Add a new AJAX/XML endpoint command to a Meralda subinterface class.
---

# New AJAX / XML Endpoint

Add an `execfrommain_getcmd_sxml_<cmdname>()` command method to an existing (or new) subinterface class.

## Required information

| Variable | Description | Example |
|----------|-------------|---------|
| `<main-ui-code>` | Main UI interface code | `admin` |
| `<subinterface-code>` | Hyphen-delimited subinterface path | `admin-users` |
| `<cmdname>` | Command name (lowercase, no hyphens) | `updatestatus` |
| Purpose | What the command should do | Update user's active flag |

## URL that will trigger this command

```
/get/<main-ui-code>/sxml/ui/<subinterface-code>/<cmdname>.xml
```

## Method signature to implement

```php
/**
 * AJAX endpoint: <cmdname>.
 *
 * Called via GET /get/<main-ui-code>/sxml/ui/<subinterface-code>/<cmdname>.xml
 *
 * @return void  Outputs XML directly.
 */
function execfrommain_getcmd_sxml_<cmdname>() {
    // 1. Read parameters from $this->cmdparams
    // 2. Perform business logic (use managers from $this->mainap)
    // 3. Build and output XML response
}
```

## Parameter access patterns

```php
// Numeric positional param
$itemId = $this->cmdparams[0];

// Named key-value param (e.g. /status/active)
$status = $this->get_cmdparam("status");
```

## XML response helpers

```php
// Success response
$this->print_sxml_ok();

// Error response
$this->print_sxml_error("Error message");

// Custom XML (use framework XML builder)
$xml = $this->create_xml_root();
// ...populate $xml...
$this->print_sxml($xml);
```

## Rules

- Method must live in a class extending `mwmod_mw_ui_sub_uiabs`.
- Subinterface codes use hyphens — do not use underscores or slashes.
- The framework routes automatically; do **not** add manual routing.
- Refer to `docs/ai/ajax-xml-endpoints-pattern.md` for complete routing details.
