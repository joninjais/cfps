# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

CFPS ("Claim Foreigner work fund Phang-nga system"): a Thai-language (`language => 'th-TH'`) Yii 2 app for loading and reporting on health-claim data (OP/IP/PP visits, ICD10, costs) per hospital (`hoscode` / `FundHolderID`). It is built on the Yii 2 Advanced template, but only the `frontend` tier holds real application code.

Stack (from `.github/copilot-instructions.md`): PHP 8.3 (`composer.json` requires `>=8.3`), MariaDB 11.8, nginx 1.26. Main libraries: Kartik widgets (GridView, Export, Select2, DatePicker), PhpSpreadsheet (Excel import), Highcharts widget, mPDF/TCPDF, AdminLTE and Gentelella assets.

This directory is not a git repository, so deletions cannot be recovered from history.

## Commands

```bash
composer install                  # vendor/ is already present; asset-packagist.org is configured for npm/bower assets
php yii migrate                   # run console/migrations (root ./yii defaults to YII_ENV=prod)
php yii migrate-password/run      # dry-run: report legacy plaintext passwords
php yii migrate-password/run --apply  # re-hash them to bcrypt
vendor/bin/codecept run           # root codeception.yml includes common, frontend and backend suites
vendor/bin/codecept run -c frontend unit <path/to/Test.php>   # single test file, per-app config
```

There is no build or lint step. `./yii_test` boots the console app with `YII_ENV=test` (loads `common/config/test-local.php`). I have not run the codecept commands. The tests under `frontend/tests` (Login, Signup, Contact, About, password reset) look like the stock template tests and may not match the current app.

The web entry point is `frontend/web/index.php` (pretty URLs, no script name, `.htaccess` rewrites to it). The root `index.php` only redirects the browser to `frontend/web/site/login`. Entry scripts default to `YII_ENV=prod` and `YII_DEBUG=false` unless the constants are defined beforehand.

## Architecture

**Config layering.** Each entry script merges, in order: `common/config/main.php`, `common/config/main-local.php`, `<app>/config/main.php`, `<app>/config/main-local.php`. The DB connection lives in `common/config/main-local.php`. The cookie validation key lives in `frontend/config/main-local.php`. `environments/{dev,prod}` hold the templates used by `./init`.

**Only `frontend` is real.** `backend` has just a stock `SiteController`. `console` has migrations and `MigratePasswordController`. Models, controllers and views are all under `frontend/` (namespace `frontend\...`). `frontend\models\User` is the identity class, not `common\models\User`.

**Authentication is session-based, not `Yii::$app->user`.** `frontend/controllers/BaseController.php` has a `beforeAction` that requires `Yii::$app->session['user']` and enforces a 4-hour timeout using `session['login_time']`. Subclasses list unauthenticated actions by overriding `getPublicActionIds()` (see `SiteController`). New controllers should extend `BaseController`.

Controllers extending `BaseController`: `Site`, `User`, `Opdatainfo`, `Ipdatainfo`, `Fwfdatabase`, `Fwfhealth`, `FwfFyClaim`, `Insurancepolicies`, `Xlsfileuploads`, `Deptor`, `Opa`, `Opc`, `Ipa`, `Ipc`, `Ppa`, `Ppc`, `Rpstlist`, `Fwfhealthms`, `Fwfhealthclient`.

Controllers extending `yii\web\Controller` directly, on purpose: `Logout` (must always be reachable), `Loginfail` (static page shown after a failed login), and `Sqlcode`, which checks `session->get('user')` and `hoscode === '00064'` itself.

**Per-hospital scoping lives in the controllers.** `SiteController::actionIndex` (the dashboard; the file is about 2,700 lines) reads `session['hoscode']` and branches on it. `00064` (the provincial office account) takes a different branch from ordinary hospital ("client") accounts; `SqlcodeController` also gates on `hoscode === '00064'`. Reports are mostly hand-written SQL strings run through `Yii::$app->db->createCommand(...)`, not ActiveRecord queries. Several contain interpolated values, so be careful when editing them.

**Controller naming roughly maps to features.**
- `Opdatainfo`, `Ipdatainfo`, `Opa`/`Opc`, `Ipa`/`Ipc`, `Ppa`/`Ppc`: OP, IP and PP data views and reports (A/C variants).
- `Fwfdatabase`, `Xlsfileuploads`: Excel upload and import into `opdata`, `ipdata` and `fwfdatabase` (PhpSpreadsheet; uploaded files land in `frontend/web/uploads/`, which is web-served). `Fwfdatabase/index?del=deletedata` runs `TRUNCATE TABLE fwfdatabase`. `Opdatainfo` and `Ipdatainfo` have the same `del=deletedata` parameter; I did not read what they delete.
- `Fwfhealth*`, `FwfFyClaim`, `Insurancepolicies`, `Deptor`, `Rpstlist`: other claim and registration reports.
- `User`, `Loginfail`, `Logout`, `Sqlcode`: administration.

**Views and theming.** `frontend/config/main.php` maps `@frontend/views` to `@frontend/themes/cfps` through the view `theme.pathMap`. A view under `themes/cfps/` overrides the same path in `frontend/views/`, and anything missing there falls back to `frontend/views/`. Only `layouts` and `site` exist in the theme. `themes/adminlte` is the older theme and is not active.

