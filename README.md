Enrol level
===========

Enrol users in courses when a certain level from [Level up!](https://moodle.org/plugins/block_xp) is attained.

Requirements
------------

- Level Up! 3.1 or greater.
- Moodle 2.7 or greater.

How to use
----------

Navigate to a course, and go the enrolment methods. The link to the enrolment methods can be found in the administration block under _Course administation > Users_ and sometimes in the participants page in the _cog menu_.

From that page, add the method _Level enrolment_ by selecting it in the dropdown menu, and configure it as per your requirements.

When are users enrolled?
------------------------

Enrolment occurs two different ways:

- Automatically, when a user reaches the required level.
- Manually, when triggered from the enrolment methods screen.

The manual method is useful to enrol all users who already attained the level. Note that this requires cron to be enabled.

Installation
------------

### Zip upload

If you have configured Moodle to allow plugin installation from the user interface, and you received a zip of the plugin, follow the following steps. If not, refer to the manual process.

1. Visit the _Install plugins_ admin page (Site administration > Plugins > Install plugins)
2. Drag & drop the plugin in the _Zip package_ area
3. Click _Install plugin from the ZIP file_ and follow the process

That's it!

### Manual process

1. Place the content of this plugin in the folder `enrol/xp`.
2. Visit your admin's _Notifications_ page (Site administration > Notifications)
3. Follow the upgrade process

That's it!

License
-------

Licensed under the [GNU GPL License](http://www.gnu.org/copyleft/gpl.html).

