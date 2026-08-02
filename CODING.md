# Coding

## Requirements

* PHP >= 8.3 with [Xdebug](https://xdebug.org/) (for code coverage)
* [Composer](https://getcomposer.org/)

## Setup

```bash
composer install
```

Access tokens are optional, tests relying on them are skipped when missing :

```bash
# see https://github.com/settings/tokens
export GITHUB_TOKEN=AnyGithubToken
# see https://gitlab.com/-/profile/personal_access_tokens
export GITLAB_TOKEN=AnyGitlabToken
# see https://gitea.com/user/settings/applications
export GITEA_TOKEN=AnyGiteaToken
```

## Commands

| Command                | Description                                                     |
| ---------------------- | --------------------------------------------------------------- |
| `composer test`        | Run `check-style`, `check-rules` and `phpunit` (same as CI)     |
| `composer phpunit`     | Run tests only, with coverage reports                           |
| `composer check-style` | Check coding style with [php-cs-fixer](https://cs.symfony.com/) |
| `composer fix-style`   | Fix coding style with php-cs-fixer                              |
| `composer check-rules` | Run static analysis with [phpstan](https://phpstan.org/)        |

Coding style is `@Symfony,-global_namespace_import`, static analysis is configured in [phpstan.neon](phpstan.neon).

## Outputs

`composer phpunit` writes to the `output/` directory :

* `output/coverage/index.html` : HTML coverage report
* `output/clover.xml` : coverage report sent to [coveralls.io](https://coveralls.io/github/mborne/remote-git) by CI
* `output/junit-report.xml` : test report


## Source layout

`src/` is a `MBO\RemoteGit\` PSR-4 root split between a generic API and its per-host implementations :

| Path                                      | Description                                                                                                                    |
| ----------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `src/*.php`                               | The generic API : `ClientInterface` and `ProjectInterface`, their options (`ClientOptions`, `FindOptions`) and `ClientFactory` |
| `src/Github/`, `src/Gitlab/`, `src/Gogs/` | One `<Host>Client` + `<Host>Project` pair per host, extending `AbstractClient` (`Gogs` also covers gitea)                      |
| `src/Filter/`                             | `ProjectFilterInterface` implementations, composable through `FilterCollection`                                                |
| `src/Http/`, `src/Helper/`                | HTTP and logging plumbing shared by the clients                                                                                |
| `src/Exception/`                          | Library exceptions                                                                                                             |

Adding a host means adding a `<Host>Client`/`<Host>Project` pair and registering the client in `ClientFactory`, which maps a URL to a client using each client's `TYPE` and `TOKEN_TYPE` constants.

`tests/` mirrors that layout under `MBO\RemoteGit\Tests\`. Tests named `*FunctionalTest` hit the real APIs and are skipped without a token, while `*ProjectTest` ones are offline and build projects from the JSON fixtures in `tests/data/` (see `TestCase::getDataJson()`).
