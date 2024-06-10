# Changelog

## 2.1.0 - 2024-06-10

### Added

- Added a bulk copy feature for entries on the element index view
- Added a settings option to copy craft's native image alt field (enabled per default for new installs)

### Changed

- If an entry doesn't exist yet and the propagation method is set to "Let each entry choose which sites it should be saved to", Site Copy X will now propagate the entry to the new site.

### Fixed

-  Fixed the copy of variant fields on save of the product

## 2.0.1 - 2024-04-10

### Changed

- Updated dependency to Craft CMS 5.0.0 stable from beta

## 2.0.0 - 2024-03-11

### Changed

- Updated dependencies to make the plugin work for Craft CMS 5.0.0-beta-6

## 1.1.0 - 2024-01-22

### Changed

- Site Copy X now requires Craft CMS 4.5.11

### Fixed
- Fixed a database error that could occur when saving an entry while the copy-to-site job is running [#9](https://github.com/Goldinteractive/craft-sitecopy/issues/9)

## 1.0.10 - 2023-10-19

### Changed

- The list of sites to copy to is now sorted by site group to provide a better ui for multisite setups [#6](https://github.com/Goldinteractive/craft-sitecopy/issues/6)


## 1.0.9 - 2023-10-16

### Added

- Added a new setting option that allows administrators to set a queue priority to the plugin jobs if they desire (thanks @Alxmerino)

## 1.0.8 - 2023-09-11

### Changed

- Rolled back the changes from version 1.0.7 as the related issue got fixed in craft 4.5.1

## 1.0.7 - 2023-08-23

### Fixed
- Fixed an issue with craft 4.5.0 matrix field values not being copied

## 1.0.6 - 2023-07-06

### Added
- Added a new "select all" checkbox

## 1.0.5 - 2023-04-03

### Added
- Added the ability to copy commerce variant fields

## 1.0.4 - 2022-11-24

### Added
- Site Copy now works with categories too!

## 1.0.3 - 2022-11-11

### Changed
- Changed the plugin handle and name to not confuse it with the old plugin

## 1.0.2 - 2022-09-23

### Fixed
- Fixed bugs caused by the changing of the plugin handle

## 1.0.1 - 2022-09-21

### Changed
- Changed the plugin handle to separate it from the craft 3 plugin

## 1.0.0 - 2022-09-20
### Added
- Support for craft 4!

### Changed
- The user can now only copy to sites he is allowed to edit (previously he could copy to **any** site, regardless of permissions)

## 0.8.0 - 2022-07-22
### Fixed
- Fixed an issue where the content of nested blocks with the propagation method "all" would be copied to too many sites ([#33](https://github.com/Goldinteractive/craft3-sitecopy/issues/33))

## 0.7.1 - 2022-06-16
### Fixed
- Fixed a bug on siteGroups where element sections are disabled for the primary site

## 0.7.0 - 2021-12-23
### Changed
- Sitecopy now requires Craft 3.7 or later

### Fixed
- Fixed compatibility for new Craft 3.7 draft system ([#28](https://github.com/Goldinteractive/craft3-sitecopy/issues/28))

## 0.6.5 - 2021-11-23
### Changed
- Better German translations

## 0.6.4 - 2021-04-21
### Added
- Ability to copy asset fields

## 0.6.3 - 2021-01-11
### Fixed
- Fixed an error that was thrown on copy of a craft commerce product

## 0.6.2 - 2020-09-02
### Fixed
- Fixed an error that would be thrown when copying a deactivated entry

## 0.6.1 - 2020-08-27
### Fixed
- Possible error when trying to copy global set ([#23](https://github.com/Goldinteractive/craft3-sitecopy/issues/23))

## 0.6.0 - 2020-08-13
### Added
- Ability to copy global sets

### Changed
- Automatic copy: Renamed current OR implementation to XOR and added new non-breaking "OR" check method.

### Fixed
- Deactivated fields now get copied to the target site too ([#21](https://github.com/Goldinteractive/craft3-sitecopy/issues/21))

## 0.5.3 - 2020-08-10
### Fixed
- Fixed an issue where unchanged neo blocks on the target site would be wiped ([#19](https://github.com/Goldinteractive/craft3-sitecopy/issues/19))

## 0.5.2 - 2020-04-28
### Fixed
- Fixed a bug where integer site ids would break the plugin functionality ([#13](https://github.com/Goldinteractive/craft3-sitecopy/issues/13))

## 0.5.1 - 2020-03-02
### Fixed
- Craft 3.4 compatibility

## 0.5.0 - 2020-02-05
### Added
- Possibility to choose what fields you want to copy

## 0.4.1 - 2019-12-06
### Fixed
- Fixed method parameter type "object" to ensure PHP 7.1 compatibility

## 0.4.0 - 2019-11-04
### Added
- Possibility to copy to multiple sites at once
- Compatibility for commerce products

### Changed
- Outsource element syncing to queue

