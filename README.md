ProjectQualityInspector
-----

ProjectQualityInspector is a PHP script `pqi` which checks project good practices.
This generic quality tool will check your projects through various configurable rules.

[![Latest Stable Version](https://img.shields.io/packagist/v/rednaxe/project-quality-inspector.svg?style=flat-square)](https://packagist.org/packages/rednaxe/project-quality-inspector)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](https://img.shields.io/travis/ReDnAxE/project-quality-inspector/master.svg?style=flat-square)](https://travis-ci.org/ReDnAxE/project-quality-inspector)

Requirements
------------

ProjectQualityInspector requires PHP version 5.6 or greater

Installation
------------

You can install the component in 2 different ways:

* Install it via Composer (``rednaxe/project-quality-inspector`` on `Packagist`_);
* Use the official Git repository (https://github.com/rednaxe/project-quality-inspector).

Usage
-----

Each rule is configurable within a pqi.yml file, and new rules can be added

- it can detects permissive wildcards in composer.json, 
- it can detects two routes for one action in a Sf project
- it can check if certain files exists in project (e.g .php_cs)


.. _Packagist: https://packagist.org/packages/rednaxe/project-quality-inspector