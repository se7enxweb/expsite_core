# expsite_core TODO

Code-observed gaps; no promises attached.

- `settings/expsite_core.ini.append.php` declares `[MailSettings]` `SenderEmail` / `SenderName`, but `expSiteBundleMailHelper` never reads them — it reads `site.ini` `[MailSettings]` `EmailSender` / `AdminEmail` / `EmailSenderName` instead. Either wire the extension settings into the helper or drop the dead keys.
- `settings/design.ini.append.php` registers `expsite_core` as a design extension, but the extension ships no `design/` directory; the registration is currently a no-op.
- `settings/expsite_core.ini.append.php` and `settings/design.ini.append.php` are missing the closing `*/ ?>` comment marker used by the other INI append files in this project (parsed fine, but inconsistent).
- `expSiteBundleMailHelper::sendMail()` sends HTML-only mail; there is no text/plain alternative part.
- `expSiteBundlePathHelper::getPath()` fetches every path node individually (`eZContentObjectTreeNode::fetch()` per ancestor); deep trees pay one query per level.
- No siteaccess-aware handling in `expSiteBundleRedirectHelper` for relative external redirects beyond prefixing the content root URL alias.
