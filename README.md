[![Latest Stable Version](https://img.shields.io/github/release/php-taskman/core.svg?style=flat-square)](https://packagist.org/packages/phptaskman/core)
 [![Stars](https://img.shields.io/github/stars/php-taskman/core.svg?style=flat-square)](https://github.com/php-taskman/core)
 [![Total Downloads](https://img.shields.io/packagist/dt/phptaskman/core.svg?style=flat-square)](https://packagist.org/packages/phptaskman/core)
 [![Build Status](https://img.shields.io/travis/php-taskman/core/master.svg?style=flat-square)](https://travis-ci.org/php-taskman/core)
 [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/php-taskman/core.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/php-taskman/core.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![License](https://img.shields.io/github/license/php-taskman/core.svg?style=flat-square)](https://packagist.org/packages/phptaskman/core)
 
# PHP Taskman

## Description

Taskman is a helper for running commands and tasks. It is shipped with a few simple default tasks.

It will help you in your every day life of to setup recurrent tasks that you have to run in your project in order to
set it up or install it.

Taskman is based on [Robo](https://robo.li/) and not tied to any framework or whatsoever.

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

* [phptaskman/travis](https://github.com/php-taskman/travis)
Provide commands to execute parts of your `.travis.yml` file.

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

Taskman will run commands. Commands contains one or multiple tasks.

A task can be defined using YAML or through code, same goes for commands.

An example of custom command with some tasks in a `taskman.yml.dist` file:

```yaml
commands:
  foo:foo:
    - ls -la
    - { task: "mkdir", dir: "foo" }
    - ls -la
    - { task: "run", command: "foo:remove" }
  foo:remove:
    - rm -rf foo
    - ls -la
```

As you can see, there are 2 custom commands that are defined: `foo:foo` and `foo:remove`.

Those commands contains tasks, 4 tasks for `foo:foo` and 2 tasks for `foo:remove`.

A task can be either a string or an well structured array.

### Expose custom tasks in YAML

Let's use the same example and add a custom task in the YAML file.

```yaml
tasks:
  baz:
    - ls -la

commands:
  foo:foo:
    - ls -la
    - { task: "mkdir", dir: "foo" }
    - ls -la
    - { task: "run", command: "foo:remove" }
  foo:remove:
    - rm -rf foo
    - { task: "baz" }
```

There are a few tasks that are supported by default in Taskman and provided by [the package phptaskman/core-tasks](https://packagist.org/packages/phptaskman/core-tasks).

### Expose custom commands in YAML

Taskman allows you to expose new commands using a yaml file (_taskman.yml.dist or taskman.yml_).
Commands can reference each-other, allowing for complex scenarios to be implemented with relative ease.

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
    description: Write a short description of your task here.
    help: Write a short help text here.
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
