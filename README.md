# Site Copy X for Craft CMS 4/5

This plugin makes it easy to copy the content of an element from one site to another, with fine-grained control over what gets copied.

## Supported elements

- Entries
- Global sets
- Assets
- Categories
- Craft Commerce products (including variant fields and titles)

---

## Features

### Copy to another site

When editing an element, the sidebar shows a **"Copy to site"** toggle. Enable it, select one or more target sites, and save — the content is copied in the background via Craft's queue.

> **Attention:** This will **overwrite** all content on the selected target sites.

![Screenshot](resources/screenshots/screenshot1.png)

### Bulk copy from the element index

Select multiple entries, assets, categories, or Commerce products from the element index and use the **"Copy to site"** action to copy them all at once. A modal lets you choose the target sites before confirming.

Bulk copy is available for:

- Entries (section-based sources only)
- Assets
- Categories
- Craft Commerce products

### Per-field selection

When "Fields (Content)" is enabled in plugin settings, the sidebar widget shows a **checkbox for each field** in the element's field layout. Uncheck any field you don't want copied on that specific save. All fields are pre-selected by default.

### Choose which attributes to copy

In the plugin settings you can configure which attributes are copied globally:

| Attribute         | Description                           |
| ----------------- | ------------------------------------- |
| Fields (Content)  | All custom field values               |
| Title             | The element title                     |
| Slug              | The URL slug                          |
| Commerce Variants | Variant custom fields and titles      |

### Automatic copy rules

![Screenshot](resources/screenshots/screenshot2.png)

Configure rules in the plugin settings to automatically pre-select target sites when editing specific entries. Rules can match by entry ID, type, section, site, or other criteria, and support `equals` / `does not equal` operators with `AND`, `OR`, and `XOR` logic.

### Global sets and assets

For global sets, the copy toggle appears at the bottom of the content area. Assets are supported through the element index bulk action and the standard sidebar widget.

### Craft Commerce

Variant custom fields and titles are copied when the corresponding attributes are enabled in plugin settings.

---

## How it works

The copy is handled by a **queue job** (`SyncElementContent`), so changes may not appear immediately on target sites. The job:

1. Serializes the selected fields/attributes from the source element
2. Remaps linked element IDs and Link field reference tags to the target site
3. Saves each target site element with propagation disabled to prevent cascading back to the source site

---

## Requirements

Craft CMS 4.5.11 or later (compatible with both Craft 4 and Craft 5).

## Installation

```bash
composer require teamnovu/craft-sitecopy
```
