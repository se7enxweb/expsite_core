# expsite_core

Site helper classes for Exponential CMS (legacy): breadcrumb path building, content-driven redirects and templated mail sending. Ported from selected `netgen/site-bundle` helper classes.

## Key classes

| Class | File | Purpose |
| --- | --- | --- |
| `expSiteBundlePathHelper` | `classes/expsitebundlepathhelper.php` | Builds a breadcrumb path array for a location ID |
| `expSiteBundleRedirectHelper` | `classes/expsitebundleredirecthelper.php` | Resolves `internal_redirect` / `external_redirect` fields to a redirect URL |
| `expSiteBundleMailHelper` | `classes/expsitebundlemailhelper.php` | Sends HTML mail rendered from a template through the kernel mail layer |

## Configuration

Settings live in `settings/expsite_core.ini.append.php`:

```ini
[PathHelper]
# Class identifiers to exclude from the breadcrumb path
ExcludedContentTypes[]
```

`settings/design.ini.append.php` registers `expsite_core` as a design extension.

The path helper reads the breadcrumb root from `content.ini` `[NodeSettings]` `RootNode`. The mail helper reads the default sender from `site.ini` `[MailSettings]` `EmailSender` (falling back to `AdminEmail`) and the sender name from `EmailSenderName`.

## Helpers at a glance

- `expSiteBundlePathHelper::getPath( $locationId, $options )` returns an array of `text` / `url` / `location` items, honouring `use_all_content_types`, `show_current_location` and `absolute_url` options. If a `breadcrumb_title` field exists and is filled, it is used as the item text.
- `expSiteBundleRedirectHelper::checkRedirect( $node )` returns a redirect URL string or `false`. `internal_redirect` expects an object relation field; `external_redirect` expects an `ezurl` field (other datatypes fall back to their string value).
- `expSiteBundleMailHelper::sendMail( $receivers, $subject, $template, $parameters, $sender )` renders a `design:` template with the given parameters and sends it as HTML mail. Receivers can be a string, an indexed array, or an associative `email => name` array.

See `doc/USAGE.md` for full examples.

## Provenance

The helpers mirror the behaviour of the `netgen/site-bundle` `MailHelper`, `PathHelper` and `RedirectHelper` services, reimplemented on the native kernel APIs (`eZContentObjectTreeNode`, `eZMail`, `eZTemplate`).

## Documentation

- `INSTALL.md` — activation and configuration
- `doc/USAGE.md` — verified code examples and customization guide
- `doc/FAQ.md` — common questions
- `doc/TODO.md` — known gaps
- `doc/SUPPORT.md` — how to get help
