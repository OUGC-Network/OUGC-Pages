<p align="center">
    <a href="" rel="noopener">
        [![Status](https://github.com/OUGC-Network/OUGC-Pages/assets/1786584/5bb99abc-2937-44ba-a2c4-d3f4ccb1f8ab)]()
        ![image](https://github.com/OUGC-Network/OUGC-Pages/assets/1786584/5bb99abc-2937-44ba-a2c4-d3f4ccb1f8ab)
        <img width=700px height=400px src="https://github.com/OUGC-Network/OUGC-Pages/assets/1786584/5bb99abc-2937-44ba-a2c4-d3f4ccb1f8ab" alt="Project logo">
    </a>
</p>

<h3 align="center">OUGC Pages</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/OUGC-Pages.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/OUGC-Pages.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Create additional HTML or PHP pages directly from the control panel.
    <br> 
</p>

## 📝 Table of Contents <a name = "tableOfContents"></a>

- [About](#about)
- [Getting Started](#gettingStarted)
    - [Dependencies](#dependencies)
    - [Install](#install)
    - [Update](#update)
- [Settings](#settings)
    - [Disable PHP Pages](#settingsDisablePHP)
    - [Configure SEO Urls](#settingsSEO)
- [Templates](#templates)
- [Usage](#usage)
    - [Categories](#usageCategories)
    - [Pages](#usagePages)
    - [Example Pages](#examplePages)
- [Plugins](#plugins)
    - [Global Scope](#pluginGlobal)
    - [Hooks](#pluginHooks)
    - [Methods](#pluginMethods)
    - [Constants](#pluginConstants)
- [Built Using](#builtUsing)
- [Authors](#authors)
- [Contributing](../CONTRIBUTING.md)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🧐 About <a name = "about"></a>

OUGC Pages is a versatile and feature-rich PHP plugin designed for MyBB forum administrators. It empowers administrators
to effortlessly create, manage, and customize unlimited HTML or PHP pages within their forum. With this plugin,
administrators can enhance their forum's functionality by adding unique and personalized content. The plugin offers a
user-friendly interface for creating, editing, and organizing pages, while its SEO-friendly URLs ensure optimal search
engine visibility. Administrators can take advantage of features like seamless page export and import, extensive group
access permissions, unlimited page categories, and automatic menu creation. From About Us pages to FAQ sections and
more, OUGC Pages provides a comprehensive solution for creating engaging and dynamic custom content within MyBB forums.

[Go up to Table of Contents](#tableOfContents)

## 🏁 Getting Started <a name = "gettingStarted"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

> [MyBB](https://docs.mybb.com/1.8/install/) >= 1.8.30
> PHP >= 7.4
> [MyBB-PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php?action=view&pid=6) site or
   from the [repository releases](./releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.

  ```
   .
   ├── inc
   │ ├── plugins
   │ │ ├── ougc_pages.php
   │ ├── languages
   │ │ ├── espanol
   │ │ │ ├── ougc_pages.lang.php
   │ │ │ ├── admin
   │ │ │ │ ├── config_ougc_pages.lang.php
   │ │ ├── english
   │ │ │ ├── ougc_pages.lang.php
   │ │ │ ├── admin
   │ │ │ │ ├── config_ougc_pages.lang.php
   ├── admin
   │ ├── modules
   │ │ ├── config
   │ │ │ ├──ougc_pages.php
   └── pages.php
   ```

3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Configuration » Manage Pages_ to create page categories and pages.

### 🔧 Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _Configuration » Manage Pages_ to create page categories and pages.

[Go up to Table of Contents](#tableOfContents)

## 🚀 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Global Settings

- **Use SEO friendly URLs**
  -`text` Default: `yes`
  -_Whether if to enable SEO friendly URLs for pages._

**Page URL Scheme**
`text` Default: `page-{url}`
_Enter the Page URL scheme. Leave empty to disable SEO URLs for Pages._

**Category URL Scheme**
`text` Default: `category-{url}`
_Enter the Category URL scheme. Leave empty to disable SEO URLs for Categories._

**Items Per Page**
`numeric` Default: `20`
_Maximum number of items to show per page in the ACP list._

**UserCP Nav Priority**
`select` Default: `40`
_The priority given to UserCP navigation categories._

### Disable PHP Pages <a name = "settingsDisablePHP"></a>

Additionally, you can stop `initExecute()` from using `eval()` by modifying a constant in the plugin
file `./inc/plugins/ougc_pages.php`:

`OUGC_PAGES_DISABLE_EVAL` `bool`; default is set to `true`

[Go up to Table of Contents](#tableOfContents)

## 🚀 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin. Uncommon in plugins, we use some templates exclusively
for the Administrator Control Panel.

- `ougcpages` _front end_; used when viewing a category or non-PHP pages
- `ougcpages_adminCategoryName` _back end_; used when viewing a category
- `ougcpages_adminCategoryStatus` _back end_; used when viewing a category or page
- `ougcpages_adminCodeMirror` _back end_; used when editing a page
- `ougcpages_adminCodeMirrorFooter` _back end_; used when editing a page
- `ougcpages_adminGroupSelect` _back end_; used when editing a category or page
- `ougcpages_adminPageName` _back end_; used when viewing a page
- `ougcpages_category_list` _front end_; used when viewing a category
- `ougcpages_category_list_empty` _front end_; used when viewing a category
- `ougcpages_category_list_item` _front end_; used when viewing a category
- `ougcpages_menu` _front end_; used when category `buildMenu` is `1`
- `ougcpages_menu_css` _front end_; used when category `buildMenu` is `1`
- `ougcpages_menu_item` _front end_; used when category `buildMenu` is `1`
- `ougcpages_navigation` _front end_; used when category `displayNavigation` is `1`
- `ougcpages_navigation_next` _front end_; used when category `displayNavigation` is `1`
- `ougcpages_navigation_previous` _front end_; used when category `displayNavigation` is `1`
- `ougcpages_wrapper` _front end_; used when page `wrapper` is `1`
- `ougcpages_wrapper_edited` _front end_; used page category `wrapper` is `1`
- `ougcpages_wrapper_ucp` _front end_; used when category `wrapucp` is `1`
- `ougcpages_wrapper_ucp_nav` _front end_; used when category `wrapucp` is `1`
- `ougcpages_wrapper_ucp_nav_item` _front end_; used when category `wrapucp` is `1`

[Go up to Table of Contents](#tableOfContents)

## 🎈 Usage <a name="usage"></a>

Add notes about how to use the system.

### 🎈 Categories <a name="usageCategories"></a>

### 🎈 Pages <a name="usagePages"></a>

### 🎈 Example Pages <a name="examplePages"></a>

The download package ships with nine example pages that can be used as production pages or as a reference for designing
custom pages.

- **Forum stats signature** ([see file](../Examples/Signature/OUGC_Pages_Signature.xml)) A dynamically generated
  signature
  image that displays stats about your forum.
- **Banned List** ([see file](../Examples/OUGC_Pages_Banned_List.xml)) Displays a list of banned accounts.
- **HTML Test Page** ([see file](../Examples/OUGC_Pages_HTML_Test_Page.xml)) Plain HTML page meant to serve as
  reference.
- **List Profile Fields** ([see file](../Examples/OUGC_Pages_List_Profile_Fields.xml)) Displays a list of users and
  their
  custom profile fields values.
- **New Thread** ([see file](../Examples/OUGC_Pages_New_Thread.xml)) A new thread page meant to serve as reference.
- **PHP Test Page** ([see file](../Examples/OUGC_Pages_PHP_Test_Page.xml)) Basic PHP page that uses the MyBB parser
  meant to
  serve as reference.
- **Profile Fields** ([see file](../Examples/OUGC_Pages_Profile_Fields.xml)) Allow users to update their custom profile
  fields.
- **ShoutBox Page** ([see file](../Examples/OUGC_Pages_ShoutBox_Page.xml)) Displays the DVZ Shoutbox in a custom PHP
  page.
- **Sticky Threads** ([see file](../Examples/OUGC_Pages_Sticky_Threads.xml)) Displays a list with all sticky threads.

[Go up to Table of Contents](#tableOfContents)

## 🎈 Plugins <a name="plugins"></a>

Provides a list of available variables, functions, and methods for plugins to use.

### Variables available at the global scope: <a name="pluginGlobal"></a>

- `(array) $categoriesCache` array containing cached categories data when `visible` is equal to `1` and `allowedGroups`
  is not empty, ordered
  by `cid, disporder`, array key is set to category identifier `cid`:
    - `(string) name`
    - `(string) description`
    - `(string) url`
    - `(string) allowedGroups`
        - `-1` for all groups
        - CSV for allowed groups
    - `(int) breadcrumb`
    - `(int) displayNavigation`
    - `(int) buildMenu`
    - `(int) wrapucp`
- `(array) $pagesCache` array containing cached pages data when `visible` is equal to `1` and `allowedGroups` is not
  empty, ordered
  by `pid, disporder`, array key is set to page identifier `pid`:
    - `(int) cid`
    - `(string) name`
    - `(string) description`
    - `(string) url`
    - `(string) allowedGroups`
        - `-1` for all groups
        - CSV for allowed groups
    - `(int) menuItem`
    - `(int) wrapper`
    - `(int) wol`
    - `(int) php`
    - `(int) classicTemplate`
    - `(int) init`
    - `(int) dateline`
- `(bool) $isCategory` `true` if current page is a category.
- `(bool) $isPage``true` if current page is a page.
- `(int) $categoryID` current category identifier.
- `(int) $pageID` current page identifier, `0` when `$isPage` is `false`.
- `(array) $categoryData` array containing current category page:
    - `(string) name`
    - `(string) description`
    - `(string) url`
    - `(string) allowedGroups`
        - `-1` for all groups
        - CSV for allowed groups
    - `(int) breadcrumb`
    - `(int) displayNavigation`
    - `(int) buildMenu`
    - `(int) wrapucp`
- `(array) $pageData` array containing current page, empty when `$isPage` is `false`.
    - `(int) cid`
    - `(string) name`
    - `(string) description`
    - `(string) url`
    - `(string) allowedGroups`
        - `-1` for all groups
        - CSV for allowed groups
    - `(int) menuItem`
    - `(int) wrapper`
    - `(int) wol`
    - `(int) php`
    - `(int) classicTemplate`
    - `(int) init`
    - `(string) template`
    - `(int) dateline`

### List of available hooks: <a name="pluginHooks"></a>

- `ougcPagesExecutionInit`
- `ougcPagesExecutionGlobalStart`
- `ougcPagesExecutionGlobalIntermediate`
- `ougcPagesExecutionGlobalEnd`
- `oucPagesCategoryInsertEnd` Array object is passed by reference with the following variables:
    - `(array) categoryID` inserted category identifier.
    - `(array) pcategoryData` array containing category data
        - `(string) name`
        - `(string) description`
        - `(string) url`
        - `(string) allowedGroups`
            - `-1` for all groups
            - CSV for allowed groups
            - empty for none
        - `(int) disporder`
        - `(int) visible`
        - `(int) breadcrumb`
        - `(int) displayNavigation`
        - `(int) buildMenu`
        - `(int) wrapucp`
- `oucPagesCategoryUpdateEnd` Array object is passed by reference with the following variables:
    - `(array) categoryID` current category identifier.
    - `(array) categoryData` array containing category data
        - `(string) name`
        - `(string) description`
        - `(string) url`
        - `(string) allowedGroups`
            - `-1` for all groups
            - CSV for allowed groups
            - empty for none
        - `(int) disporder`
        - `(int) visible`
        - `(int) breadcrumb`
        - `(int) displayNavigation`
        - `(int) buildMenu`
        - `(int) wrapucp`
- `oucPagesCategoryDeleteEnd` Variable passed by reference:
    - `(int) $categoryID` current page identifier.
- `oucPagesPageInsertEnd` Array object is passed by reference with the following variables:
    - `(array) pageID` inserted page identifier.
    - `(array) pageData` array containing page data
        - `(int) cid`
        - `(string) name`
        - `(string) description`
        - `(string) url`
        - `(string) allowedGroups`
            - `-1` for all groups
            - CSV for allowed groups
            - empty for none
        - `(int) disporder`
        - `(int) visible`
        - `(int) menuItem`
        - `(int) wrapper`
        - `(int) wol`
        - `(int) php`
        - `(int) classicTemplate`
        - `(int) init`
        - `(string) template`
        - `(int) dateline`
- `oucPagesPageUpdateEnd` Array object is passed by reference with the following variables:
    - `(array) pageID` current page identifier.
    - `(array) pageData` array containing page data
        - `(int) cid`
        - `(string) name`
        - `(string) description`
        - `(string) url`
        - `(string) allowedGroups`
            - `-1` for all groups
            - CSV for allowed groups
            - empty for none
        - `(int) disporder`
        - `(int) visible`
        - `(int) menuItem`
        - `(int) wrapper`
        - `(int) wol`
        - `(int) php`
        - `(int) classicTemplate`
        - `(int) init`
        - `(string) template`
        - `(int) dateline`
- `oucPagesPageDeleteEnd` Variable passed by reference:
    - `(int) $pageID` current page identifier.
- `oucPagesStart`
- `oucPagesEnd`

### List of available methods at the `OUGCPages\Core` namespace: <a name="pluginMethods"></a>

- `function loadLanguage(): void { ... }`
- `function getSetting(string $settingKey = ''): string { ... }`
- `function cacheUpdate(): void { ... }`
- `function cacheGetPages(): array { ... }`
- `function cacheGetCategories(): array { ... }`
- `function initExecute(int $pageID): never { ... }`
- `function initSession(): void { ... }`
- `function categoryInsert(array $categoryData = [], int $categoryID = 0, bool $update = false): int { ... }`
- `function categoryUpdate(array $data = [], int $cid = 0): int { ... }`
- `function categoryDelete(int $categoryID): bool { ... }`
- `function categoryGet(int $categoryID, string $categoryUrl = ''): array { ... }`
- `function categoryQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): array { ... }`
- `function categoryGetByUrl(string $categoryUrl): array { ... }`
- `function categoryGetLink(int $categoryID): string { ... }`
- `function pageInsert(array $pageData = [], int $pageID = 0, bool $update = false): int { ... }`
- `function pageUpdate(array $data = [], int $pageID = 0): int { ... }`
- `function pageDelete(int $pageID): int { ... }`
- `function pageGet(int $pageID, string $pageUrl = ''): array { ... }`
- `function pageQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): array { ... }`
- `function pageGetByUrl(string $url): array { ... }`
- `function pageGetLink(int $pageID): string { ... }`

### List of available methods at the `OUGCPages\Core` namespace: <a name="pluginConstants"></a>

The following is a list of constants defined dynamically, `defined()`should be used to verify they are defined.

- `OUGC_PAGES_STATUS_IS_CATEGORY` `int` defined as the category identifier when trying to view a valid category
- `OUGC_PAGES_STATUS_IS_PAGE` `int` defined as the page identifier when trying to view a valid page
- `OUGC_PAGES_STATUS_CATEGORY_INVALID` `bool` defined as `true` when trying to view an invalid category
- `OUGC_PAGES_STATUS_CATEGORY_NO_PERMISSION` `bool` defined as `true` when permission to view a category is denied
- `OUGC_PAGES_STATUS_PAGE_INVALID` `bool` defined as `true` when trying to view an invalid page
- `OUGC_PAGES_STATUS_PAGE_NO_PERMISSION` `bool` defined as `true` when permission to view a page is denied
- `OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START` `int` defined as the page identifier when trying to view a valid page
  which `php` value is `1` and `init` value is `2`
- `OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE` `int` defined as the page identifier when trying to view a valid
  page which `php` value is `1` and `init` value is `3`

[Go up to Table of Contents](#tableOfContents)

## ⛏️ Built Using <a name = "builtUsing"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#tableOfContents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](./contributors) who participated in this
project.

[Go up to Table of Contents](#tableOfContents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#tableOfContents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#tableOfContents)