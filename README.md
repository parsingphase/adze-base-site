# parsingphase/adze-base-site [![Build Status](https://travis-ci.org/parsingphase/adze-base-site.svg?branch=master)](https://travis-ci.org/parsingphase/adze-base-site)

Deployment wrapper for sites based on the [parsingphase/adze](https://github.com/parsingphase/adze) framework

## Installation

You can clone this repo, or [download it as a zip file](https://github.com/parsingphase/adze-base-site/archive/master.zip), then run "composer install" to fetch dependencies.
As the package isn't yet registered on Packagist yet, `composer create-project` isn't an option.

## Setup

 - Copy config/config.php.dist to config.php and edit
 - Copy web/index.php.dist to index.php and edit
 - Set your webserver to use the `web` directory as the Document Root and your copied `index.php` as the default document.
