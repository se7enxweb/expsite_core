Exponential Site Core
===================

General description
-------------------

Exponential Site Core (extension name: `expsite_core`) is a set of site
helper classes for Exponential CMS (eZ Publish Legacy), ported from selected
`netgen/site-bundle` helper classes and reimplemented on the native kernel
APIs (`eZContentObjectTreeNode`, `eZMail`, `eZTemplate`). It provides the
following capabilities:

- Breadcrumb path building - Build a ready-to-render breadcrumb path array
  for any location ID, with class exclusion, current-location and
  absolute-URL options.
- Content-driven redirects - Resolve editorial `internal_redirect` /
  `external_redirect` fields on content objects into redirect URLs, so
  editors can control redirects without developer involvement.
- Templated mail sending - Render a `design:` template with parameters and
  send the result as HTML mail through the kernel mail layer, with flexible
  receiver formats and sensible sender defaults.

The helpers mirror the behaviour of the `netgen/site-bundle` `MailHelper`,
`PathHelper` and `RedirectHelper` services, with no Symfony dependencies -
they are plain PHP classes usable from any module, template operator,
cronjob or block handler.

Features
--------

Exponential Site Core provides the following features in detail:

Key classes

| Class | File | Purpose |
| --- | --- | --- |
| `expSiteBundlePathHelper` | `classes/expsitebundlepathhelper.php` | Builds a breadcrumb path array for a location ID |
| `expSiteBundleRedirectHelper` | `classes/expsitebundleredirecthelper.php` | Resolves `internal_redirect` / `external_redirect` fields to a redirect URL |
| `expSiteBundleMailHelper` | `classes/expsitebundlemailhelper.php` | Sends HTML mail rendered from a template through the kernel mail layer |

Breadcrumbs

- `expSiteBundlePathHelper::getPath( $locationId, $options )` returns an
  array of `text` / `url` / `location` items, honouring
  `use_all_content_types`, `show_current_location` and `absolute_url`
  options.
- If a `breadcrumb_title` field exists and is filled, it is used as the item
  text instead of the node name.
- `use_all_content_types` - `false` (default) skips classes listed in
  `expsite_core.ini` `[PathHelper]` `ExcludedContentTypes[]`; `true` keeps
  them in the path but renders them without a URL.
- `show_current_location` - `true` includes the requested location itself as
  the last item (always without a URL).
- `absolute_url` - prefixes each URL with `eZSys::serverURL()` and
  `eZSys::indexDir()`.
- The path starts at `content.ini` `[NodeSettings]` `RootNode`; ancestors
  above that root are omitted.

Redirects

- `expSiteBundleRedirectHelper::checkRedirect( $node )` returns a redirect
  URL string or `false`.
- `internal_redirect` expects an object relation field; it resolves to the
  related object's main node URL alias (returns `false` if it points to the
  current node).
- `external_redirect` expects an `ezurl` field (other datatypes fall back to
  their string value). Absolute `http(s)://` URLs are returned as-is;
  relative values are prefixed with the content root's URL alias.

Mail

- `expSiteBundleMailHelper::sendMail( $receivers, $subject, $template,
  $parameters, $sender )` renders a `design:` template with the given
  parameters and sends it as HTML mail.
- Receivers can be a string, an indexed array, or an associative
  `email => name` array.
- When `$sender` is omitted, the default sender is read from `site.ini`
  `[MailSettings]` `EmailSender` (falling back to `AdminEmail`), with the
  display name from `EmailSenderName`.

Configuration

Settings live in `settings/expsite_core.ini.append.php`:

```ini
[PathHelper]
# Class identifiers to exclude from the breadcrumb path
ExcludedContentTypes[]
```

`settings/design.ini.append.php` registers `expsite_core` as a design
extension. The path helper reads the breadcrumb root from `content.ini`
`[NodeSettings]` `RootNode`.

Version
-------

- The current version of Exponential Site Core is 1.0.0
- Last Major update: July 30, 2026

Copyright
---------

- Exponential Site Core is copyright 1998 - 2026 7x
- See: [LICENSE.md](LICENSE.md) for more information on the terms of the copyright and license

License
-------

Exponential Site Core is licensed under the GNU General Public License.

The complete license agreement is included in the LICENSE.md file.

