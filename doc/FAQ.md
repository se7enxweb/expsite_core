# expsite_core FAQ

## Where does the default mail sender come from?

`expSiteBundleMailHelper` reads `site.ini` `[MailSettings]` `EmailSender`, falls back to `AdminEmail`, and uses `EmailSenderName` as the display name. Note: the `[MailSettings]` `SenderEmail` / `SenderName` keys declared in this extension's own `expsite_core.ini.append.php` are not read by the code (see `TODO.md`).

## Why are some ancestors missing from my breadcrumb?

Two reasons, both by design: the path only starts at `content.ini` `[NodeSettings]` `RootNode` (everything above is dropped), and classes listed in `expsite_core.ini` `[PathHelper]` `ExcludedContentTypes[]` are skipped unless you pass `use_all_content_types => true` (which keeps them but without a link).

## How do I change a breadcrumb item's label without renaming the content?

Add a `breadcrumb_title` field to the content class. When the field exists and has content, `expSiteBundlePathHelper` uses it as the item text instead of the node name.

## What fields does the redirect helper look for?

`internal_redirect` (an object relation, resolved to the related object's main node URL alias) and `external_redirect` (an `ezurl` field; other datatypes fall back to their string value). If neither field exists or has content, `checkRedirect()` returns `false`.

## Does checkRedirect() perform the redirect itself?

No. It only returns the URL string (or `false`). The caller decides how to redirect, e.g. `eZHTTPTool::redirect( $url, array(), 301 )`.

## The extension registers itself as a design extension — where are its templates?

There are none yet. `settings/design.ini.append.php` reserves the design slot, but the extension currently ships no `design/` directory. Mail templates are provided by the calling site design.
