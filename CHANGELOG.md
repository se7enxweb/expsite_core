# Changelog

All notable changes to expsite_core from 1.0.6 on. Earlier releases are described on
<https://github.com/se7enxweb/expsite_core/releases>.

## 1.0.6 (2026-11-14)

- Updated: Pull request #1 (Marco-JAC) is merged: the settings files close their PHP comment (`<?php /* ... */ ?>`), so
  `php -l` passes and a direct request to one prints nothing. The files had already been closed in 1.0.5, so the
  merge changes no settings file; it records the contribution.
- Added: `tests/expsite_core_settings_files_test.php` (SF-01..03, no kernel or database): every settings file parses as PHP,
  ends with `*/ ?>` and prints nothing when included. It fails on 1.0.4, whose settings files left the comment
  open (eZINI read them all the same, so nothing else noticed).

      php extension/expsite_core/tests/expsite_core_settings_files_test.php

- Updated: `ezinfo.php` and `extension.xml` state 1.0.6.

The same check was run over every se7enxweb extension of an Exponential 6 installation: no other settings file
leaves its comment open.
