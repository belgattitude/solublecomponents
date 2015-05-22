---
title: Requirements
taxonomy:
    category: docs
---

Normalist have been designed to support PHP 5.3+ runtime and MySQL/MariaDB 5.1+ database server. 

1. PHP 5.3 or higher
2. PHP mysqli,, mysqlnd and pdo_mysql extension.
3. MySQL/MariaDB server 5.1 or higher

>>>>> Although PHP 5.3 is the minimal requirement, we highly encourage to upgrade to higher versions.   

### PHP Requirements

Most hosting providers and even local LAMP setups have PHP pre-configured with everything you need for Grav to run out of the box.  However, some windows setups, and even Linux distributions (I'm look at you Debian!) ship with a very minimal PHP compile. Therefore, you may need to install or enable these PHP modules:

* `mysqli` (or mysqlnd, the official native mysql driver)
* `pdo_mysql` (PDO mysql driver)

##### Optional Modules

* `opcache` (PHP 5.5+) for increased PHP performance
* `xdebug` useful for debugging in development environment

### Permissions

For Normalist to function properly your schema cache path should be fully writable by the webserver and/or the CLI version.

1. In a **local development environment**, you can usually configure your webserver to run as your user.  This way the web server will always create and modify files as your user and you will never have issues.

2. Change the **group permissions** on all files and folders so that the webserver's group has write access to files and folders while keeping the standard permissions.  This requires some commands (note: adjust `www-data` to be the group your apache runs under (`www-data`, `apache`, `nobody`, etc):

```
chgrp -R www-data .
find . -type f | xargs chmod 664
find . -type d | xargs chmod 775
find . -type d | xargs chmod +s
umask 0002
```


