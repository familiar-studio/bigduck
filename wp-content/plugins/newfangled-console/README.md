# Newfangled Console #

Wordpress plugin.

The Newfangled Console is Newfangled's custom wrapper for the WordPress admin interface. It allows for the admin panel to be accessed via an overlay on the front end of the site. Additionally, it provides enhanced contextual edit icons inline on the front-end of the site, allowing for faster access to various back-end editing and list views for certain types of content and forms on some pages. 

## 2.1 ##

* Override some of the default WordPress workspace css to clean things up
* Fixed bug where mouseing out of the side toolbar would cause it to collapse

## 2.0 ##

* Auto update functionality via Kernl service
* Verify a valid Newfangled Logging connection 

## 1.4.11 ##

* Update readme/release notes

## 1.4.10 ##

* Make sure jquery is always loaded

## 1.4.9 ##

* adjust position of inline edit buttons

## 1.4.8 ##

* adjust inline edit icons, menu

## 1.4.7 ##

* fix inline edit links

## 1.4.6 ##

* use site_url() as the base url for redirects

## 1.4.4 ##

* fix php notices

## 1.4.3 ##

* Removed Newfangled logo from console overlay
* Added new setting 'editlinks_enabled', to auto-insert edit links whenever the_content() is called
* Misc formatting tweaks

## 1.3 ##

* Add bitbucket remote update url

## 1.2 ##

* Misc overlay formatting tweaks
* Update support/help text

## 1.1 ##

* Update formatting of comments, code to be consistent with other Newfangled plugins
* Disabled automatic refresh of page being edited, to prevent conflict with other plugins
* Adjusted loading of console to ensure the includes happen in the right place

## 1.0 ##

* Initial release