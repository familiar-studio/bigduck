=== Newfangled Tracking ===
Requires at least: 4.0
Tested up to: 4.7.1
Stable tag: 4.7.1

Integrate with Newfangled's visitor tracking system

== Description ==

Integrate with Newfangled's visitor tracking system

== Changelog ==

### 2.1.3 ###

* Fixed bug where plugin key check could prevent other plugin updates from showing in the wordpress available updates list

### 2.1.2 ###

* Reduce delayed tracking callout timeout

### 2.1.1 ###

* Rename menu item

### 2.1 ###

* Enable Marketing Dashboard WP Admin menu item
* Pass UTM Campaign data to pagehit API calls
* New mechanism to pass the user's 3rd-party tracking cookie value (currently, only Act-On) to the tracking system to reconcile future unknown visits

### 2.0 ###

* Auto update functionality via Kernl service
* Verify a valid Newfangled Logging connection 

### 1.4.3 ###
* Update readme/release notes

### 1.4.2 ###
* Update iframe size, point release
* Make sure jquery is always loaded


### 1.4 ###
* Added an optional 'Ajax' mode. If the site is using full-page caching, this mode must be enabled to allow for page tracking to work.

### 1.3.3 ###
* Restore correct referrer, dashboard instead of menu pages
* Convert tabs to spaces
* Clean up comments
* Update readme.txt for plugin upgrader display
* Changes to plugin folder name, repo
* Bitbucket remote update url
* Changes to settings page, help text

### 1.3 ###
* Added Tracking Dashboard admin panel

### 1.2 ###
* Add integration with newfangled_logging plugin, to record any failures in pushing conversions or content changes to the Insight Engine.

### 1.1 ###
* Update formatting of comments, code to be consistent with other Newfangled plugins
* Overhauled to work with the new Insight Engine tracking API. New settings for API key and Insight engine endpoint. New functionality to push content Draft and Add activity to the Insight Engine API. 
* Disable short tags https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/9/replace-php-short-tags-with-full-tags/diff

### 1.0 ###
* Initial release
