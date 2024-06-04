# Site Copy X for Craft CMS 5

This plugin makes it easy to copy the content of an entry from a site to another.

### Supported elements

- Entries
- Global sets
- Assets
- Categories
- Craft commerce products

> **_NOTE:_** This plugin only works with elements that can be propagated to other sites. If the propagation method is set to "Only save entries to the site they were created in", we cannot copy the element.


## Using the Site Copy X plugin

### Copy entry to another site

When editing an entry in the sidebar on the right you will find a toggle to enable
site syncing. From there select the site that you want to overwrite with the currently
visible content and then just save the entry like normal. Any content of the current
site will be automatically copied to the selected site.

It's even possible to copy the current entry to multiple sites. 

As the copy will trigger a queue job, the changes might be not be reflected instantaneously in the other sites. 

***Attention:*** This action will OVERWRITE all content from the selected site.

![Screenshot](resources/screenshots/screenshot1.png)

### Global Sets and assets

In addition to entries, you can also copy global sets and assets. For global sets, you will find the toggle at the very bottom of the content.

### Craft Commerce

This plugin is compatible with Craft Commerce products.

### Bulk copy

If you select entries on their index views, you will be able to bulk copy them over to another site.

![Screenshot](resources/screenshots/bulk-copy.png)

### Choose the content you want to be copied
In the plugin settings you can configure the content that gets copied from the current entry.
Per default it copies only the entries content (without meta data like title and slug)

### Activate automatic copy for specific entries

![Screenshot](resources/screenshots/screenshot2.png)

In the plugin settings you can configure the automatic copy function. 
With this you can configure if some entries should be automatically set
to be copied to a specific site. 

## Requirements

This plugin requires Craft CMS 5.0.0 or later.

## Installation

Install using `composer require goldinteractive/craft-sitecopy` 
