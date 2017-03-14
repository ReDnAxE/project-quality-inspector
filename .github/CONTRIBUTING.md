# Contributing to ProjectQualityInspector

## Contributor Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

## Workflow

* Fork the project.
* Make your bug fix or feature addition.
* Add tests for it. This is important so we don't break it in a future version unintentionally.
* Send a pull request. Bonus points for topic branches.

Please make sure that you have [set up your user name and email address](http://git-scm.com/book/en/v2/Getting-Started-First-Time-Git-Setup) for use with Git. Strings such as `silly nick name <root@localhost>` look really stupid in the commit history of a project.

Pull requests for bug fixes must be based on the current stable branch whereas pull requests for new features must be based on the `master` branch.

We are trying to keep backwards compatibility breaks in ProjectQualityInspector to an absolute minimum. Please take this into account when proposing changes.

## Coding Guidelines

This project comes with a configuration file for [php-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) (`.php_cs`) that you can use to (re)format your sourcecode for compliance with this project's coding guidelines:

```bash
$ wget http://get.sensiolabs.org/php-cs-fixer.phar

$ php php-cs-fixer.phar fix
```

## Using ProjectQualityInspector from a Git checkout

The following commands can be used to perform the initial checkout of ProjectQualityInspector:

```bash
$ git clone git://github.com/rednaxe/project-quality-inspector.git

$ cd project-quality-inspector
```

Retrieve ProjectQualityInspector's dependencies using [Composer](https://getcomposer.org/):

```bash
$ composer install
```

The `pqi` script can be used to invoke the project quality inspector:

```bash
$ ./pqi --version
```

## Reporting issues

Please use the most specific issue tracker to search for existing tickets and to open new tickets:

* [General problems](https://github.com/rednaxe/project-quality-inspector/issues)

