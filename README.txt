Moodle module: Verbosity
by Paweł Suwiński <dracono@wp.pl>

The module counts amount of forum posts for each user in chosen forums and put
it into gradebook as a grade. On every update maxgrade value of grade item and
all single grades are set to current maximum so grades could be normalized.  In
percentage grade format the most active forum user get the highest grade
(100%).

Update is processed  as cron job and on every access to instance view page when
the amount of forum posts differs since last update.

Dependence: forum module
