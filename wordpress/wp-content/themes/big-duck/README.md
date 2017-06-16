### Instructions to run locally:

Clone the repo into a folder where Valet is parked.

```sudo nano /etc/hosts```

Add ```127.0.0.1 big-duck.dev```.

```^X```, then ```Y Enter```

### Good things to know:

```$context['foo']``` declared in a *.php in the template exposes a ```{{ foo }}``` in the *.twig

A template ```template.twig``` implements ```template.php```. You should query variables in the ```.php```, attach them to ```$context``` (e.g. ```$context['stuff'] = "the best stuff"```), which will pass them into the ```.twig``` template (as ```stuff```). ```$templates``` in the ```.php``` file should be an array containing the name of the corresponding ```.twig``` in ```/templates```.

Post types are namespaced with "bd-*".

**NB** You should not have to create ```.php``` files except in the case where you need to load something from the database other than what the default ```.php``` template (e.g. ```single.php```, ```page.php```, ```archive.php```, etc.) loads.

Single-post PHP templates should sit in ```/templates/single-[post_type_slug]```, so ```/templates/single-bd_case_study.php```, say. Notice the confusing mix of kebab- and snake-case.

Post-type "archives" are in ```/templates/archive-[post_type_slug]``` in the _singular_, and combo-cased_as_above.

Post-type "teases" are what get rendered on the archive pages.
