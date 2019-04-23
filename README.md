[![Latest Stable Version](https://poser.pugx.org/phptaskman/core/v/stable)](https://packagist.org/packages/phptaskman/core)
 [![Total Downloads](https://poser.pugx.org/phptaskman/core/downloads)](https://packagist.org/packages/phptaskman/core)
 [![Build Status](https://travis-ci.org/php-taskman/core.svg?branch=master)](https://travis-ci.org/php-taskman/core)
 [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-taskman/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![Code Coverage](https://scrutinizer-ci.com/g/php-taskman/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![License](https://poser.pugx.org/phptaskman/core/license)](https://packagist.org/packages/phptaskman/core)
 [![composer.lock](https://poser.pugx.org/phptaskman/core/composerlock)](https://packagist.org/packages/phptaskman/core)

# PHP Taskman

## Description

This library is a helper for running pre-defined customizable commands and tasks.

It is shipped with a few simple commands and tasks, just the bare minimum.

## Requirements

* PHP >= 5.6

## Installation

If you're using PHP 7 (_tags starting with `1`_):

```composer require phptaskman/core```

If you're using PHP 5.6, then you must use the branch `0.x` or tags starting with `0`

```composer require phptaskman/core:dev-0.x```

## Configuration

Taskman can be customized in different ways:

1. By setting arguments and options when running a command.
2. By providing default values in configuration files. Taskman will read
   the following files in the specified order. Options supplied in later files
   will override earlier ones:
    * The defaults provided by Taskman. This file is located inside the Taskman
       repository in `config/default.yml`.
    * `taskman.yml.dist` - project specific defaults. This file should be placed
      in the root folder of the project that depends on Taskman. Use
      this file to declare default options which are expected to work with your
      application under regular circumstances. This file should be committed in
      the project.
    * `taskman.yml` - project specific user overrides. This file is also located
      in the root folder of the project that depends on Taskman. This
      file can be used to override options with values that are specific to the
      user's local environment. It is considered good practice to add this file
      to `.gitignore` to prevent it from being accidentally committed in the
      project repository.
    * User provided global overrides stored in environment variables. These can
      be used to define environment specific configuration that applies to all
      projects that uses Taskman, such as database credentials and the
      Github access token. The following locations will be checked and the first
      one that is found will be used:
        * `$PHPTASKMAN_CONFIG`
        * `$XDG_CONFIG_HOME/phptaskman/taskman.yml`
        * `$HOME/.config/phptaskman/taskman.yml`

## Optional packages

* [phptaskman/drupal](https://github.com/php-taskman/drupal)
Provides commands for your Drupal environment.

* [phptaskman/package](https://github.com/php-taskman/package)
Provide commands for generating packages out of your sources.

## Usage

```bash
./vendor/bin/taskman
```

Then run a command:

```bash
./vendor/bin/taskman [NAME-OF-THE-COMMAND]
```

## Documentation

The documentation is not up to date, this is a work in progress.

### Expose custom commands in YAML

Taskman allows you to expose new commands using a yaml file (_taskman.yml.dist or taskman.yml_):

Example:

```yaml
commands:
  myproject:site-setup:
    - { task: "chmod", file: "${site.root}/sites", permissions: 0774, recursive: true }
    - { task: "symlink", from: "../../custom/modules", to: "${site.root}/modules/custom" }
    - { task: "symlink", from: "../../custom/themes", to: "${site.root}/themes/custom" }
    - { task: "run", command: "site:setup" }
    - { task: "run", command: "site:settings-setup" }
    - { task: "run", command: "setup:behat" }
    - "ls -la"
  setup:behat:
    - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
  setup:phpunit:
    - { task: "process", source: "phpunit.yml.dist", destination: "phpunit.yml" }
```

Commands can reference each-other, allowing for complex scenarios to be implemented with relative ease.

At the moment the following tasks are supported:

| Task          | Arguments |
| ------------- | --------- |
| `mkdir`       | `dir`, `mode` (0777) |
| `touch`       | `file`, `time` (current time), `atime` (current time) |
| `copy`        | `from`, `to`, `force` (false) |
| `chmod`       | `file`, `permissions`, `umask` (0000), `recursive` (false) |
| `chgrp`       | `file`, `group`, `recursive` (false) |
| `chown`       | `file`, `user`, `recursive` (false) |
| `remove`      | `file` |
| `rename`      | `from`, `to`, `force` (false) |
| `symlink`     | `from`, `to`, `copyOnWindows` (false) |
| `mirror`      | `from`, `to` |
| `process`     | `from`, `to` |
| `process-php` | `type: append`, `config`, `source`, `destination`, `override` (false) |
| `process-php` | `type: prepend`, `config`, `source`, `destination`, `override` (false) |
| `process-php` | `type: write`, `config`, `source`, `destination`, `override` (false) |
| `run`         | `command` (will run `./vendor/bin/taskman [command]`) |

Tasks provided as plain-text strings will be executed as is in the current working directory.

### Custom commands

* Create a file `taskman.yml` or `taskman.yml.dist` in your project, and start adding commands:

```
commands:
  hello-world:
    - echo "Hello"
    - echo "world !"
    - { task: "mkdir", dir: "foo" }
  datetime:
    - date -u
```

Taskman will automatically look into your package dependencies for such files automatically.

This means that you can create custom packagist packages containing your `taskman.yml` file with your custom commands,
this will work.

### Advanced custom commands

You can define also command options along with a custom command.

```yaml
commands:
  setup:behat:
    # When you need to define command options, the list of tasks should be
    # placed under the 'tasks' key...
    tasks:
      - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
    # ...and option definitions are under 'options' key.
    options:
      # The option name, without the leading double dash ('--').
      webdriver-url:
        # Optional. If this key is present, the input option value is assigned
        # to this configuration entry. This a key feature because in this way
        # you're able to override configuration values, making it very helpful
        # in CI flows.
        config: behat.webdriver_url
        # Optional. You can provide a list of shortcuts to the command, without
        # adding the dash ('-') prefix.
        shortcut:
          - wdu
          - wurl
        # The mode of this option. See the Symfony `InputOption::VALUE_*`
        # constants. Several options can be combined.
        # @see \Symfony\Component\Console\Input\InputOption::VALUE_NONE
        # @see \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED
        # @see \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL
        # @see \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY
        mode: 4
        # Optional. A description for this option. This is displayed when
        # asking for help. E.g. `./vendor/bin/run setup:behat --help`.
        description: 'The webdriver URL.'
        # Optional. A default value when an optional option is not present in
        # the input.
        default: null
```

## Contributing

See [Contributing](CONTRIBUTING.md).