Exponential Site Core is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License or at your
option a later version.

Exponential Site Core is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

The GNU GPL gives you the right to use, modify and redistribute
Exponential Site Core under certain conditions. The GNU GPL license
is distributed with the software, see the file LICENSE.md.

It is also available at https://www.gnu.org/licenses/gpl.txt

You should have received a copy of the GNU General Public License
along with Exponential Site Core in LICENSE.md.  If not, see https://www.gnu.org/licenses/.

Using Exponential Site Core under the terms of the GNU GPL is free (as in freedom).

For more information or questions please contact info@se7enx.com

Requirements
------------

The following requirements exists for using Exponential Site Core extension:

Exponential CMS / eZ Publish Legacy version
- Make sure you use Exponential 6 (eZ Publish Legacy) or higher.

PHP version
- Make sure you have PHP 8.1 or higher.

Other extensions
- No other extensions are required; the helpers use only kernel classes.

Installation
------------

Installation is the standard legacy extension procedure: place the extension
in `extension/expsite_core`, activate it via `[ExtensionSettings]`
`ActiveExtensions[]` (or `ActiveAccessExtensions[]` for a single siteaccess),
regenerate the extension autoloads and clear all caches. Optionally exclude
content classes from breadcrumbs via `[PathHelper]` `ExcludedContentTypes[]`.

See the complete step-by-step instructions in [INSTALL.md](INSTALL.md).

Usage
-----

All three helpers are plain classes instantiated with `new` - no service
container required.

Send a templated HTML mail:

```php
$helper = new expSiteBundleMailHelper();
$helper->sendMail(
    array( 'contact@example.com' => 'Site Contact' ),
    'New message',
    'design:mail/contact.tpl',
    array( 'name' => 'A visitor' ),
    array( 'noreply@example.com' => 'No reply' )
);
```

Build a breadcrumb path:

```php
$helper = new expSiteBundlePathHelper();
$path = $helper->getPath( 123, array(
    'use_all_content_types' => false,
    'show_current_location' => false,
    'absolute_url' => false,
) );

foreach ( $path as $item )
{
    // $item['text']     — item label (breadcrumb_title field if present and filled, else node name)
    // $item['url']      — '/url_alias', absolute when absolute_url is true, or false when suppressed
    // $item['location'] — the eZContentObjectTreeNode
}
```

Check for an editorial redirect (typical placement is early in a
`content/view` override or a custom module, before rendering):

```php
$helper = new expSiteBundleRedirectHelper();
$url = $helper->checkRedirect( $node );
if ( $url !== false )
{
    return eZHTTPTool::redirect( $url, array(), 301 );
}
```

See [doc/USAGE.md](doc/USAGE.md) for the full verified examples and the
customization guide covering all three layers: the settings layer (the INI
cascade for `expsite_core.ini` overrides and the related kernel settings),
the template layer (mail templates resolve through the normal design
cascade; restyle by shipping the template path you pass to `sendMail()` in
your own design extension) and the PHP layer (all helpers expose protected
hook methods - `createSenderAddress()`, `addReceivers()`,
`renderTemplate()`, `excludedContentTypes()`, `getUrlFromAttribute()`,
`isAbsoluteUrl()` - intended for subclassing instead of editing the
extension).

Documentation
-------------

| Document | Description |
| --- | --- |
| [INSTALL.md](INSTALL.md) | Activation and configuration instructions |
| [doc/USAGE.md](doc/USAGE.md) | Verified code examples plus the settings / template / PHP customization layers |
| [doc/FAQ.md](doc/FAQ.md) | Answers to the most common questions |
| [doc/TODO.md](doc/TODO.md) | Known gaps and planned improvements |
| [doc/SUPPORT.md](doc/SUPPORT.md) | How and where to get help |
| [LICENSE.md](LICENSE.md) | The complete GNU General Public License agreement |

Troubleshooting
---------------

Read the FAQ
- Some problems are more common than others. The most common ones are listed in the [doc/FAQ.md](doc/FAQ.md).

Use our support systems
- If you have any questions not handled by this document or the FAQ you can reach us through [se7enx.com](https://se7enx.com)
- If you find a bug or defect, please report it to the [Exponential Site Core: Issue Tracker](https://github.com/se7enxweb/expsite_core/issues)
