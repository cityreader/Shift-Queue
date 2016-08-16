CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------
Shift queue is extended from Drupal system queue with two new features:
 * Disable the processing of a particular queue
   This feature allows to stop the processing a queue.

 * Set maximum failed processing attempts
   Originally in Drupal system queue, when a queue item is processed with
   exception, it will be processed again until it is processed completely.
   This feature allows to retry in a limited configurable times and if it still
   get problem, this item will be moved to a separated queue called:
   'ORIGINAL_QUEUE_NAME:failed'.


REQUIREMENTS
------------
This module requires the following modules:
 * Queue functions in Drupal core (https://drupal.org/project/drupal)


INSTALLATION
------------
 * Install as you would normally install a contributed Drupal module. See:
   https://drupal.org/documentation/install/modules-themes/modules-7
   for further information.


CONFIGURATION
-------------
* Set queue class in variable:

  - For example we want to use Braintree webhooks notification queue to use
    this class.

    variable_set('queue_class_braintree_webhooks_queue', 'ShiftQueue');

* Change shift queue settings in page 'admin/config/system/shift_queue'

  - Enable queue processing

    Enable processing of a queue.

  - Set maximum failed processing attempts

    It defaults to 2.


MAINTAINERS
-----------
Current maintainers:
* Eric Chen (eric.chenchao / cityreader) - https://www.drupal.org/user/265729

