# mborne/remote-git

[![CI](https://github.com/mborne/remote-git/actions/workflows/ci.yml/badge.svg)](https://github.com/mborne/remote-git/actions/workflows/ci.yml) [![Coverage Status](https://coveralls.io/repos/github/mborne/remote-git/badge.svg?branch=master)](https://coveralls.io/github/mborne/remote-git?branch=master)

## Description

A lightweight PHP client providing a consistent access to hosted and self-hosted git repositories (github, gitlab, gogs and gitea).

## Use cases

Note that a small set of features is prefered here to a rich API integration to allow homogenous access to remote hosting services.

The original development has been realized in [mborne/satis-gitlab](https://github.com/mborne/satis-gitlab) repository to **generate a config file referencing git repositories**.

This module is also used by [mborne/git-manager](https://github.com/mborne/git-manager#git-manager) to **backup and analyse git repositories** (for example that following files are present : README.md, LICENSE,...)

## Features

* List repositories from multiple GIT hosting services filtering by
    * usernames
    * organizations/groups
* Get raw files from repositories
* Apply custom filter
    * Project contains a given file (`RequiredFileFilter`)
    * Project is a composer project (`ComposerProjectFilter`)
    * Project name doesn't match a given regexp (`IgnoreRegexpFilter`)

## Requirements

* PHP >= 7.4

## Supported GIT hosting services

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

## Dependencies

* [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [psr/log](https://packagist.org/packages/psr/log)


## License

mborne/remote-git is licensed under the MIT License - see the [LICENSE](LICENSE) file for details


## Testing

```bash
export GITLAB_TOKEN=AnyGitlabToken
export GITHUB_TOKEN=AnyGithubToken

make test
```

Note that an HTML coverage report is generated to `output/coverage/index.html`


