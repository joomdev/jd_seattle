<?php die();?>
Akeeba Backup 6.1.1
================================================================================
+ Added support for new Amazon S3 regions: Canada, Mumbai, Seoul, Osaka-Local, Ningxia, London, Paris
+ OneDrive for Business support
+ OVH cloud storage support
+ OpenStack Swift support
# [LOW] DirectFTP and Upload to FTP create folders with 0744 instead of 0755 permissions

Akeeba Backup 6.1.0
================================================================================
+ Support for Amazon S3 OneZone-IA (single zone, infrequent access) storage class
+ Revamped Site Transfer Wizard, with more options to improve compatibility with more servers
# [HIGH] Links pointing outside open_basedir restrictions cause a PHP Fatal error, halting the backup
# [MEDIUM] Installer (ANGIE) language files not included in the backup
# [MEDIUM] Default backup file permissions should be 0644, not 0755
# [MEDIUM] Fixed folder scanning when a file is inaccessible due to open_basedir restrictions
# [LOW] The View Log link displayed after backup is broken when the backup completes in a single page load
# [LOW] The Site Transfer Wizard uses the wrong color labels
# [LOW] The Site Transfer Wizard interface works erratically

Akeeba Backup 6.0.1
================================================================================
+ Warn the user if either FOF or FEF is not installed
+ While importing backup archives, they are now sorted alphabetically
# [HIGH] Site Transfer Wizard: browser-specific BUTTON element behavior prevents using this feature in Firefox
# [LOW] Inadvertent space between domain and path in the Schedule page

Akeeba Backup 6.0.0
================================================================================
# [LOW] Fixed warning message during the installation due to missing folders

Akeeba Backup 6.0.0.b1
================================================================================
+ Rewritten interface using our brand new Akeeba Frontend Framework
+ Preliminary Joomla! 4.0 support
# [MEDIUM] Fixed connection issue to the database when certain hostnames are used (named pipes)
# [LOW] The kicktemp folder and the kickstart.transfer.php file were not removed after Site Transfer Wizard had finished
# [LOW] The Transfer Wizard may select a chunk size which is too big for the target server
# [LOW] Using the Site Transfer Wizard may result in a "wordpress" folder being left behind on some servers with a broken FTP server (technically a bug with your FTP server; we had to remove a minor feature to work around it)
# [LOW] Add-on ANGIE language files would not be included in the backup archive
# [LOW] Leftover empty file after the end of the Configuration Wizard run

Akeeba Backup 5.6.4
================================================================================
# [LOW] Add-on ANGIE language files would not be included in the backup archive

Akeeba Backup 5.6.3
================================================================================
! Some JavaScript files had a zero size in the 5.6.2 package

Akeeba Backup 5.6.1 & 5.6.2
================================================================================
! Missing language strings (fixed in 5.6.2)
+ Display info about exceptions and PHP 7+ fatal errors in the backend
+ BackBlaze B2 integration (Pro only)
~ PHP 7.2 compatibility: renaming Object class to BaseObject
~ Update Dropbox API with the new "_v2" endpoints where applicable
~ JSON API stepBackup: now enforcing the check for the MANDATORY parameter "backupid"
# [MEDIUM] Backing up to JPS with a static salt using the JSON API would always fail
# [MEDIUM] "Apply to all" filter button does not work due to a Javascript issue
# [LOW] Core version: some post-processing options were displayed even though the code to make them work was not present.
# [LOW] The ANGIE Password option in Configuration and Backup Now pages didn't show up in the Core release
# [LOW] Calendar fields did not display the month due to a Joomla! 3.7+ CSS-related bug
# [LOW] Programmatically triggering mouse events could fail in some browsers, e.g. newer versions of Firefox
# [LOW] WebDav post-processing: Fixed issue with hosts without a valid certificate file and SSL connections
# [LOW] Backup On Update was available on Pro version only. Now it's available in the Core version, too.

