
=== Progressive Profiling ===
Requires at least: 4.0
Tested up to: 4.7.2
Stable tag: 4.7.2

Progressive profiling management. Uses plugins to interface with individual form sources (Gravity Forms, etc).

== Description ==

This plugin provides advanced ‘progressive profiling’ functionality for Gravity Forms.
Typically, it will not be necessary to modify these mappings. If, however, you add a new field to an existing form, and wish to capture that value in Act-On, you should also add that new field to the Act-On form, and then update the mapping to tie them together. Likewise, if you add a completely new form to the site, you’ll need to create a corresponding new form in Act-On, and create a new feed, mapping each field of the new form.

The master list of Progressive Profile fields is managed in WordPress, under Settings->Newfangled Progressive Profiling. This is a master list that applies to all forms on the site. For instance, if the field ‘Company’ is designated as a progressive profile field, all forms that have the field ‘Company’ will treat it as a progressive profile field, making it required if all the other fields leading up to it have been supplied by the user. This means that when adding a new form, if you simply duplicate an existing form that already has all the profile fields, no further action is needed - they’ll inherit the master profile list.

It is important to remember that different forms, on different pages, will look different to users depending on which fields they’ve previously filled out. As such, it is important to test and design the form fields to account for all of these various scenarios. The progressive profiling system is cookie-based, so clearing your cookie will ‘reset’ you back to the beginning, to facilitate testing. 

= Smart CTAs = 

Smart CTAs provide a way to designate an area of the site that shows only forms that the user has not already filled out. For instance, in the footer, you might want to show the Newsletter Signup form, until the user submits it, at which point on future pages they should see the Talk To Us in the footer, until they submit that. 

This is done by using a widget, or via the [smartcta] shortcode. In the example above, assuming the IDs for the Request A Quote and Blog Signup forms are 2 and 4, you would use the following shortcode:
	
[smartcta id=”2,4”]

Note that if you’ve previously completed those two forms, nothing will show up, so you should clear your cookies or use a different browser to preview this page. 

Smart CTAs can also be placed in any Widget Area by simply dragging the Smart CTA widget, and selecting the forms you wish to appear. Drag-and-drop the forms in the widget to control the order in which they will appear.


== Changelog ==

### 2.0.8 ###

* changes to the Custom Sidebar CTAs module

### 2.0.7 ###

* fix bug where ajax-loaded smart cta could fail to render depending on the calling page url

### 2.0.6 ###

* fix bug where OTHER field is not correctly prefilled/hidden

### 2.0.5 ###

* Added option to disable gravity forms ajax on Smart CTAs

### 2.0.4 ###

* Fixed bug where plugin key check could prevent other plugin updates from showing in the wordpress available updates list

### 2.0.3 ### 

* Added an option to the gravityforms module, to always preload the core GF scripts and styles in the header. Since the smartcta could contain a gravity form, this can overcome display issues if those resources are not loaded.

### 2.0.2 ###

* Fixed bug where url parameters on pages with ajax-loaded gravity forms could result in dynamic field population not working
* Fixed bug where previously submitted checkbox progressive profil fields would not be preset in gravity forms loaded via ajax
* Fixed bug where the SmartCTA could log a console error if jQuery Sortable library is not loaded by the wordpress admin


### 2.0.1 ###

* Fixed bug where tab indexing was not correct when loading forms via ajax

### 2.0 ###
* Improved upgrade/licencing control via new Licence Key functionality

### 1.9.4 ###
* Fixed bug in 'Ajax-Mode' where gravityforms javascript was not being reparsed after an ajax form refresh

### 1.9.3 ###
* Remove the isAdmin ignore from the field preprocessors, as it was interfering with GravityViews functionality
* Fixed bug where progressive profiling fields were not pre-populating on the first page load after the visitor submits their first form

### 1.9.2 ###
* Ignore .DS_Store from module list

### 1.9 ###
* Added an optional 'Ajax' mode. If the site is using full-page caching, this mode must be enabled to allow for progressive profiling and smart CTAs to work.

### 1.8.4 ###
* remove debug code


### 1.8.3 ###
* allow for gravityform module template, fix post overwriting bug in smart cta

### 1.8.2 ###
* Change hide method to css base

### 1.8.1 ###
* Make sure shortcode forms include js/css resources

### 1.8 ###
* Fixed shortcode issue
* Fixed dbDelta statement

### 1.7 ###
* Reworked the way that profile_ids are encoded and stored for the user, to prevent possible data corruption. Note that this upgrade *will* reset all progressive profiling for site visitors.
* Better settings page help notes
* Fix bug where getemail() was not returning a value
Tags

### 1.6 ###
* New progressive profiling option - 'Automatically hide progressive profiling fields'. If checked, previously completed or upcoming fields will be hidden on all forms. If unchecked, the css classes 'progressiveprofiling-notrequired' and 'progressiveprofiling-prefilled' can be used to manually control this behavior. 

### 1.5.2 ###
* Added smart cta callback functions
* Fixed php notices
* Comment cleanup, fix bugs with webinar+gated smart cta
* Searchable widget interface

### 1.4 ###
* Added bitbucket remote update url

### 1.3 ###
* Added custom 'Insert Smart CTA' button for sites using the 'Gravity Forms' module, to auto-insert the Smart CTA shortcode into a page's content area
* Added hooks to allow other plugins to define their own smart ctas
* Cleaned up the formatting of the checkboxes on the Progressive Profiling settings screen

### 1.2 ###
* Add check for multibyte support when producing safe css selector strings - fallback to vanila preg_replace if not

### 1.1 ###
* Update formatting of comments, code to be consistent with other Newfangled plugins
* Many improvements to the way checkbox groups, radio buttons, and conditional fields are handled as progressive profiling fields
* Allow the number of fields per profile step to be defined as a plugin setting https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/10/make-profiling-fields-visible-editable/diff
* Updating Gravity Forms WP progressive profiling to check for content in the user profile before marking as pre-filled. https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/2/updating-gravity-forms-wp-progressive/diff
* Add check for module name https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/11/add-check-for-module-name/diff
* Disable short tags https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/9/replace-php-short-tags-with-full-tags/diff
* Fix bug with stored smartcta forms. When submissions were being stored, they were previously relying on a form input that is not there. This now stores submitted UIDs by form id. https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/8/fix-bug-with-stored-smartcta-forms/diff
* Fix incorrect call to smartcta shortcode function https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/6/fix-incorrect-call-to-smartcta-shortcode/diff
* Allow title display for smart cta shortcode https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/7/allow-title-display-for-smart-cta/diff


### 1.0 ###
* Initial release