**Database schema is partly outside migrations.** `console/migrations` creates only `login_history`, `sql_code`, `fwf_fy_claim`, and some user and collation changes. Core tables such as `opdata`, `ipdata` and `fwfdatabase` have no migration here, so check the live schema before changing their models. `note.txt` (Thai) holds a draft stored procedure `InsertIntoFWFDatabase` that reads `registrationtbl_tmp`/`periodtbl_tmp`, a list of planned features, and a proposal to move to a Yii2 REST API plus a Next.js/Nuxt frontend. No PHP code references the procedure.

## Things to watch

- Several controllers that existed earlier (`Ncddm`, `Ncdht`, `Person`, `Locksentday`, `Easyupload`, `Test`) and the `*.bak` copies are gone. `frontend/views/site/datacorrect.php` still links to `/person/index`, `/delperson/index`, `/ncddm/index` and `/ncdht/index`, which no longer have controllers. `views/layouts/main-gentelella.php` has a commented-out `/test/index` entry.
- `frontend/web/frontend/views/insurancepolicies/` is a stray copy of the views under `frontend/views/insurancepolicies/`. Edit the ones under `frontend/views/`.
- `frontend/web/uploads/` holds many uploaded `.xls`/`.xlsx` files plus `.sql` and `.zip` files, and `frontend/web/` also has `hosos.zip` and per-hospital `09113`, `09115`, `11354` folders with `.zip` files. These are inside the web root and may be downloadable by URL. The data is health-claim data, so review it before changing web server or upload settings.
- `/www/wwwroot/frg/dataconfig.php` (outside this project, in a sibling directory) is still present. It is obfuscated: hex-escaped strings feed `eval(htmlspecialchars_decode(gzinflate(base64_decode(...))))`. Nothing in this project references it. Do not include, run or deploy it. It looks like a backdoor and should be reviewed and removed by the project owner. `/www/wwwroot/frg-03112569.zip` (about 2.3 GB) is also in that parent directory.
- `.vscode/` is now empty. The database credentials are in `common/config/main-local.php` and the cookie validation key is in `frontend/config/main-local.php`. Do not copy these into other files, logs or commits.
- The session cookie has `secure => false` in `frontend/config/main.php`; it is meant to be set to `true` under HTTPS.
- UI text, comments and flash messages are in Thai. Match that when editing user-facing strings.


---
name: yii2-cfps-developer
description: Expert Yii2 Advanced developer guide for CFPS project. Combines clean architecture (Ponytail) with ultra-concise, no-fluff responses (Caveman).
---

# CFPS Project context & Tech Stack
- **App**: CFPS (Claim Foreigner Work Fund Phang-nga System)
- **Framework**: Yii 2 Advanced Template (Only `frontend` tier holds real app code)
- **Stack**: PHP 8.3+, MariaDB 11.8, Nginx 1.26
- **UI/Theming**: AdminLTE / Gentelella (Theme mapped to `@frontend/themes/cfps`), Kartik Widgets
- **Language**: Thai (`th-TH`) for UI, labels, flash messages, and user-facing strings.

# Output & Communication Style (Caveman Style)
- **No Fluff**: Skip pleasantries, introductions, and verbose explanations.
- **Direct Code First**: Show code diffs, code snippets, or precise file modifications immediately.
- **Short Summaries**: Max 1-2 lines of explanation per change.
- **Bullet Points Only**: If explanation is needed, use concise technical bullets.

# Code Architecture & Rules (Ponytail Style)

## 1. Authentication & Base Controller
- **Auth Model**: Uses session-based auth (`Yii::$app->session['user']` + 4-hr timeout), NOT standard `Yii::$app->user`.
- **Inheritance**: All new controllers MUST extend `frontend\controllers\BaseController`.
- **Public Actions**: Override `getPublicActionIds()` to declare unauthenticated actions.
- **Identity**: User model lives at `frontend\models\User`.

## 2. Security & Data Scoping
- **Hospital Isolation**: Scope data by `session['hoscode']`. Account `00064` is the provincial admin level.
- **SQL Safety**: Avoid raw SQL string concatenation. Always use prepared statements via `Yii::$app->db->createCommand($sql, $params)`.
- **Session Security**: Keep `secure => true` context-aware under HTTPS environments.
- **Sensitive Paths**: Avoid exposing files in `frontend/web/uploads/` directly.

## 3. Views, Theming & UI
- **Theme Overrides**: Edit views under `@frontend/themes/cfps/` for layout/site overrides, or `@frontend/views/` for model/feature views. (Ignore legacy `frontend/web/frontend/views/` or `themes/adminlte`).
- **Widgets**: Use `kartik\` widgets (`GridView`, `Select2`, `DatePicker`, `Export`) for reporting UIs.
- **Language**: Ensure all UI text and Flash messages are written in clear Thai (`th-TH`).

## 4. Database & Models
- **Raw SQL Context**: Core tables (`opdata`, `ipdata`, `fwfdatabase`) are partly outside console migrations. Check live schema before defining ActiveRecord properties or relations.
- **Migration**: Use `console/migrations` only for system/lookup tables.

# Operational Examples

### Example 1: Creating a new Controller (Ponytail Arch + Caveman Output)
**Requirement**: Add new report controller.
**Output**:
```php
namespace frontend\controllers;

use Yii;
use frontend\controllers\BaseController;

class ReportClaimController extends BaseController
{
    protected function getPublicActionIds(): array
    {
        return []; // All actions require session auth
    }

    public function actionIndex()
    {
        $hoscode = Yii::$app->session->get('hoscode');
        $data = Yii::$app->db->createCommand('
            SELECT * FROM fwfdatabase WHERE hoscode = :hoscode
        ', [':hoscode' => $hoscode])->queryAll();

        return $this->render('index', ['data' =>$data]);
    }
}