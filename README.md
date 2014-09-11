#CssFixerBundle

This bundle wraps [csscombjs](https://github.com/csscomb/csscomb.js/) and adds a CSS code style validation to your project

###Requirements

The `npm` executable must be available in `PATH`

###Installation

####1) Add a dependency to your composer.json file:

```json
{
    "require-dev": {
        "fdevs/css-fixer-bundle": "~1.0"
    }
}
```
You need this bundle only for development, so use `require-dev` section of the `composer.json` file.

####2) Install the package to your project via composer:
```shell
composer update fdevs/css-fixer-bundle
```

####3) Add the bundle to your `AppKernel.php` file:


```php
// app/AppKernel.php

public function registerBundles()
{
    // ...
    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        $bundles[] = new FDevs\CssFixerBundle\FDevsCssFixerBundle();
        // ...
    }
    // ...
}

```
As before, register this bundle in the `dev` environment only, so it won't consume memory in the `prod` environment.    

Here `FDevsCssFixerBundle` is ready to use. Its default configuration allows it to check `all` `your` bundles, that are living inside `src` folder.

###Examples of usage

#### Validation of .css files

This bundle has console command:
```shell
app/console fdevs:cs:css-fixer
```
or its alias: 
```shell
app/console code-style:css-fixer
```

By default it works in `lint` mode, just shows files that aren't following code style conventions.

You can use next options:
```shell
--fix (-f) # force css fixer to fix files, not just show mistakes
--verbose (-v) # show additional messages and list of checked files and fixed ones
```

#### How to see all configuration options and their default values

To see all available options and examples of values, you can use a standard console command:

```shell
app/console config:dump-reference f_devs_css_fixer

```
It will show you all necessary information to quickly anderstand and customize the bundle to your needs.

#### Customizing bundle to own needs and code style conventions

The bundle has 3 main sections in its configuration:

- `include` - here you can add bundles you want to check .css files in.
- `exclude` - here you can add list of bundles you don't want to check.
- `rules` - here you can set up each of more than 20 options available for csscomb fixer.

#### Example of configuration

```yaml
f_devs_css_fixer:
    include:
        - FDevsFirstBundle
        - FDevsSecondBundle
    exclude:
        - AcmeDemoBundle
    rules:
        space_before_opening_brace: 1
        space_before_closing_brace: "\n"
    # other rules you want to change
```

Priority for bundles is next (from the lowest):
- all your bundles (if neither `include` nor `exclue` provided, then all your bundles will be checked)
- include (will replace all if provided)
- exclude (will remove bundles both from all or `include`)
