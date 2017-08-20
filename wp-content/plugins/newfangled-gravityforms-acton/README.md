# Act-On/Gravity Forms Integration #

This plugin provides the integration and admin management of Gravity Forms->Act-On integration. 

Every custom form on the website, built using the Gravity Forms plugin, is mapped to a corresponding form in Act-On. With this mapping in place, every form that is submitted on the website will appear in Act-On as a corresponding form submission. Act-On can then push this information into your Salesforce account, adding a new lead or updating existing leads or contacts.

This mapping to Act-On is controlled in WordPress, under Forms->Act-On Integration. To provide the most flexibility, each form has its own ‘feed’. Typically, it will not be necessary to modify these mappings. If, however, you add a new field to an existing form, and wish to capture that value in Act-On, you should also add that new field to the Act-On form, and then update the mapping to tie them together. Likewise, if you add a completely new form to the site, you’ll need to create a corresponding new form in Act-On, and create a new feed, mapping each field of the new form.

## 2.0.4 ##

* Fixed bug where plugin key check could prevent other plugin updates from showing in the wordpress available updates list.

## 2.0.3 ##

* Removed loading the act-on API from the export_feed function on form submit. It was unused, and added a delay to the form submission experience.

## 2.0.2 ##

* Update Act-On tracking code to use the new format beacon script
* New getActOnTrackingIDJavascript function, to be consumed by the Newfangled Tracking plugin to pass the user's Act-On cookie value
* Replace the file_get_contents with a CURL call, in the GetFields() API call

## 2.0.1 ##

* When defining an act-on form ID dynamically (as in a webinar), also look for the value 'Act-On ID'
* Pass values to Act-On even if the form is flagged as 'spam'

## 2.0 ##

* Auto update functionality via Kernl service
* Verify a valid Newfangled Logging connection 

## 1.4 ##

* Fixed bug where dynamic Act-On Form ID field was not being recognized and passed to Act-On on form submission

## 1.3 ##

* Update support, help text
* Add bitbucket remote update url

## 1.2 ##

* Add integration with newfangled_logging plugin, to record any failures in pushing form submissions to Act-On

## 1.1 ##

* Fixed bug where Act-On credentials were not being cached properly, leading to occasional 'too many login attempts' when editing act-on/gravityform feeds
* Changed all caching to use wordpress native 'transients'

## 1.0 ##

Initial Release