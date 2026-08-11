# mborne/remote-git

[![CI](https://github.com/mborne/remote-git/actions/workflows/ci.yml/badge.svg)](https://github.com/mborne/remote-git/actions/workflows/ci.yml) [![Semgrep](https://github.com/mborne/remote-git/actions/workflows/semgrep.yml/badge.svg)](https://github.com/mborne/remote-git/actions/workflows/semgrep.yml) [![Coverage Status](https://coveralls.io/repos/github/mborne/remote-git/badge.svg?branch=master)](https://coveralls.io/github/mborne/remote-git?branch=master)

## Description

A lightweight PHP client providing a consistent access to hosted and self-hosted git repositories (github, gitlab, gogs and gitea).

## Use cases

- The original development has been realized in [mborne/satis-gitlab](https://github.com/mborne/satis-gitlab) repository to **generate a config file referencing git repositories**.
- This module is also used by [mborne/git-manager](https://github.com/mborne/git-manager#git-manager) to **backup and analyse git repositories** (for example that following files are present : README.md, LICENSE,...)

## Features

* List repositories from multiple GIT hosting services filtering by
    * usernames
    * organizations/groups
* Get raw files from repositories
* Detect empty repositories (`isEmpty`)
* Apply custom filter
    * Project contains a given file (`RequiredFileFilter`)
    * Project is a composer project (`ComposerProjectFilter`)
    * Project name doesn't match a given regexp (`IgnoreRegexpFilter`)

## Requirements

* [PHP >= 8.3](https://www.php.net/supported-versions.php)
* Supported GIT hosting services :

| Type      | Description                                                              |
| --------- | ------------------------------------------------------------------------ |
| gitlab-v4 | [gitlab.com](https://about.gitlab.com/) and self hosted gitlab instances |
| github    | [github.com](https://github.com)                                         |
| gogs-v1   | [Gogs](https://gogs.io/) or [Gitea](https://gitea.io/)                   |

## Usage

### Create a client

```php
// configure client
$clientOptions = new ClientOptions();
$clientOptions
    ->setUrl('https://github.com')
    ->setToken($token)
;

// create client
$client = ClientFactory::createClient(
    $clientOptions,
    new NullLogger()
);
```

### Filter by usernames or orgs/groups

```php
$options = new FindOptions();
// Use '_me_' on github to include private repositories
$options->setUsers(array('mborne'));
$options->setOrganizations(array('symfony','FriendsOfSymfony'));
$projects = $client->find($options);
```

### Filter according to composer.json

```php
$options = new FindOptions();
$options->setUsers(array('mborne'));
$filter = new ComposerProjectFilter($client);
$filter->setType('library');
$options->setFilter($filter);
$projects = $client->find($options);
```

### Compose filters

```php
$options = new FindOptions();
$options->setUsers(array('mborne'));

$filterCollection = new FilterCollection();

// filter according to composer.json
$composerFilter = new ComposerProjectFilter($client);
$composerFilter->setType('library');
$filterCollection->addFilter($composerFilter);

// filter according to README.md
$filterCollection->addFilter(new RequiredFileFilter(
    $client,
    'README.md'
));

$options->setFilter($filterCollection);
$projects = $client->find($options);
```

### Skip empty repositories

```php
$options = new FindOptions();
$options->setUsers(array('mborne'));
foreach ($client->getProjects($options) as $project) {
    // avoid attempting cloning empty repositories
    if ($client->isEmpty($project)) {
        continue;
    }
    // ...
}
```

Note that `isEmpty` relies on the project metadata for gitlab (`empty_repo`), gitea and gogs (`empty`). As the github API doesn't provide such property, an extra API call is performed for repositories reported with a size of 0 (the size is expressed in kB and rounded, so that a repository containing a single small file is also reported with a size of 0).

## Dependencies

* [guzzlehttp/guzzle - 7.x](https://packagist.org/packages/guzzlehttp/guzzle)
* [psr/log](https://packagist.org/packages/psr/log)

## Contributing

See [CODING.md](CODING.md) for setup, testing and coding style.

## License

[MIT](LICENSE)

