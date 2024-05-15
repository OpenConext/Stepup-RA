Step-up Registration Authority
==============================

[![Build Status](https://travis-ci.org/OpenConext/Stepup-RA.svg)](https://travis-ci.org/OpenConext/Stepup-RA) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/OpenConext/Stepup-RA/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/OpenConext/Stepup-RA/?branch=develop) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8f9557e9-d8b8-4625-9e2a-60587d3cb3f0/mini.png)](https://insight.sensiolabs.com/projects/8f9557e9-d8b8-4625-9e2a-60587d3cb3f0)

This component is part of "Step-up Authentication as-a Service" and requires other supporting components to function. See [Stepup-Deploy](https://github.com/OpenConext/Stepup-Deploy) for an overview. 

## Requirements

 * Docker Compose
 * A clone of [OpenConext-devconf](https://github.com/OpenConext/OpenConext-devconf/)

## Installation

Clone the repository or download the archive to a directory. 

Start the devconf Stepup environment with SelfService with local code inclusion (see devconf readme for details).   

Run `bash` on the selfservice container (`docker exec -it stepup-selfservice-1 bash`) 

Install the dependencies by running `composer install`.
Install the JS dependencies: `yarn`
Symlink assets from external packages: `./bin/console assets:install`
Finally build the front-end resources: `yarn encore dev` or `yarn encore production`

## Release strategy
Please read: https://github.com/OpenConext/Stepup-Deploy/wiki/Release-Management for more information on the release strategy used in Stepup projects.
