# iThemes Security - Log Logins

Adds an entry into the iThemes Security (free) logs (notices), for each successful user login.

## Installation

```bash
# 1. Get it ready (to use a repo outside of packagist)
composer config repositories.pvtl-itsec-login-logs git https://github.com/pvtl/wordpress-itsec-login-logs-plugin

# 2. Install the Plugin - we want all updates from this major version (while non-breaking)
composer require "pvtl/pvtl-itsec-login-logs:~1.0"
```

## Versioning

_Do not manually create tags_.

Versioning comprises of 2 things:

- Wordpress plugin version
    - The version number used by Wordpress on the plugins screen (and various other peices of functionality to track the version number)
    - Controlled in `./itsec-login-logs.php` by `* Version: x.x.x` (line 10)
- Composer dependency version
    - The version Composer uses to know which version of the plugin to install
    - Controlled by Git tags

Versioning for this plugin is automated using a Github Action (`./.github/workflows/version-update.yml`).
To release a new version, simply change the `* Version: x.x.x` (line 10) in `./itsec-login-logs.php` - the Github Action will take care of the rest.
