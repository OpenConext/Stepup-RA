Step-up Registration Authority
==============================

[![Build Status](https://travis-ci.org/OpenConext/Stepup-RA.svg)](https://travis-ci.org/OpenConext/Stepup-RA) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/OpenConext/Stepup-RA/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/OpenConext/Stepup-RA/?branch=develop) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8f9557e9-d8b8-4625-9e2a-60587d3cb3f0/mini.png)](https://insight.sensiolabs.com/projects/8f9557e9-d8b8-4625-9e2a-60587d3cb3f0)

This component is part of "Step-up Authentication as-a Service" and requires other supporting components to function. See [Stepup-Deploy](https://github.com/OpenConext/Stepup-Deploy) for an overview. 

## Requirements

 * PHP 7.2
 * [Composer](https://getcomposer.org/)
 * A web server (Apache, Nginx)
 * Graylog2 (or disable this Monolog handler)
 * A working [Gateway](https://github.com/OpenConext/Stepup-Gateway)
 * Working [Middleware](https://github.com/OpenConext/Stepup-Middleware)

## Installation

Clone the repository or download the archive to a directory. Install the dependencies by running `composer install`.

Run `app/console mopa:bootstrap:symlink:less` to configure Bootstrap symlinks.

## Release strategy
Please read: https://github.com/OpenConext/Stepup-Deploy/wiki/Release-Management fro more information on the release strategy used in Stepup projects.
