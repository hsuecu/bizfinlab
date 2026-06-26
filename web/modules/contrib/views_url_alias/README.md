# Views URL alias

The 'Views URL alias' module allows views to be filtered by path aliases.

This module is useful if your website uses hierarchical paths. It allows you to
filter and sort a view by URL aliases. When combined with the
Views bulk operation [(VBO) module](http://drupal.org/project/views_bulk_operations)
you can apply operations to a specific section of your website based on a
URL alias.

All content entities aliases are supported.


## Table of contents

- Requirements
- Installation
- Configuration
- Notes
- Todo
- Authors/Maintainers


## Requirements

This module requires no modules outside of Drupal core.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Enable the module at Administration > Extend.
2. On the /admin/config/search/path page, click "Add alias" and follow the
   instructions.
3. Go to the view you want to filter by alias, go to Advanced > Relationships
   and click "{type} URL Alias" (you can also check the "Require this relationship" checkbox).
4. Add "Filter criteria" with "URL Alias" and configure it like a regular
   filter according to your URL Alias filtering needs.


## Notes

- This module creates and maintains separate 'views_url_alias' table
  to provide clean and fast joins between the primary {type} table and its url
  aliases.


## Todo

- Support multiple path alias per content entity


## Authors/Maintainers

- [Dima Storozhuk](https://www.drupal.org/u/dstorozhuk)
- [Kostia Bohach](https://www.drupal.org/u/_shy)
- [Erik Webb](https://www.drupal.org/u/erikwebb)
- [Jacob Rockowitz](https://www.drupal.org/u/jrockowitz)
- [Kyah Rindlisbacher](https://www.drupal.org/u/l-four)
- [Pradeep Venugopal](https://www.drupal.org/u/venugopp)
- [Rachel Lawson](https://www.drupal.org/u/rachel_norfolk)
