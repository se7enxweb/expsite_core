# Using expsite_core

All examples use real class and method names from `classes/`.

## Mail: expSiteBundleMailHelper

`sendMail( $receivers, $subject, $template, $parameters = array(), $sender = null )` renders a `design:` template with the given parameters and sends the result as HTML mail through `eZMail` / `eZMailTransport`.

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

Accepted receiver formats:

```php
// A single address
$helper->sendMail( 'contact@example.com', $subject, $template );

// A list of addresses
$helper->sendMail( array( 'a@example.com', 'b@example.com' ), $subject, $template );

// Addresses with display names
$helper->sendMail( array( 'a@example.com' => 'Person A' ), $subject, $template );
```

When `$sender` is omitted, the default sender is read from `site.ini` `[MailSettings]` `EmailSender` (falling back to `AdminEmail`), with the display name from `EmailSenderName`.

## Breadcrumbs: expSiteBundlePathHelper

`getPath( $locationId, $options = array() )` walks the node's `path_array` from the configured content root and returns a list of breadcrumb items:

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

Option semantics (from the code):

- `use_all_content_types` — `false` (default) skips classes listed in `expsite_core.ini` `[PathHelper]` `ExcludedContentTypes[]`; `true` keeps them in the path but renders them without a URL.
- `show_current_location` — `true` includes the requested location itself as the last item (always without a URL).
- `absolute_url` — prefixes each URL with `eZSys::serverURL()` and `eZSys::indexDir()`.

The path starts at `content.ini` `[NodeSettings]` `RootNode`; ancestors above that root are omitted.

## Redirects: expSiteBundleRedirectHelper

`checkRedirect( eZContentObjectTreeNode $location )` inspects the object's data map and returns a URL string or `false`:

- `internal_redirect` — an object relation; resolves to the related object's main node URL alias (returns `false` if it points to the current node).
- `external_redirect` — an `ezurl` field (other datatypes fall back to their `toString()` value). Absolute `http(s)://` URLs are returned as-is; relative values are prefixed with the content root's URL alias.

```php
$helper = new expSiteBundleRedirectHelper();
$url = $helper->checkRedirect( $node );
if ( $url !== false )
{
    return eZHTTPTool::redirect( $url, array(), 301 );
}
```

Typical placement is early in a `content/view` override or a custom module, before rendering.

## Customization

### Settings layer

The extension defaults live in `settings/expsite_core.ini.append.php`. Site integrators override them from outside the extension through the standard INI cascade (later wins):

1. Extension defaults — `extension/expsite_core/settings/expsite_core.ini.append.php`
2. Siteaccess — `settings/siteaccess/<siteaccess>/expsite_core.ini.append.php`
3. Extension siteaccess — `extension/<your_extension>/settings/siteaccess/<siteaccess>/expsite_core.ini.append.php`
4. Global override — `settings/override/expsite_core.ini.append.php`

Example override:

```ini
<?php /* #?ini charset="utf-8"?

[PathHelper]
ExcludedContentTypes[]
ExcludedContentTypes[]=folder
ExcludedContentTypes[]=frontpage

*/ ?>
```

Related kernel settings consumed by the helpers:

- `content.ini` `[NodeSettings]` `RootNode` — breadcrumb and relative-redirect root.
- `site.ini` `[MailSettings]` `EmailSender`, `AdminEmail`, `EmailSenderName` — default mail sender.

Clear the INI cache after changes: `php bin/php/ezcache.php --clear-all --purge --allow-root-user`.

### Template layer

The extension itself ships no templates; `settings/design.ini.append.php` only registers `expsite_core` as a design extension. Mail templates are chosen by the caller (`design:mail/contact.tpl` above), so they resolve through the normal design cascade: your design extension's `design/<sitedesign>/templates/` wins over `design/standard/templates/` when your extension is listed earlier and the siteaccess `SiteDesign`/`AdditionalSiteDesignList` includes that design. To restyle a mail, create the template path you pass to `sendMail()` in your own design extension.

### PHP layer

All three helpers are plain classes with protected hook methods — subclass them rather than editing the extension:

- `expSiteBundleMailHelper`: `createSenderAddress()`, `addReceivers()`, `renderTemplate()` are protected and safe to override (for example, to render a text/plain alternative or force a sender domain).
- `expSiteBundlePathHelper`: `excludedContentTypes()` is protected — override it to source exclusions from somewhere other than `expsite_core.ini`.
- `expSiteBundleRedirectHelper`: `getUrlFromAttribute()` and `isAbsoluteUrl()` are protected — override to support additional datatypes or URL schemes.

Register subclasses in your own extension's `autoloads/` class map and regenerate autoloads with `php bin/php/ezpgenerateautoloads.php -e`.
