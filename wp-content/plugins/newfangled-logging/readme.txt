=== Newfangled Logging ===
Requires at least: 4.0
Tested up to: 4.7.1
Stable tag: 4.7.1

A simple wrapper for Raven/Sentry error logging. Used to manually send specific error messages pertaining to Newfangled plugins to Sentry.

== Description ==

A simple wrapper for Raven/Sentry error logging. Used to manually send specific error messages pertaining to Newfangled plugins to Sentry.

```
#!php

// Access the plugin from your template, or another plugin
global $nflogging;

//  Is the plugin available?
if (!is_object($nflogging)) {
    return;
}

// Log the error
$nflogging->logError( 'Something went terribly wrong' );
```

== Changelog ==

### 2.0.1 ###

* Bug Fixes

### 2.0 ###

* Added logging API check, used by all other Newfangled plugins
* Auto update functionality via Kernl service

### 1.2 ###
* Add bitbucket remote update url

### 1.1 ###
* Updated help/support text
* Added enable/disable checkbox

### 1.0 ###
* Initial release