Akeeba Backup 5.6.0
================================================================================
+ Added warning if HHVM instead of PHP is used
+ Added variables for timezone information: [TIME_TZ], [TZ], [TZ_RAW], [GMT_OFFSET]
+ Backup Timezone: force all date / time variables to be expressed in a fixed timezone during backup (gh-626)
+ Clicking on "Reload update information" will fix Joomla! erroneously reporting an update is available when you have the latest version installed
+ Display the Secret Word unencrypted in the component's Options page (required for you to easily set up third party services)
~ Updates now use HTTPS URLs for both the XML update stream and the package download itself
# [LOW] Save & New button in the Configuration page didn't work correctly
# [LOW] Editing RegEx filters creates a new entry, doesn't edit in place (gh-623)
# [LOW] Repeated messages in the JavaScript console after editing a filter in Tabular or RegEx views (gh-625)

Akeeba Backup 5.5.2
================================================================================
! [SECURITY] Settings encryption key was neither cryptographically random or big enough. Now changed to 64 crypto-safe random bytes.
! [SECURITY] Secret Word for front-end and JSON backups is now stored encrypted in the database (as long as settings encryption in the application's Options is NOT disabled and your server supports encryption).
! [SECURITY] Improved internal authentication in restore.php makes brute forcing the restoration takeover a few dozen orders of magnitude harder.
- [SECURITY ADVICE] ANGIE will no longer lock its session to prevent information leaks. Please always use the ANGIE Password feature.
~ [SECURITY] akeeba-altbackup.php: verify SSL certificates by default. Use --no-verify command line option to revert to the old behavior.
~ Work around broken MijoShop plugin causing an error in Joomla's backend when the System - Backup on Update plugin is enabled.
# [HIGH] Editing two or more Multiple Databases definitions consecutively would overwrite all of them with the settings of the last definition saved
# [HIGH] Disabling decryption can lead to loss of current settings
# [LOW] "_QQ_" shown during restoration instead of double quotes
# [LOW] Removed MB label from count quota
# [LOW] ANGIE: restoring sites served by a server cluster could result in "random" errors due to session handling being tied to the server IP

Akeeba Backup 5.5.1
================================================================================
! The System - Backup on Update plugin may cause an error accessing the backend on some sites

Akeeba Backup 5.5.0
================================================================================
+ Prevent simultaneous use of ANGIE (restoration script) from two or more people / browsers
+ Workaround for Joomla! bug "Sometimes files are not copied on update"
+ Alphabetical sorting of engines and installation scripts in the Configuration page
+ Backup on Update: Show the status in the backend status bar (footer), with the ability to quickly toggle it off
+ Support for Google Storage native JSON API
~ ANGIE for Joomla (restoration): Use alternate method to read the site's configuration, preventing PHP errors from getting in the way
# [HIGH] Cannot change database prefix on restoration if the backup was taken with No Dependency Tracking enabled
# [MEDIUM] Double slashes in the WebDAV path cause 0 byte uploads on some servers
# [MEDIUM] Version information not loaded correctly (thanks Joe F.!)
# [LOW] Notice thrown converting memory_limit to bytes under PHP 7.1
# [LOW] Workaround for Joomla! bug 16147 (https://github.com/joomla/joomla-cms/issues/16147) - Cannot access component after installation when cache is enabled

Akeeba Backup 5.4.0
================================================================================
# [HIGH] Resuming after error was broken when using the file storage for temporary files (default)
# [MEDIUM] Errors when reading the backup engine's state were not reported, causing the backup to seem like it runs forever
# [MEDIUM] Database storage wouldn't report the lack of stored engine state, potentially causing forever stuck backups
# [MEDIUM] Blank page if you delete all backup profiles from the database (a default backup profile could not be created in this case)
# [MEDIUM] Errors always result in the resume pane being shown, no matter what your settings are
# [LOW] The Warnings pane was always displayed following resuming from a backup error
# [LOW] The How to Restore modal would get in the way unless you chose to not be reminded again

Akeeba Backup 5.4.0.b1
================================================================================
! Yet another BIG Joomla! 3.7.0 bug throws an exception when using the CLI backup under some circumstances.
! Yet another BIG Joomla! 3.7.0 bug kills the front-end and remote backup when you are using the System - Page Cache plugin.
- Removing the automatic update CLI script. Joomla! 3.7.0 can no longer execute extension installation under a CLI application.
# [HIGH] PHP Fatal Error if the row batch size leads to an amount of data that exceeds free PHP memory
# [HIGH] Large database records can cause an infinitely growing runaway backup or, if you're lucky, a backup crash due to exceeding PHP time limits.
# [MEDIUM] Database hostname localhost:3306 leads to connection error under some circumstances. Note that this hostname is wrong: you should use localhost without a port instead!
# [MEDIUM] Logic error leads to leftover temporary database dump files in the backup output directory under some circumstances
# [LOW] The wrong message is shown by ANGIE when performing an integrated restoration through Akeeba Backup for Joomla!
# [LOW] Wouldn't show up in the Joomla! 3.7 new backend menu type selection dialog
# [LOW] FTP/SFTP over cURL uploads would fail if the remote directory couldn't be created

Akeeba Backup 5.3.4
================================================================================
# [MEDIUM] Integrated restoration leads to an error message about the file extension being wrong

Akeeba Backup 5.3.3
================================================================================
! The workaround to Joomla! 3.7's date bugs could cause a blank / error page (with an error about double timezone) under some circumstances.
! Joomla! 3.7.0 broke backwards compatibility again, making CLI scripts fail.
! Joomla! 3.7.0 broke the JDate package, effectively ignoring timezones, causing grave errors in date / time calculations and display
~ Workaround for badly configured servers which print out notices before we have the chance to set the error reporting
~ Control Panel: Display the backup date/time in the user's timezone
~ Default backup description in the component backend includes the date / time in the user's local timezone instead of GMT
~ Date and time shown in Site Transfer Wizard is in the user's local timezone instead of GMT
# [LOW] Joomla! 3.7 added a fixed width to specific button classes in the toolbar, breaking the page layout

Akeeba Backup 5.3.2
================================================================================
! Joomla! 3.7.0 broke backwards compatibility again, making CLI scripts fail.
! Joomla! 3.7.0 broke the JDate package, effectively ignoring timezones, causing grave errors in date / time calculations and display
~ Workaround for badly configured servers which print out notices before we have the chance to set the error reporting
~ Control Panel: Display the backup date/time in the user's timezone
~ Default backup description in the component backend includes the date / time in the user's local timezone instead of GMT
~ Date and time shown in Site Transfer Wizard is in the user's local timezone instead of GMT
# [LOW] Joomla! 3.7 added a fixed width to specific button classes in the toolbar, breaking the page layout

Akeeba Backup 5.3.1
================================================================================
# [HIGH] Integrated restoration: clicking Run the Installer does nothing; you can still run the installer manually
# [HIGH] Archive integrity and restoration of JPS archives fails (the archive is valid, it's the extraction script that's the problem).
# [HIGH] The "Replace main .htaccess with default" feature, enabled by default, causes the restoration to fail.
# [LOW] Restoration was not removing password protection from the administrator folder even if requested to

Akeeba Backup 5.3.0
================================================================================
! SECURITY: Workaround for MySQL security issue CVE-2016-5483 (https://blog.tarq.io/cve-2016-5483-backdooring-mysqldump-backups/) affecting SQL-only backups. This is a security issue in MySQL itself, not our backup engine. Full site backups / restoration were NOT affected.
+ You can use multiple PushBullet tokens to notify multiple accounts
# [MEDIUM] gh-619 "Invalid upload ID specified" when trying to re-upload backup to remote storage from the Manage Backups page
# [HIGH] PHP's memory_limit given in bytes (e.g. 134217728 instead of 128M) prevent the backup from running
# [LOW] How To Restore modal in Manage Backups appearing halfway outside the page if your computer is a bit too fast

Akeeba Backup 5.3.0.b1
================================================================================
+ Add support for Canada (Montreal) Amazon S3 region
+ Add support for EU (London) Amazon S3 region
+ Add support for Joomla! 3.7's new administrator menu manager
+ Support for JPS format v2.0 with improved password security
+ Hide action icons based on the user's permissions
~ Permissions are now more reasonably assigned to different views
~ Now using the Reverse Engineering database dump engine when a Native database dump engine is not available (PostgreSQL, Microsoft SQL Server, SQLite)
# [MEDIUM] The web.config file introduced in Akeeba Backup 3.3 was removed from the backup output directory
# [MEDIUM] Infinite recursion if the current profile doesn't exist
# [MEDIUM] Views defined against fully qualified tables (i.e. including the database name) could not be restored on a database with a different name
# [LOW] The unit of measurement was not displayed in the Configuration page

Akeeba Backup 5.2.5
================================================================================
# [HIGH] Trailing slashes in the Directory name would cause donwloading a backup back from Dropbox to fail
+ Alternative FTP post-processing engine and DirectFTP engine using cURL providing better compatibility with misconfigured and broken FTP servers
+ Alternative SFTP post-processing engine and DirectSFTP engine using cURL providing compatibility with a wide range of servers
~ Anticipate and report database errors in more places while backing up MySQL databases
~ Do not show the JPS password field in the Restoration page when not restoring JPS archives
# [HIGH] Site Transfer Wizard does not work with single part backup archives
# [HIGH] Outdated, end-of-life PHP 5.4.4 in old Debian distributions has a MAJOR bug resulting in file data not being backed up (zero length files). We've rewritten our code to work around the bug in this OLD, OUTDATED, END-OF-LIFE AND INSECURE version of PHP. PLEASE UPGRADE YOUR SERVERS. OLD PHP VERSIONS ARE **DANGEROUS**!!!
# [MEDIUM] Dumping VIEW definers in newer MySQL versions can cause restoration issues when restoring to a new host
# [MEDIUM] Dropbox: error while fetching the archive back from the server
# [MEDIUM] Error restoring procedures, functions or triggers originally defined with inline MySQL comments
# [LOW] Folders not added to archive when both their subdirectories and all their files are filtered.
# [LOW] ALICE would display many false positives in the old backups detection step
# [LOW] The quickicon plugin was sometimes not sure which Akeeba Backup version is installed on your site
# [LOW] The "No Installer" option was accidentally removed

Akeeba Backup 5.2.4
================================================================================
+ ALICE: Added check about old backups being included in the backup after changing your backup output directory
+ JSON API: export and import a profile's configuration
# [HIGH] Changes in Joomla 3.6.3 and 3.6.4 regarding Two Factor Authentication setup handling could lead to disabling TFA when restoring a site
# [HIGH] Javascript error when using on sites with the sequence "src" in their domain name
# [HIGH] Site Transfer Wizard fails on sites with too much memory or very fast connection speeds to the target site
# [MEDIUM] In several instances there was a typo declaring 1Mb = 1048756 bytes instead of the correct 1048576 bytes (1024 tiems 1024). This threw off some size calculations which, in extreme cases, could lead to backup failure.
# [MEDIUM] Obsolete records quota was applied to all backup records, not just the ones from the currently active backup profile
# [MEDIUM] Obsolete records quota did not delete the associated log file when removing an obsolete backup record
# [MEDIUM] The backup quickicon plugin would always deactivate itself upon first use
# [MEDIUM] Infinite loop creating part size in rare cases where the space left in the part is one byte or less
# [LOW] Fixed ordering in Manage Backups page
# [LOW] Fixed removing One Click backup flag

Akeeba Backup 5.2.3
================================================================================
+ ANGIE: Prevent direct web access to the installation/sql directory
~ PHP 5.6.3 (and possibly other old 5.6 versions) are buggy. We rearranged the order of some code to work around these PHP bugs.

Akeeba Backup 5.2.2
================================================================================
! The ZIP archiver was not working properly

Akeeba Backup 5.2.1
================================================================================
! PHP 5.4 compatibility (now working around a PHP bug which has been fixed years ago in PHP 5.5 and later)

Akeeba Backup 5.2.0
================================================================================
! mcrypt is deprecated in PHP 7.1. Replacing it with OpenSSL.
! Missing files from the backup on some servers, especially in CLI mode
+ Added warning if CloudFlare Rocket Loader is enabled on the site
+ Added warning if database updates are stuck due to table corruption
+ ALICE raw output now is always in English
+ Support the newer Microsoft Azure API version 2015-04-05
+ Support uploading files larger than 64Mb to Microsoft Azure
+ You can now choose whether to display GMT or local time in the Manage Backups page
+ Sort the log files from newest to oldest in the View Log page (based on the backup ID)
+ View Log after successful backup now takes you to this backup's log file, not the generic View Log page
# [MEDIUM] Cannot download archives from S3 to browser when using the Amazon S3 v4 API
# [MEDIUM] gh-601 CloudFlare Rocket Loader is broken and kills the Javascript on your site, causing Akeeba Backup to fail
# [LOW] Front-end URL without a view and secrety key should return a plain text 403 error, not a Joomla 404 error page.
# [LOW] Reverse Engineering database dump engine must be available in Core version, required for backing up PostgreSQL and MS SQL Server
# [LOW] Editing a profile's Configuration would always reset the One-click backup icon checkbox
# [LOW] Archive integrity check refused to run when you are using the "No post-processing" option but the "Process each part immediately" option was previously selected in a different post-processing engine
# [LOW] Total archive size would be doubled when you are using the "Process each part immediately" option but not the "Delete archive after processing" option (or when the post-processing engine is "No post-processing")
# [LOW] Warnings from the database dump engine were not propagated to the interface

Akeeba Backup 5.1.4
================================================================================
# [MEDIUM] Updated "Backup on Update" plugin to be compatible with Joomla 3.6.1 and later

Akeeba Backup 5.1.3
================================================================================
+ Automatically handle unsupported database storage engines when restoring MySQL databases
+ Help buttons everywhere. No more excuses for not reading the fine manual.
# [HIGH] Failure to upload to newly created Amazon S3 buckets
# [MEDIUM] Import from S3 didn't work with API v4-only regions (Frankfurt, S??o Paulo)
# [LOW] The [WEEKDAY] variable in archive name templates returned the weekday number (e.g 1) instead of text (e.g. Sunday)
# [LOW] Deleting the currently active profile would cause a white page / internal server error
# [LOW] Chrome and other misbehaving browsers autofill the database username/password, leading to restoration failure if you're not paying very close attention. We are now working around these browsers.
# [LOW] WebDAV prost-processing: Fixed handling of URLs containing spaces

Akeeba Backup 5.1.2
================================================================================
- Removed Dropbox API v1 integration. The v1 API is going to be discontinued by Dropbox, see https://blogs.dropbox.com/developers/2016/06/api-v1-deprecated/ Please use the Dropbox API v2 integration instead.
+ Restoration: a warning is displayed if the database table name prefix contains uppercase characters
~ Changing the #__akeeba_common table schema
~ Workaround for sites with upper- or mixed-case prefixes / table names on MySQL servers running on case-insensitive filesystems and lower_case_table_names = 1 (default on Windows)
~ The backup engine will now warn you if you are using a really old PHP version
~ You will get a warning if you try to backup sites with upper- or mixed-case prefixes as this can cause major restoration issues.
# [HIGH] Conservative time settings (minimum execution time greated than the maximum execution time) cause the backup to fail
# [MEDIUM] Akeeba Backup's control panel would not be displayed if you didn't have the Configure privilege (the correct privilege is, in fact, Access Administrator Interface)
# [MEDIUM] Restoration: The PDOMySQL driver would always crash with an error
# [LOW] Restoration: Database error information does not contain the actual error message generated by the database
# [LOW] Integrated Restoration: The last response timer jumped between values

Akeeba Backup 5.1.1
================================================================================
+ Added optional filter to automatically exclude MyJoomla database tables
~ Removed dependency on jQuery timers
~ Taking into account Joomla! 3.6's changes for the log directory location
~ Better information about outdated PHP versions
~ Warn about broken eAccelerator
~ Work around broken hosts with invalid / no default server timezone in CLI scripts
# [HIGH] gh-592 Misleading 403 Access forbidden error when your Akeeba Backup tables are missing or corrupt.
# [LOW] Seldom fatal error when installing or updating (most likely caused by bad opcode optimization by PHP itself)
# [LOW] PHP warning in the File and Directories Exclusion page when the root is inaccessible / does not exist.

Akeeba Backup 5.1.0
================================================================================
+ Big, detailed error page when you try to use an ancient PHP version which is not supported.
+ Do not automatically display the backup log if it's too big
+ Halt the backup if the encrypted settings cannot be decrypted when using the native CLI backup script
+ Better display of tooltips: you now have to click on the label to make them static
+ Enable the View Log button on failed, pending and remote backups
~ Automatically exclude core dump files
# [HIGH] PHP's INI parsing "undocumented features" (read: bugs) cause parsing errors when certain characters are contained in a password.
# [LOW] Database dump could fail on servers where the get_magic_quotes_runtime function is not available

Akeeba Backup 5.1.0.b2
================================================================================
# [LOW] The CHANGELOG shown in the back-end is out of date (thanks @brianteeman)
# [LOW] The integrated restoration would appear to be in an infinite loop if an HTTP error occurred
# [HIGH] CLI scripts in old versions of Joomla were not working
# [HIGH] CLI scripts do not respect configuration overrides (bug in engine's initialization)
# [HIGH] Backups taken with JSON API would always be full site backups, ignoring your preferences

Akeeba Backup 5.1.0.b1
================================================================================
! Restoration was broken
! Integrated restoration was broken
+ Integrated restoration in Akeeba Backup Core
~ If environment information collection does not exist, do not fail (apparently the file to this feature is auto-deleted by some subpar hosts)
~ Chrome and Safari autofill the JPS key field in integrated restoration, usually with the WRONG password (e.g. your login password)
~ Less confusing buttons in integrated restoration
+ ANGIE for Joomla!: Option to set up Force SSL in Site Setup step
# [HIGH] Remote JSON API backups always using profile #1 despite reporting otherwise
# [LOW] The "How do I restore" modal appeared always until you configured the backup profile, no matter your preferences

Akeeba Backup 5.0.4
================================================================================
# [HIGH] FaLang erroneously makes Joomla! report that the active database driver is mysql instead of mysqli, causing the backup to fail on PHP 7. Now we try to detect and work around this badly written extension.
# [HIGH] Remote JSON API backups always using profile #1
# [MEDIUM] Obsolete .blade.php files from 5.0.0-5.0.2 were not being removed
# [LOW] Junk akeeba_AKEEBA_BACKUP_ORIGIN and akeeba.AKEEBA_BACKUP_ORIGIN files would be created by legacy front-end backup
# [LOW] Backup download confirmation message had escaped \n instead of newlines

Akeeba Backup 5.0.3
================================================================================
! Blade templates would not work on servers where the Toeknizer extension is disabled / not installed
# [HIGH] The backup on update plugin wouldn't let you update Joomla!
# [MEDIUM] The "Upload Kickstart" feature would fail
# [MEDIUM] Site Transfer Wizard: could not set Passive Mode
# [LOW] Testing the connection in Multiple Databases Definitions would not show you a success message
# [LOW] If saving the file and directories filters failed you would not receive an error message, it would just hang

Akeeba Backup 5.0.2
================================================================================
! Multipart backups are broken
# [HIGH] Remote JSON API backups would result in an error
# [HIGH] Front-end (remote) backups would always result in an error
# [MEDIUM] Sometimes the back-end wouldn't load complaining that a class is already loaded

Akeeba Backup 5.0.1
================================================================================
# [HIGH] The update sites are sometimes not refreshed when upgrading directly from Akeeba Backup 4.6 to 5.0
# [HIGH] The Quick Icon plugin does not work and disables itself
# [MEDIUM] Profile copy wasn't working
# [MEDIUM] The update sites in the XML manifest were wrong

Akeeba Backup 5.0.0
================================================================================
+ Automatic detection and working around of temporary data load failure
+ Improved detection and removal of duplicate update sites
+ Direct download link to Akeeba Kickstart in the Manage Backups page
+ Working around PHP opcode cache issues occurring right before the restoration starts if the old restoration.php configuration file was not removed
+ Schedule Automatic backups button is shown after the Configuration Wizard completes
+ Schedule Automatic backups button in the Configuration page's toolbar
+ Download log button from ALICE page
~ Remove obsolete FOF 2.x update site if it exists
~ Chrome won't let developers restore the values of password fields it ERRONEOUSLY auto-fills with random passwords. We worked around Google's atrocious bugs with extreme prejudice. You're welcome.
# [HIGH] Joomla! "Conservative" cache bug: you could not enter the Download ID when prompted
# [HIGH] Joomla! "Conservative" cache bug: you could not apply the proposed Secret Word when prompted
# [HIGH] Joomla! "Conservative" cache bug: component Options (e.g. Download ID, Secret Word, front-end backup feature) would be forgotten on the next page load
# [HIGH] Joomla! "Conservative" cache bug: the "How do I restore" popup can never be made to not display
# [MEDIUM] Fixed Rackspace CloudFiles when using a region different then London
# [LOW] Missing language strings in ALICE
