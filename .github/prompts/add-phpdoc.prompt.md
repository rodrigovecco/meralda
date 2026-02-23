---
mode: agent
description: Add or improve PHPDoc documentation on a Meralda PHP class following the project's documentation standards.
---

# Add PHPDoc to a Meralda Class

Analyze the target PHP file and add or correct PHPDoc comments following the Meralda framework standards from `docs/ai/phpdoc-documentation-guide.md`.

## What to add / verify

### Class-level docblock

```php
/**
 * One-line summary of the class.
 *
 * Optional longer description explaining key responsibilities,
 * architecture patterns used, or important lifecycle notes.
 *
 * @property-read TypeName  $propertyName  Description (for __get_priv_*() properties).
 */
class ClassName extends ParentClass {
```

### `@property-read` rules

- Every private property exposed through a `__get_priv_*()` method **must** have a corresponding `@property-read` tag on the class docblock.
- Format: `@property-read <type> $<name>  <Description ending with period.>`
- Use concrete types (not `mixed`) when the type is known.

### Method docblocks

Every `public` or `protected` method needs:

```php
/**
 * One-line description of what the method does.
 *
 * @param  TypeName  $paramName  What this parameter represents.
 * @return TypeName              What is returned, or `void`.
 */
```

- Use `@internal` on methods prefixed with `__get_priv_`.
- `@override` is acceptable for PHP 8.3+; otherwise add a `// Overrides ParentClass`.

## Conventions checklist

- [ ] Class docblock present and starts with a clear one-liner.
- [ ] All `private` magic-accessed properties declared with `@property-read`.
- [ ] All `public`/`protected` methods have `@param` and `@return`.
- [ ] No undocumented `__construct()` parameters.
- [ ] Types are specific: `string`, `int`, `SomeManager`, `array`, `void` — not `mixed` unless unavoidable.

## Reference

Full examples are in `docs/ai/phpdoc-documentation-guide.md`.
