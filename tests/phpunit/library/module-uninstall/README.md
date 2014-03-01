WordPoints Module Uninstall Tester
==========================

A testcase class for testing WordPoints module install and uninstall, with related
tools.

# Background #
#
This repo is an extension of the [WP Plugin Uninstall
Tester](https://github.com/JDGrimes/wp-plugin-uninstall-tester), which enables it for
use with WordPoints modules. For more information about the purpose and use of it,
see that repo.

# Requirements #
#
The [WP Plugin Uninstall
Tester](https://github.com/JDGrimes/wp-plugin-uninstall-tester) is required, and must
be included in your bootstrap before you include this extension's files.

# Installation #
#
The installation may be performed similar as for the plugin uninstall tester
(examples assume you are adding these tools in
tests/phpunit/library/module-uninstall):

```bash
git subtree add --prefix tests/phpunit/library/module-uninstall \
   git@github.com:WordPoints/wordpoints-module-uninstall-tester.git master --squash
```

Updating is then done like so:

```bash
git subtree pull --prefix tests/phpunit/library/module-uninstall \
   git@github.com:WordPoints/wordpoints-module-uninstall-tester.git master --squash
```

# Set Up #

You need to modify your tests' boostrap file to only include the module when the
uninstall tests aren't being run.

```php
/*
 * This needs to go after you include WordPress's unit test functions, but before
 * loading WordPress's bootstrap.php file.
 */

// Include the plugin uninstall test tools functions.
include_once dirname( __FILE__ ) . '/../library/plugin-uninstall/includes/functions.php';

// Now include the module uninstall test tools functions.
include_once dirname( __FILE__ ) . '/../library/module-uninstall/includes/functions.php';

// Check if the tests are running. Only load the plugin if they aren't.
if ( ! running_wordpoints_module_uninstall_tests() ) {
    tests_add_filter( 'muplugins_loaded', 'my_plugin_activate' );
}
```

Secondly, you need to include the `bootstrap.php` file:

```php
/*
 * This needs to be included after loading WordPress's bootstrap.php, because the
 * uninstall testcase extends WordPress's WP_UnitTestCase class.
 */

// Include the plugin uninstall tools bootstrap.
include_once dirname( __FILE__ ) . '/../library/plugin-uninstall/bootstrap.php';

// Now include the module uninstall tools bootstrap.
include_once dirname( __FILE__ ) . '/../library/module-uninstall/bootstrap.php';
```

Thirdly, you need to exclude the uninstall group from the tests in your PHPUnit XML
config file:

```xml
    <!-- This needs to go inside of the <phpunit></phpunit> tags -->
    <groups>
        <exclude>
            <group>uninstall</group>
        </exclude>
    </groups>
```

That will exclude the uninstall tests from running by default. To run them, you'll
need to do `phpunit --group=uninstall`.

Finally, you will need to set `WORDPOINTS_TESTS_DIR` in your environment. This should
be the full path to the `/tests/phpunit/` directory in the WordPoints plugin
development source.

Example:

`export WORDPOINTS_TESTS_DIR=/path/to/wordpoints/tests/phpunit/`

# Usage #

The only difference in usage from the plugin uninstall test tools, is that the path
to the main module file is stored in the `$module_file` property, instead
of `$plugin_file`.
