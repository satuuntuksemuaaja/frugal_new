# Vocalogic Package Usage
- [Installation](#installation)
- [Form Builder](#form-builder)

<a name="installation"></a>
## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `vocalogic/laravel5`.

```json
"require": {
    "vocalogic/laravel5": "dev-master"
}
```

It will also be necessary to include the GitHub repository in the repositories settings:

```json
"repositories": [
    {
        "type": "vcs",
        "url":  "git@github.com:vocalogic/laravel5.git"
    }
]
```

Next, update Composer from the Terminal (use `-n` so composer uses SSH keys to authenticate at GitHub):

    composer -n update

Next, add your new provider to the `providers` array of `config/app.php`:

```php
  'providers' => [
    // ...
    'Vocalogic\VocalogicServiceProvider',
    // ...
  ],
```

Finally, add the class aliases to the `aliases` array of `config/app.php`:

```php
  'aliases' => [
    // ...
        'Form'      => 'Vocalogic\Html\FormFacade',
        'Html'      => 'Vocalogic\Html\HtmlFacade',
    // ...
  ],
```

<a name="form-builder"></a>
## Form Builder
