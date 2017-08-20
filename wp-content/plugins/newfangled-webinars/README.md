# Newfangled Webinars #

Wordpress plugin.

Adds webinar functionality to posts. Webinars are either upcoming, and integrate a registration
via Act-On, or past, and requre a form submission to view. 


## 2.0.9 ##

* Fix version number issue

## 2.0.8 ##

* Adjustments to the webinar metabox styling, to correct issues when jQuery UI is installed in the WP Admin interface.

## 2.0.7 ##

* Focus on the form/response when loading via ajax

## 2.0.6 ##

* Removed leftover PHP Shortcode

## 2.0.5 ##

* Added missing kernl.us upgrade functionality

## 2.0.4 ##

* Added option to disable gravity forms ajax on pre-gated content

## 2.0.3 ##

* Fixed bug where plugin key check could prevent other plugin updates from showing in the wordpress available updates list

## 2.0.2 ##

* Fixed bug where url parameters on pages with ajax-loaded gravity forms could result in dynamic field population not working

## 2.0.1 ##

* Fixed bug where Act-On ID was not being passed dynamically to the 
upcoming webinar registration form

## 2.0 ##

* Auto update functionality via Kernl service
* Verify a valid Newfangled Logging connection 
* Updated cookie UID to be more obfuscated

## 1.3.3 ##

* Updated readme/release notes
* Updated is_webinar_upcoming() to load the WordPress timezone setting, defaulting to EST. PHP timezone is restored after the lookup
* Updated the Webinar post meta box to show the Wordpress timezone setting that the date/time will be evaluated against
* Updated the upcoming webinar pre-form template to show the webinar full date/time, shown in the correct timezone as indicated by the Wordpress timezone setting.

## 1.3.2 ##

* Updated default templates. 

## 1.3.2 ##

* Hide upcoming acton form id if no acton integration being used

## 1.3 ##

* Added an optional 'Ajax' mode. If the site is using full-page caching, this mode must be enabled to allow for dynamic webinars to work.
* The Smart CTA template will now show the Webinar title/image/abtract/link, rather than the registration form. 
* New 'Access Code' functionality

## 1.2.2 ##

* Fix path issue for custom templates in theme

## 1.2.1 ##

* Apply formatting to templates, fix inline edit links

## 1.2 ##

* Fixed shorttag issue
* Fixed bug where transcript text was not being propery wp decoded

## 1.1 ##

* Added more control/fields to the webinar edit box and template
* Latest webinar smart cta
* fix missing close i tag
* remove widget file

## 1.0 ##

* Initial release