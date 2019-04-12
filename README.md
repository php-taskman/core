[![Latest Stable Version](https://poser.pugx.org/phptaskman/core/v/stable)](https://packagist.org/packages/phptaskman/core)
 [![Total Downloads](https://poser.pugx.org/phptaskman/core/downloads)](https://packagist.org/packages/phptaskman/core)
 [![Build Status](https://travis-ci.org/php-taskman/core.svg?branch=master)](https://travis-ci.org/php-taskman/core)
 [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-taskman/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![Code Coverage](https://scrutinizer-ci.com/g/php-taskman/core/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-taskman/core/?branch=master)
 [![License](https://poser.pugx.org/phptaskman/core/license)](https://packagist.org/packages/phptaskman/core)

# PHP Taskman

## Description

This library is a helper for running pre-defined customizable commands and tasks.

It is shipped with a few simple commands, just the bare minimum.

## Requirements

* PHP >= 7.1

## Installation

```composer require phptaskman/core```

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

### Custom commands

* Create a file `taskman.yml` or `taskman.yml.dist` in your project, and start adding commands:

```
commands:
  hello-world:
    - echo "Hello"
    - echo "world !"
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
