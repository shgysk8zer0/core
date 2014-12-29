[Super User Core](<https://github.com/shgysk8zer0/core>)
====

Core classes for Super User and others (for use as submodule)
> A collection of classes for common PHP tasks such as:
* Database queries (*using PDO*)
* Creating `JSON` encoded responses to `XMLHttp` requests
* Handling `$_SESSION`s & `$_COOKIE`s
* Parsing a variety of file MIME types
* Managing logins
* Logging PHP errors
* etc.

# Installation instructions
There are a variety of ways to include these classes in your project, such as
downloading them as in an archived format and extracting them where desired,
directly cloning into your project, or adding as a submodule in your projects
repository.

## To add as a submodule:
* Copy the repository URI or for it and copy the URI of your fork
* `cd` into your project directory
* `git submodule add {repository URI} {path/to/destination}`
* **Note that submodules are not updated in a regular `git pull`**
 * To pull updates in submodules, use `git submodule update`

# Using
Super User Core classes are designed to be easy to use with PHP's built-in auto-loading
* Make sure that the parent directory is in your `include_path`
 * `set_include_path({core_parent_directory} . PATH_SEPARATOR . get_include_path());`
* Configure file extensions to use
 * `spl_autoload_extensions('.class.php');`
* Set the auto-loader
 * `spl_autoload_register();`
* Create a new instance of a class including path/namespace
 * `$my_class = new \shgysk8zer0\Core\my_class($args)`
 * Or `$my_class = \shgysk8zer0\Core\my_class::static_method($args)`

# Updating
If installed as a submodule in Git, updating is relatively easy
* To update to the latest version: `git submodule update --remote`
* To update to the version in your repository:
 * `git pull` to pull changes from your repository
 * `git submodule update` to checkout the commit used in the project's repository

**Git treats submodules as single files, and submodules have a `DETACHED HEAD` unless you checkout a branch**

# Contributing
## Report a bug or request a feature
Issues may be reported on GitHub via my [Issues Page](<https://github.com/shgysk8zer0/core/issues/new>)
## Create a pull request
Pull requests can be made either on GitHub or via [email](<mailto:shgysk8zer0@gmail.com>)
For best results, you should fork this repo and add the main repo as a remote
`git remote add shgysk8zer0 git://github.com/shgysk8zer0/core.git`
* Create a [Pull Request](<https://github.com/shgysk8zer0/core/compare>) on GitHub
* To send a pull request via email:
 * Email me the output of `git request-pull shgysk8zer0/master origin > {path/to/destination.diff}`
* Or send me a patch (along with a pull-request or diff)
 * `git format-patch -o {/path/to/patches} shgysk8zer0/master`

## Donate
Donations may be made using Bitcoin (*paypal coming soon*)

![Bitcoin QR](<http://chriszuber.com/images/coinbase_qr.png>)
[12WunGFBrDTRkAdgU6fbiZyyM4WSaAZeHD](<bitcoin:12WunGFBrDTRkAdgU6fbiZyyM4WSaAZeHD>)
