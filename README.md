**Original Out-of-the-box version**
Original Core Simple Stats (data collection, Dashboard and separate Hit Counter)

Original Simple Stats functionality:

- Only collects daily results fixed at 7 days retention.
- Hit counter only shows the current days hit rate.

Issues with original:

- The count goes up on each page refresh with no restriction, so F5 can artificially increase stats faster than it should.
- Collection of data fixed at 7 days needing a code change to alter.

**New enhanced version**
Simple Stats Plus (data collection, Dashboard and separate Hit Counter in one bundle)

Recommendation:
This plugin uses the PHP-intl module to support international formats. If you have an installation of PHP that does not have this installed, eg Centos you may need to need to refer to either of the following to install:
- https://www.php.net/manual/en/intl.installation.php
- https://stackoverflow.com/questions/14679990/how-do-i-install-php-intl-extension-on-centos

Fixed:
- A session for each page is started preventing constant page refreshing artificially increasing the count
- Collection Periods are all parametrised and configurable in settings
- Excluded Type=autosave from gathered array display list or updating stats

New Functionality:
- Enhanced – Daily stats retention now has variable data retention(able to independently turn off)
- New –  Weekly stats captured for x weeks (configurable, including off)
- New – Monthly stats captured for x months  (configurable, including off)
- New – Page session with a configurable timeout
- New – Running total of all page hits captured (able to set specific value to get you started).
- All existing Languages expanded for all changes plus some added
- Dashboard - Can change the displayed chart type within plugin settings
- New – Dashboard now also shows total page count
- Total Page count can be set via a combination of admin settings and visiting a web page to trigger.
- Enhanced - Hit Counter: sidebar independently displays all current collection data as configured in settings
- New - Individual rolling page count with page count chart and table on seperate tabs to content tabs.
- New - Capture individual page display count and display Top x as chart and table.
- New - Keep rolling 40 day backups of daily count array file.

Not done yet, but want to do:
- Display line charts within Tab Navigation without having to change settings.
