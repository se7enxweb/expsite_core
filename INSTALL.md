# Installing expsite_core

## Requirements

- Exponential CMS (legacy) installation, PHP 8.1 or newer.
- No other extensions are required.

## Steps

1. Place the extension in `extension/expsite_core`.

2. Activate it in `settings/override/site.ini.append.php` (site-wide) or in a siteaccess `site.ini.append.php`:

   ```ini
   [ExtensionSettings]
   ActiveExtensions[]=expsite_core
   ```

   For a single siteaccess use `ActiveAccessExtensions[]` instead.

3. Regenerate the extension autoloads:

   ```bash
   php bin/php/ezpgenerateautoloads.php -e
   ```

4. Clear all caches:

   ```bash
   php bin/php/ezcache.php --clear-all --purge --allow-root-user
   ```

## Configuration

- Exclude content classes from breadcrumbs in `settings/expsite_core.ini.append.php` (or an override of `expsite_core.ini`):

  ```ini
  [PathHelper]
  ExcludedContentTypes[]=folder
  ```

- The breadcrumb root is `content.ini` `[NodeSettings]` `RootNode`.
- The default mail sender comes from `site.ini` `[MailSettings]` `EmailSender` (fallback `AdminEmail`) with the display name from `EmailSenderName`.
