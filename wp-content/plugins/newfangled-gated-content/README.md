# Newfangled Gated Content #

Any post can be made ‘Gated’ simply by checking the ‘Make Gated Content’ checkbox. Posts that are gated will display the main content on its details page, followed by the gated content form. Once this form is filled out, the details page will instead display the new custom field, ‘Protected Content’. 

Once a user completes a gated content form for a particular piece of content, they will be cookied, and not prompted to fill that form out again. Other gated content on the site *will* require a new form submission, however. 

Any post that is flagged as ‘Gated’ will also appear as an available ‘CTA’ in the Smart CTA picker. These behave like any other CTA - if the user has currently completed the gated content for for the selected post, that CTA item will not display.

## 2.0.4 ##

* Focus on the form/response when loading via ajax

## 2.0.3 ##

* added option to disable gravity forms ajax on pre-gated content

## 2.0.2 ##

* Fixed bug where plugin key check could prevent other plugin updates from showing in the wordpress available updates list

## 2.0.1 ##

* Fixed bug where url parameters on pages with ajax-loaded gravity forms could result in dynamic field population not working

## 2.0 ##

* Auto update functionality via Kernl service
* Verify a valid Newfangled Logging connection 
* Updated cookie UID to be more obfuscated

## 1.7.2 ## 

* Fixed bug where gated content templates were not resetting the global $post after rendering

## 1.7.1 ## 

* Added an optional 'Ajax' mode. If the site is using full-page caching, this mode must be enabled to allow for dynamic gated content to work.
* Update default smart cta template to show title, abstract instead of form
* Only show forms with Gated Content ID field in form select dropdown

## 1.6.2 ##

* fix path issue for custom templates in theme

## 1.6.1 ##

* apply formatting to templates, fix inline edit links

## 1.6 ##

* Fix shortcode issue

## 1.5.2 ##

* Remove global combo webinar/gated smart CTA from this plugin
* Move webinar and gated smart cta to nfprofiling plugin
* Latest gated content smart cta
* Clean up comments
* Update readme.txt for plugin upgrader display
* New dynamic smart cta
* Remove widget file


## 1.4 ##

* Added checks to make sure the gated content form (global or post-specific) contains the required hidden field

## 1.3 ##

* Add bitbucket remote update url
* Update help/support policy footert

## 1.2 ##

Lots of new functionality, geared towards making this usable for 'plugin installation' engagements, where we are not nessiarkly building the site. 

* Re-enabled the 'Protected Content' meta field. Unless disabled in the settings, the get_content() hook will now default to showing the post content, followed by the gated form. When the form is completed, the get_content() hook will show the post content followed by the 'Protected Content' field. 
* Added hooks to tie gated content in the Progressive Profiling Smart CTA system. Any post flagged as 'gated' will now be available as a discrete smart cta.
* Added setting 'Use as Smart CTAs'. If not enabled, the gated content items will *not* be used as smart CTAs
* Added setting 'Manually Control Post-Gated Form Content'. When enabled, the auto get_content() hooks will be ignored. Instead, pre- and post- gated content must be controlled manaully in the template
* Added setting 'Default Gated Content Form'. If selected, all posts flagged as gated will use this form. If not selected, each post must select a gated content form. 
* Added a /templates directory, with default templates for the pre-form, post-form, and smart-cta display of gated content, when in 'Auto' mode. These can be moved into the theme directory to be customized. 


## 1.1 ##

* Update formatting of comments, code to be consistent with other Newfangled plugins
* Run gated content through wpautop() function
https://bitbucket.org/newfangled_web/core-wordpress/pull-requests/4/run-gated-content-through-wpautop-function/diff

## 1.0 ##

* Initial release