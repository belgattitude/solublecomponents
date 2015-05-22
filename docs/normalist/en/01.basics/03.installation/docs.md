---
title: Installation
taxonomy:
    category: docs
---

Installation of Grav is a trivial process. In fact, there is no real installation.  You have **two** options for installing Grav.  The first, and simplicity way is simply grab the **zip**, the other way is to install the source from **GitHub** and run a command to install dependencies:
Installation of Normalist is very easy. 

## Option 1: Install with composer.

The easiest and recommended way to install Normalist is to through [Composer](https://getcomposer.org/):

```bash
$ php composer require soluble/normalist
```

Or alternatively, add soluble/normalist in your composer.json file as described below

```json
{
    "require": {
        "soluble/normalist": "0.9.*"
    }
}
```

```bash
$ php composer.phar update
```

>>>>> Composer will figure out all needed dependencies and install them accordingly.


## Option 1: Install with ZIP package

The easiest way to install Grav is to use the ZIP package and install it:

1. Download the latest-and-greatest **Grav Base** package from the [Downloads](http://getgrav.org/downloads)
2. Extract the ZIP file in your [webroot](https://www.wordnik.com/words/webroot) of your web server, e.g. `~/webroot/grav`

>>>> If you downloaded the ZIP file and then plan to move it to your webroot, please move the **ENTIRE FOLDER** because it contains several hidden files (such as .htaccess) that will not be selected by default. The omission of these hidden files can cause problems when running Grav.

