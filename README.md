<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Advanced Project Template</h1>
    <br>
</p>

Yii 2 Advanced Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

The local Vagrant environment uses Ubuntu 24.04, PHP 8.3, and the official NGINX
stable package repository. Its HTTP virtual hosts set `X-Content-Type-Options`,
`X-Frame-Options`, and `Referrer-Policy`; configure HSTS only at an HTTPS
terminating edge. MySQL remains on its default local listener and no remote root
account is created. These settings do not update any separately deployed server.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-advanced/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-advanced/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-advanced.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-advanced)

DIRECTORY STRUCTURE
-------------------

```

common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```

### Insurance policy Excel import

Run `php yii migrate` before importing the current insurance report. The import
supports the legacy 26-column report and the newer A–AR and A–AV reports,
including the address, allocation and employer columns. Existing duplicate
policies are skipped rather than updated; rows without a non-zero card price
are also skipped as before. The migration does not remove existing data and
cannot be rolled back automatically because the added columns may contain
imported data. Back up the database before applying schema changes.

The insurance dashboard filters all summaries by purchase date, defaulting to
the current Thai fiscal year (October through September). Select a fiscal year,
an individual month, or all periods; all periods also includes records without
a purchase date.
Provincial administrators can filter the dashboard by a single agency or view
all agencies; other users cannot select an agency.

The uploaded-file list shows only records whose facility code matches the
signed-in account, including for provincial administrators. Existing
facility-scoped permissions for file operations and imports are unchanged.

### User access and approval

The frontend uses Yii RBAC roles with facility-scoped data access:

| Role | Read scope | Write and management scope |
| --- | --- | --- |
| Provincial administrator (`provincialAdmin`) | Facilities with the same `provcode` as the administrator's facility | Province-wide administrative actions |
| Delegated user manager (`userManager`, added on top of the facility role) | Same as its hospital/PCU staff role | Same as its facility role, plus managing ordinary user accounts in the province of facility `00064` |
| Hospital staff (`hospitalStaff`) | Own facility and facilities sharing its `cupcode` | Own facility only |
| PCU staff (`pcuStaff`) | Own facility only | Own facility only |

Self-registered accounts remain pending and cannot sign in until approved by a
provincial administrator. The account at facility `00064` can grant or remove
the delegated `userManager` role. It is assigned in addition to the account's
hospital/PCU role, so these users keep the normal facility home page and data
access and gain only the user-management menu. They cannot manage other
administrator accounts or grant administrator roles, and may change their own
facility code only within the province associated with `00064`. Accounts are restricted to that province. Staff role assignment is checked against
`chospital2.cupcode`: a facility whose `cupcode` equals its own `hospcode` is a
hospital; other facilities are PCUs. Existing accounts are marked approved by
the RBAC migration, and existing `hoscode=00064` accounts receive the
provincial administrator role.

Before deploying, back up the database and verify the `chospital2` province and
CUP mappings for existing accounts. Apply the Yii RBAC migrations and the
application access-control migrations with `php yii migrate`. The delegated
user-manager migration converts previously delegated `provincialAdmin`
assignments (all except accounts at facility `00064`) to the facility role plus
`userManager`. Do not run migrations against production until the
account-to-facility mapping has been reviewed and the database backed up.

Deleting a user hides the account from user management, revokes its roles, and
blocks both new logins and existing sessions. The user record remains in the
database, and signup continues to reject its username and email. Apply the user
soft-delete migration with `php yii migrate` before deploying this change. Its
rollback is blocked while deleted accounts exist, to avoid accidentally
re-enabling them.
