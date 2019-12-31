# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2018-03-27

### Added

- [zendframework/zend-dom#20](https://github.com/zendframework/zend-dom/pull/4) adds support for
  attribute selectors that contain spaces, such as `input[value="Marty McFly"]`.
  Previously, spaces within the selector value would result in a query per
  space-separated word; they now, correctly, result in a single query for the
  exact value.

- [zendframework/zend-dom#19](https://github.com/zendframework/zend-dom/pull/4) adds support for PHP
  versions 7.1 and 7.2.

- Adds documentation and publishes it to https://docs.laminas.dev/laminas-dom/

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-dom#13](https://github.com/zendframework/zend-dom/pull/4) and
  [zendframework/zend-dom#19](https://github.com/zendframework/zend-dom/pull/4) remove support for PHP
  versions prior to 5.6.

- [zendframework/zend-dom#13](https://github.com/zendframework/zend-dom/pull/4) and
  [zendframework/zend-dom#19](https://github.com/zendframework/zend-dom/pull/4) remove support for HHVM.

### Fixed

- Nothing.

## 2.6.0 - 2015-10-13

### Added

- [zendframework/zend-dom#2](https://github.com/zendframework/zend-dom/pull/4) ads context node
  support for DOMXPath->query that supports querying in the context of a
  specific node.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-dom#5](https://github.com/zendframework/zend-dom/pull/5) - Increase test converage and improve tests.
