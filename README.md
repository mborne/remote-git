# mborne/remote-git

[![Build Status](https://travis-ci.org/mborne/remote-git.svg)](https://travis-ci.org/mborne/remote-git)

## Description

A lightweight client providing a consistent way to find hosted and self-hosted git repositories (github, gitlab,...)

It targets use case such as :

* Backuping repositories from multiple sources
* Cloning all repositories from multiple sources (self hosted private repositories and public repositories) to performs analysis
* Generate PHP SATIS configuration according to existing repositories (see [mborne/satis-gitlab](https://github.com/mborne/satis-gitlab))

Note that a small set of features is prefered to a rich API integration to allows homogenous access to remote hosting services.


## Supported GIT hosting services

| Name      | Description                                                              |
|-----------|--------------------------------------------------------------------------|
| gitlab-v4 | [gitlab.com](https://about.gitlab.com/) and self hosted gitlab instances |
| github    | https://github.com                                                       |
| gogs-v1   | https://gogs.io/                                                         |

## Features

* Auto-detect hosting services type according to hostname
* Get repositories filtering by
    * usernames
    * organizations/groups
* Apply custom filter
    * Project contains a given file (`RequiredFileFilter`)
    * Project is a composer project (`ComposerProjectFilter`)
    * Project name doesn't match a given regexp (`IgnoreRegexpFilter`)

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


## History

The original development has been realized in [mborne/satis-gitlab](https://github.com/mborne/satis-gitlab) repository.


## Dependencies

* [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
* [psr/log](https://packagist.org/packages/psr/log)


## Requirements

* GITLAB API v4

## License

mborne/remote-git is licensed under the MIT License - see the [LICENSE](LICENSE) file for details


## Testing

```bash
export SATIS_GITLAB_TOKEN=AnyGitlabToken
export SATIS_GITHUB_TOKEN=AnyGithubToken

make test
```

Note that an HTML coverage report is generated to `output/coverage/index.html`
