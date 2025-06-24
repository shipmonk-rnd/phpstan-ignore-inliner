# PHPStan error ignore inliner

Allows you to easily **inline ignore your PHPStan errors** via `@phpstan-ignore` comment.

So instead of:

```neon
parameters:
    ignoreErrors:
        -
            message: '#^Construct empty\(\) is not allowed\. Use more strict comparison\.$#'
            identifier: empty.notAllowed
            path: ../src/App/User.php
            count: 1
```

You will have the ignored error directly in the source code `src/App/User.php`:

```php
class User {

    public function updateSurname(string $surname): void
    {
        if (empty($surname)) { // @phpstan-ignore empty.notAllowed
            throw new EmptyNameException();
        }
    }

}
```

## Installation:

```sh
composer require --dev shipmonk/phpstan-ignore-inliner
```

## Usage

```sh
vendor/bin/phpstan --error-format=json | vendor/bin/inline-phpstan-ignores
```

## Cli options
- `--comment`: Adds a comment to all inlined ignores, resulting in `// @phpstan-ignore empty.notAllowed (the comment)`

## Contributing
- Check your code by `composer check`
- Autofix coding-style by `composer fix:cs`
- All functionality must be tested
