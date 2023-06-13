# Contributing to Optimole ⚙

We would love for you to contribute to Optimole and help make it even better than it is
today! As a contributor, here are the guidelines we would like you to follow:

 - [Code of Conduct](#coc)
 - [Question or Problem?](#question)
 - [Issues and Bugs](#issue)
 - [Feature Requests](#feature)
 - [Submission Guidelines](#submit)
 - [Coding Guidelines](#rules)
 - [Commit Message Guidelines](#commit) 

## <a name="coc"></a> Code of Conduct
Help us keep Optimole open and inclusive. Please read and follow our [Code of Conduct](https://github.com/Codeinwp/optimole-wp/blob/master/CODE_OF_CONDUCT.md).

## <a name="question"></a> Got a Question or Problem?

Do not open issues for general support questions as we want to keep GitHub issues for bug reports and feature requests. You've got much better chances of getting your question answered if you contact us directly on [optimole.com](https://optimole.com)

To save your and our time, we will systematically close all issues that are requests for general support and redirect people to our support form.
 
## <a name="issue"></a> Found a Bug?
If you find a bug in the source code, you can help us by
[submitting an issue](#submit-issue) to our [GitHub Repository](https://github.com/Codeinwp/optimole-wp). Even better, you can
[submit a Pull Request](#submit-pr) with a fix.

## <a name="feature"></a> Missing a Feature?
You can *request* a new feature by [submitting an issue](#submit-issue) to our GitHub
Repository. If you would like to *implement* a new feature, please submit an issue with
a proposal for your work first, to be sure that we can use it.
Please consider what kind of change it is:

* For a **Major Feature**, first open an issue and outline your proposal so that it can be
discussed. This will also allow us to better coordinate our efforts, prevent duplication of work,
and help you to craft the change so that it is successfully accepted into the project.
* **Small Features** can be crafted and directly [submitted as a Pull Request](#submit-pr).

## <a name="submit"></a> Submission Guidelines

### <a name="submit-issue"></a> Submitting an Issue

Before you submit an issue, please search the issue tracker, maybe an issue for your problem already exists and the discussion might inform you of workarounds readily available.

We want to fix all the issues as soon as possible, but before fixing a bug we need to reproduce and confirm it.  

A minimal reproduction allows us to quickly confirm a bug (or point out a coding problem) as well as confirm that we are fixing the right problem.

Unfortunately, we are not able to investigate / fix bugs without a minimal reproduction, so if we don't hear back from you we are going to close an issue that doesn't have enough info to be reproduced.

You can file new issues by selecting from our [new issue templates](https://github.com/Codeinwp/optimole-wp/issues/new/choose) and filling out the issue template.


### <a name="submit-pr"></a> Submitting a Pull Request (PR)
Before you submit your Pull Request (PR) consider the following guidelines:

1. Search [GitHub](https://github.com/Codeinwp/optimole-wp/pulls) for an open or closed PR
  that relates to your submission. You don't want to duplicate effort.
1. Be sure that an issue describes the problem you're fixing, or documents the design for the feature you'd like to add.
  Discussing the design up front helps to ensure that we're ready to accept your work. 
1. Fork the Codeinwp/optimole-wp repo.
1. Make your changes in a new git branch:

     ```shell
     git checkout -b my-fix-branch master
     ```

1. Create your patch, **including appropriate test cases**. 
1. If you are familiar with Docker, you can test your change in a containerezed environment by running `docker-compose up -d`. The environment will contains wordpress files + npm and phpunit used in the testing routines. More details about the docker image used [here](https://github.com/HardeepAsrani/pirate-brewery) 
1. Follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/) and make sure the changes are following the standard using `composer run-script lint` command.
1. Run the full PHPUnit test suite, using `composer run-script test` command, and ensure that all tests pass.
1. Commit your changes using a descriptive commit message that follows our
  [commit message conventions](#commit). Adherence to these conventions
  is necessary because release notes are automatically generated from these messages.
1. Push your branch to GitHub:

    ```shell
    git push origin my-fix-branch
    ```

1. In GitHub, send a pull request to `optimole-wp:master`.
* If we suggest changes then:
  * Make the required updates.
  * Re-run the PHPunit test suites to ensure tests are still passing and coding standard is followed.
  * Rebase your branch and force push to your GitHub repository (this will update your Pull Request):

    ```shell
    git rebase master -i
    git push -f
    ```

That's it! Thank you for your contribution!

## <a name="rules"></a> Coding Guidelines 

- **Ensure you stick to the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)**
- Install our pre-commit hook using npm. It'll help with the Coding Standards. To install run `npm install` from the command line within the optimole-wp plugin directory.
- Ensure you use LF line endings in your code editor. Use [EditorConfig](http://editorconfig.org/) if your editor supports it so that indentation, line endings and other settings are auto configured.
- When committing, reference your issue number (#1234) and follow the [commit message conventions](#commit) .
- Ensure that your code is compatible with PHP 5.4+.
- Push the changes to your fork and submit a pull request on the master branch of the `optimole-wp` repository.

## Releasing

This repository uses conventional [changelog commit](https://github.com/Codeinwp/conventional-changelog-simple-preset) messages to trigger release 

### Code freeze

After all the changes for a release are merged into development then we will freeze the code on 
the development branch and only release a new version after the plugin build is 
tested and approved by QA.

This help us make sure no error occurs after all the small pr's were tested and merged together. 

Once you decide the development branch is ready for a release you will need to post a comment to the pr 
to let everyone know that the code is frozen and no more changes should be merged into development. It's important
to tag the `qa` team and the `Optimole` team. 

To make sure the qa team gets the notification for testing the pre release pull request, please
add the pull request to the planning project and move it to `ready to test`(same as we do for issues). 


### How to release a new version:

- Clone the master branch
- Do your changes
- Send a PR to master and merge it using the following subject message
  - `release: <release short description>` - for patch release
  - `release(minor): <release short description>` - for minor release
  - `release(major): <release short description>` - for major release
The release notes will inherit the body of the commit message which triggered the release. For more details check the [simple-preset](https://github.com/Codeinwp/conventional-changelog-simple-preset) that we use.