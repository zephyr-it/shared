# 🌐 Zephyr Shared Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zephyr-it/shared.svg?style=flat-square)](https://packagist.org/packages/zephyr-it/shared)
[![GitHub Tests Status](https://img.shields.io/github/actions/workflow/status/zephyr-it/shared/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zephyr-it/shared/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Style Status](https://img.shields.io/github/actions/workflow/status/zephyr-it/shared/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zephyr-it/shared/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zephyr-it/shared.svg?style=flat-square)](https://packagist.org/packages/zephyr-it/shared)

**Zephyr Shared** is a foundational package that delivers shared logic, UI components, and developer tools across all Zephyr-IT modular Laravel applications.

It provides:

-   A reusable `BasePlugin` system for Filament v3 (auto-register pages, resources, widgets)
-   Pre-configured Filament resources: Country, State, and City
-   Export architecture for multi-sheet Excel reports using Laravel Excel
-   A CLI script to check for missing language keys
-   Centralized helpers, traits, and support classes for common functionality

---

## 📦 Installation

Install via Composer:

```bash
composer require zephyr-it/shared
```

---

## 🚀 Usage

### ⚙️ Run the Shared Installer

After installing the package, publish all shared configuration files, assets, migrations, and utilities by running:

```bash
php artisan shared:install
```

This command will:

-   Publish shared configs, views, migrations, and scripts
-   Publish third-party assets like `spatie/laravel-activitylog` migrations
-   Prompt you to run `php artisan migrate`

To overwrite existing files:

```bash
php artisan shared:install --force
```

> ℹ️ This is the preferred way to initialize the shared stack within any Laravel application or module using Zephyr-IT’s architecture.

---

## 📤 Excel Report Exports

This package offers a powerful and flexible foundation for building multi-sheet Excel reports using [Laravel Excel (maatwebsite/excel)](https://laravel-excel.com).

With built-in support for:

-   🎨 Styled sheets and column formatting
-   📚 Auto-generated legends and notes
-   🎯 Custom filters, headings, and merged cells
-   📊 Modular architecture for reusable reporting logic

> ✅ Laravel Excel is already required. No additional setup is necessary.

---

### 🧱 Base Export Classes

#### 🧾 `BaseSheet`

Defines a single Excel sheet. Implements:

-   `FromCollection`, `WithHeadings`, `WithStyles`
-   `WithEvents`, `WithTitle`, `WithColumnFormatting`
-   `ShouldQueue`, `ShouldAutoSize`

Ideal for building modular, stylized reports.

#### 🗃️ `AbstractReportExport`

Used to group and manage multiple `BaseSheet` instances.
Also provides integrated date range parsing via the `HasDateRangeParser` trait.

```php
new CompanyUsersExport(
    $company,
    '2024-01-01 - 2024-01-31',
    $extraContext
);
```

---

### 🛠 BaseSheet Constructor Example

```php
new UsersSheet(
    title: 'Users',
    data: $collection,
    headings: ['ID', 'Name'],
    headerColor: '1E88E5',
    legendRows: [['Column', 'Details']],
    legendStyles: [],
    columnWidths: ['A' => 15, 'B' => 30],
    mergeCells: ['A1:C1'],
    rowStyles: [],
    applyBorders: true,
    freezeHeader: true,
    useAutoFilter: true,
    enableLogging: true,
    notesRow: 'Data generated from system.'
)
```

---

### 🧩 Feature Breakdown

| Feature         | Description                                                    |
| --------------- | -------------------------------------------------------------- |
| `notesRow`      | Adds an intro row above headings for metadata or descriptions  |
| `legendRows`    | Displays a table legend for context (e.g., column definitions) |
| `legendStyles`  | Style individual cells in the legend                           |
| `columnWidths`  | Specify manual widths for columns                              |
| `mergeCells`    | Merge specific cell ranges                                     |
| `rowStyles`     | Apply font, color, or alignment styles to specific rows        |
| `headerColor`   | Adds a background color and white text to the heading row      |
| `useAutoFilter` | Enables Excel dropdown filters on the heading row              |
| `freezeHeader`  | Locks the top row while scrolling                              |
| `enableLogging` | Logs export metadata to `storage/logs/laravel.log`             |

---

### 🎨 Extending Styles and Formats

You can override `columnFormats()` and `styles()` in your custom sheet:

```php
public function columnFormats(): array
{
    return [
        'C' => '#,##0.00',
        'D' => 'dd-mm-yyyy',
    ];
}

public function styles(Worksheet $sheet): array
{
    return [
        2 => ['font' => ['bold' => true]],
    ];
}
```

---

### 🧵 Advanced Customization

Override `registerEvents()` in your `BaseSheet` class to:

-   Customize borders
-   Dynamically merge cells
-   Alter freeze pane logic
-   Inject `notesRow` or `legendRows` conditionally

---

### ✅ Full Export Flow

#### Step 1: Define the Sheet

```php
class UsersSheet extends BaseSheet
{
    public function columnFormats(): array
    {
        return ['C' => '#,##0.00'];
    }
}
```

#### Step 2: Create the Export

```php
class CompanyUsersExport extends AbstractReportExport
{
    public function sheets(): array
    {
        return [
            new UsersSheet(
                title: 'Users',
                data: $this->entity->users->map(fn ($u) => [$u->id, $u->name, $u->balance]),
                headings: ['ID', 'Name', 'Balance'],
                notesRow: 'This report shows active users only.',
                legendRows: [['Balance', 'User’s current account balance']],
            )
        ];
    }
}
```

#### Step 3: Trigger the Export

Immediate download:

```php
return Excel::download(
    new CompanyUsersExport($company, 'this_month'),
    'company-users.xlsx'
);
```

Queued export:

```php
Excel::queue(
    new CompanyUsersExport($company, 'last_30_days'),
    'exports/company.xlsx'
);
```

---

### 📦 Real-World Use Cases

-   Export buttons on Filament table listings
-   Scheduled exports via Laravel scheduler
-   Admin and compliance reporting
-   API-based report generators
-   Multi-sheet financial summaries
-   Dynamic data audits filtered by date range

---

### 📚 Filament Base

### 🔌 Creating a Modular Filament Plugin

Zephyr Shared provides a powerful `BasePlugin` class to streamline plugin development for Filament v3. It enables clean, modular registration of your Pages, Resources, and Widgets — all based on convention.

#### 🚀 What `BasePlugin` Does

-   ✅ Auto-registers Filament components via reflection
-   ✅ Detects correct namespaces and directories based on file location
-   ✅ Keeps plugin definitions DRY and declarative
-   ✅ Supports toggling discovery for Pages, Widgets, and Resources

---

#### 🧰 How to Create a Plugin

To build a plugin for your module (e.g., `Accounts`), extend `BasePlugin`:

```php
namespace ZephyrIt\Accounts\Filament;

use ZephyrIt\FilamentCustomizer\Base\Plugins\BasePlugin;
use Filament\Navigation\NavigationGroup;

class AccountsPlugin extends BasePlugin
{
    public function getId(): string
    {
        return 'accounts';
    }

    protected function navigationGroups(): array
    {
        return [
            NavigationGroup::make()
                ->label(__('accounts::navigations.groups.transactions'))
                ->icon('tabler-report-money')
                ->collapsed(),

            NavigationGroup::make()
                ->label(__('accounts::navigations.groups.configuration'))
                ->icon('tabler-settings')
                ->collapsed(),
        ];
    }
}
```

---

#### 📁 Required Folder Structure

To enable auto-discovery, structure your module as follows:

```
src/
└── Filament/
    ├── Pages/
    ├── Resources/
    ├── Widgets/
    └── AccountsPlugin.php
```

Any components inside `Pages/`, `Resources/`, and `Widgets/` will be automatically registered when the plugin is loaded.

---

#### 🧪 Register in Filament Panel

To enable your plugin in the Filament panel:

```php
use ZephyrIt\Accounts\Filament\AccountsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(AccountsPlugin::make());
}
```

---

#### ⚙️ Optional Discovery Controls

Want to skip registering specific components?

```php
AccountsPlugin::make()
    ->registerPages(false)
    ->registerWidgets(false);
```

---

By using `BasePlugin`, all your Zephyr module plugins remain:

-   ✅ Clean
-   ✅ Auto-wired
-   ✅ Convention-driven
-   ✅ Consistently modular

---

### 📊 Creating a Report Page with Filters

Zephyr Shared includes a `ReportPage` base class to help you rapidly scaffold Filament-powered dashboard pages — complete with filters, permissions, and layout.

---

#### 📋 Example: Custom Ledger Report Page

```php
namespace ZephyrIt\Accounts\Filament\Pages;

use ZephyrIt\Shared\FilamentBase\Pages\ReportPage;
use Filament\Forms\Components\Select;

class LedgerReport extends ReportPage
{
    protected static ?string $navigationIcon = 'tabler-report';
    protected static string $titleKey = 'accounts::navigations.labels.ledger_report';

    public static function getNavigationGroup(): string
    {
        return __('accounts::navigations.groups.reports');
    }

    protected function getFilterFormSchema(): array
    {
        return [
            Select::make('account_id')
                ->label(__('accounts::labels.account'))
                ->relationship('account', 'name')
                ->searchable(),
        ];
    }
}
```

---

### ✅ Built-In Features

When extending `ReportPage`, you automatically get:

-   📅 A date range picker with today’s date as the default
-   🧭 Navigation label and title via `titleKey`
-   🗂 Grouping via `getNavigationGroup()`
-   🧱 Layout managed by Filament’s dashboard base
-   🔍 Optional custom filters via `getFilterFormSchema()`
-   🔐 Role-based access control with `HasPageShield`

---

## 🧠 Global Helper Functions

All helpers are available globally from `src/Helpers/bootstrap_helpers.php`.

---

### 🔢 `FormatHelpers`

```php
numberToIndianFormat(1234567);      // "12,34,567"
numberToWord(123);                  // "one hundred twenty-three"
formatNumberShort(1500000);         // "1.5M"
formatAddButtonLabel('User');       // "Add User"
formatCopyMessage('Email');         // "Copied Email"
```

---

### 🧵 `StringHelpers`

```php
sanitizeAndFormat('John DOE');      // "John Doe"
sanitizeSpecialCharacters('Hey#$', ' ') // "Hey"
normalizeString('Ärgerlich');       // "Argerlich"
```

---

### 🧬 `EnumHelpers`

```php
getEnumValues(StatusEnum::class);   // ['active', 'inactive']
getEnumLabels(StatusEnum::class);   // ['active' => 'Active', ...]
getFilteredEnumStatuses(StatusEnum::class, ['archived']);
```

---

### 📦 `ModuleHelpers`

```php
getActiveModules();                 // ['cms', 'hrm', 'insurance']
getModuleModels('cms');            // ['Page', 'Post']
getAllModelPaths();
getModuleNamespace('cms');         // App\Modules\Cms
clearModuleCache();
```

---

### 🧩 `ModelHelpers`

```php
getAllModelClasses();               // Fully-qualified class names
resolveFieldCast(User::class, 'email');  // 'string'
getLastRecord(User::class, 'created_at'); // Latest created user
```

---

### 🧰 `GeneratorHelpers`

```php
generateUniqueNumber(User::class, 'USR', 'code');  // "USR/2025/001"
generateYears(2000, 2025);                         // [2000, ..., 2025]
getClassesFromDirectory(app_path('Models'), 'App\\Models');
```

---

### 💸 `ApplicationHelpers`

```php
getCurrencySymbol();               // ₹
getDenominationsArray('currency');
transformSupportedLocales();       // ['en' => 'English', ...]
```

---

### ⚙️ `InstallHelper` Functions

> Used in commands and setup logic

```php
install_publish($this, [['tag' => 'toolkit-config']]);
install_publish_files($this, base_path('publish'));
install_publish_migrations($this, base_path('migrations'));
```

---

### 📄 `BaseModel`

A foundational Eloquent model that serves as the base for all Zephyr-IT models.

#### ✅ Key Features

-   Auto-tracks `fillable` changes using `Spatie\Activitylog`
-   Lifecycle control via `HasLifecycleHooks`
-   Easily extended with shared scopes (`HasCommonScopes`)

```php
use ZephyrIt\Shared\Models\BaseModel;
use Spatie\Activitylog\LogOptions;

class Invoice extends BaseModel
{
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
```

---

### 👤 `BaseAuthModel`

Extends Laravel's `Authenticatable` with the same lifecycle behaviors.

#### 🛠 Use It For:

-   `User`, `Admin`, `Staff`, or other models with Laravel Auth

```php
use ZephyrIt\Shared\Models\BaseAuthModel;

class Staff extends BaseAuthModel
{
    // Includes auth + lifecycle logic
}
```

---

## 🧩 Model Concerns

---

### 🧬 `HasLifecycleHooks`

Encapsulates transactional lifecycle control for Eloquent model events.

#### 🔧 Key Features

-   Wraps create/update/save/delete/restore in DB transactions
-   Uses simple overridable methods for each lifecycle phase
-   Supports automatic cascade delete/restore for relationships

---

#### 🔁 Soft Delete Cascade

Enable cascade delete/restore on related models:

```php
protected bool $shouldSyncRelatedSoftDeletes = true;

protected function relatedModelsForSoftDelete(): array
{
    return ['tasks', 'comments'];
}
```

---

#### ✨ Lifecycle Hook Methods

You can override any of the following:

```php
protected function performCreating(): void {}
protected function performAfterCreate(): void {}

protected function performUpdating(): void {}
protected function performAfterUpdate(): void {}

protected function performSaving(): void {}
protected function performAfterSave(): void {}

protected function performDeleting(): void {}
protected function performAfterDelete(): void {}

protected function performRestoring(): void {}
protected function performAfterRestore(): void {}
```

> All methods are optional — define only what you need.

---

#### ✅ Example Usage

```php
use ZephyrIt\Shared\Models\BaseModel;

class Invoice extends BaseModel
{
    protected bool $shouldSyncRelatedSoftDeletes = true;

    protected function relatedModelsForSoftDelete(): array
    {
        return ['lineItems'];
    }

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    protected function performAfterUpdate(): void
    {
        AuditLogger::log("Invoice {$this->id} was updated.");
    }
}
```

---

### 🔍 `HasCommonScopes`

A collection of reusable and schema-safe Eloquent query scopes.

#### 🧠 Purpose

-   Add common filtering, searching, and ordering methods
-   Skip queries safely if the expected column doesn’t exist
-   Ideal for multi-tenant or optional schema scenarios

---

#### 🔐 Column Guard

Every scope uses `hasColumn()` internally:

```php
protected function hasColumn(Builder $query, string $column): bool
```

Ensures the query only runs if the column is present.

---

#### 🧰 Available Scopes

| Scope            | Description                              |
| ---------------- | ---------------------------------------- |
| `active()`       | `is_active = true` (safe fallback)       |
| `inactive()`     | `is_active = false`                      |
| `whereBoolean()` | Generic boolean filter with column check |
| `whereStatus()`  | Filters by a `status` value              |
| `ordered()`      | Order by any column + direction          |
| `recent()`       | Order by `created_at DESC`               |
| `latestFirst()`  | Most recent records by any column        |
| `oldestFirst()`  | Oldest records by any column             |
| `search()`       | LIKE %term% search on a single column    |

---

#### ✅ Example Usage

```php
User::active()
    ->whereStatus('verified')
    ->search('email', 'example.com')
    ->latestFirst()
    ->get();
```

Each scope checks for the existence of the target column — meaning no more "column not found" errors in evolving or modular schemas.

---

## 🔒 `UniqueEncryptedRule`

This custom validation rule ensures **encrypted fields remain unique** across your models — even when stored as ciphertext.

It is designed specifically for cases where traditional `unique:` validation can't compare encrypted values in the database.

---

### ✅ When to Use It

-   You store fields like `email`, `phone`, or `SSN` encrypted in the DB
-   You need to prevent duplicate entries without decrypting every row
-   You want secure validation while editing (excluding the current model)

---

### 🧪 Example Usage

#### Standalone instantiation:

```php
use ZephyrIt\Shared\Rules\UniqueEncryptedRule;

new UniqueEncryptedRule(User::class, 'email', $this->user, 'name');
```

-   `User::class` — target model
-   `'email'` — encrypted column
-   `$this->user` — optional current model (for edit forms)
-   `'name'` — optional display column for error messages

---

#### In a Form Request:

```php
public function rules(): array
{
    return [
        'email' => [
            'required',
            new UniqueEncryptedRule(User::class, 'email', $this->user),
        ],
    ];
}
```

---

### 🌍 Localization Message

Add this to your `resources/lang/en/messages.php` file to customize the validation error:

```php
'unique_encrypted' => 'The :attribute already exists (used by :existing).',
```

> `:existing` will be replaced with the value from the optional "display column" (like `name` or `email`)

---

## 🌱 Smart Seeding System with `BaseSeeder`

The `BaseSeeder` class provides a **safe, idempotent, and environment-aware seeding entrypoint** for Zephyr-IT applications. It is used at the top level (`DatabaseSeeder`) to orchestrate and manage all module and development seeders.

> ❗ Module-specific seeders (e.g. `AccountsSeeder`, `UserSeeder`) should extend the default `Illuminate\Database\Seeder`, not `BaseSeeder`.

---

### ✅ Key Features

-   📓 Logs seeder runs in the `seed_log` table to prevent duplicate execution
-   ♻️ Automatically suppresses `spatie/laravel-activitylog` during seeding
-   ⚙️ Smart `run()` logic that separates production vs. development seeders
-   🔁 Nested seeding supported via Laravel’s native `$this->call()` system

---

### 🔧 Setup

Ensure the `seed_log` tracking table exists by running:

```bash
php artisan vendor:publish --tag=shared-migrations
php artisan migrate
```

---

### 🧪 Actual Usage Pattern

#### ✅ `DatabaseSeeder` using `BaseSeeder`

```php
namespace Database\Seeders;

use ZephyrIt\Shared\Support\BaseSeeder;
use ZephyrIt\Shared\Database\Seeders\WorldDatabaseSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->command->info('🔄 Starting database seeding...');

        $baseSeeders = [
            WorldDatabaseSeeder::class,
            ShieldSeeder::class,
            UserSeeder::class,
            PlanSeeder::class,
            FeatureSeeder::class,
            ProductSeeder::class,
        ];

        $developmentSeeders = [
            TenantSeeder::class,
            // Add other dev/test seeders here
        ];

        $seeders = app()->isProduction()
            ? $baseSeeders
            : array_merge($baseSeeders, $developmentSeeders);

        $this->call($seeders);

        $this->command->info('✔ Database seeding completed.');
    }
}
```

---

#### ❌ `AccountsSeeder` — does NOT extend `BaseSeeder`

```php
namespace ZephyrIt\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccountTypeSeeder::class,
            DefaultAccountsSeeder::class,
        ]);
    }
}
```

This pattern keeps your module seeders lightweight and clean, using Laravel's core `Seeder` class.

---

### ✅ Running Seeders

To run shared or environment-specific seeders via `DatabaseSeeder`:

```bash
php artisan db:seed
```

To run a specific one:

```bash
php artisan db:seed --class="ZephyrIt\Shared\Database\Seeders\WorldDatabaseSeeder"
```

---

### 🔁 Benefits of `BaseSeeder`

-   🛡 Prevents duplicate seed execution using `seed_log`
-   🧘 Auto-suppresses activity logs during seeding
-   🔄 Restores log state even if an exception occurs
-   🧵 Supports full project orchestration without touching module logic

---

## 🎨 Useful Shared Traits

These traits help you keep your services, charts, and multi-tenant logic **modular, DRY, and production-ready** across Zephyr-IT modules.

---

### 1️⃣ `ColorPaletteTrait`

Dynamically generate a consistent color palette for charts (e.g. Chart.js, ApexCharts) with fallback to random RGBA values.

#### ✅ Features

-   Returns an array of RGBA strings with optional opacity
-   Ensures visual consistency using preconfigured base colors
-   Auto-fills additional entries with randomized color variants

#### ✅ Example Usage

```php
use ZephyrIt\Shared\Traits\ColorPaletteTrait;

class RevenueChartService
{
    use ColorPaletteTrait;

    public function getChartColors(int $count): array
    {
        return $this->getColors($count);
    }
}
```

#### 🖼️ Sample Output

```php
[
    'backgroundColor' => ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', ...],
    'borderColor' => ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', ...],
]
```

This array can be injected directly into chart configuration objects.

---

### 2️⃣ `DeterminesTenant`

Resolves the current tenant based on the domain, using the `stancl/tenancy` package.

#### ✅ Features

-   Reads the current domain via `request()->getHost()`
-   Queries the `domains` table to find the matching `Tenant`
-   Returns the resolved tenant model — or `null` if not found

#### ✅ Example Usage

```php
use ZephyrIt\Shared\Traits\DeterminesTenant;

class TenantAwareReportService
{
    use DeterminesTenant;

    public function getTenantName(): ?string
    {
        return optional($this->determineTenant())->name;
    }
}
```

#### 💡 Safe for CLI Use

Returns `null` if `php artisan` is running (e.g. in queue workers, commands, or test runners).

---

### 3️⃣ `HasDateRangeParser`

A smart, flexible parser for handling UI-driven or backend-passed date ranges in a clean, uniform format.

#### ✅ Example Usage

```php
[$start, $end] = $this->parseDateRange('2024-05-01 - 2024-05-31');

[$from, $to] = $this->parseDateRangeAsStrings(['01/05/2024', '31/05/2024']);
```

#### 🗂️ Supported Input Formats

-   `'2024-05-01 - 2024-05-31'` (string range)
-   `['2024-05-01', '2024-05-31']` (array of ISO dates)
-   `['01/05/2024', '31/05/2024']` (array of D/M/Y format)

This trait is extremely useful for:

-   Report filtering
-   Dashboard widgets
-   Scheduled exports
-   API endpoints expecting flexible date inputs

---

## 📊 Dynamic Metrics Engine

The `DynamicMetricsTrait` provides a flexible, powerful way to **aggregate, filter, and chart dynamic data metrics** across any set of models. It is ideal for use in Filament dashboards, admin reports, and time-based insights.

---

### ✅ Key Features

-   Aggregate metrics (sum, count, list, avg) over time
-   Group by single or nested keys (e.g., date → user → status)
-   Chart-ready structure with interval-aware logic (daily/weekly/monthly)
-   Optional `Closure`-based filters and dynamic groupings
-   Works across multiple models in a single call

---

## 🚀 How to Use

### 1️⃣ `fetchDynamicMetricsData()`

Fetches grouped, nested metric results across a time range.

```php
$results = $this->fetchDynamicMetricsData(
    models: [Invoice::class, Payment::class],
    metrics: [
        'total_amount' => ['amount', 'sum'],
        'avg_settlement_days' => ['registered_date', 'avg', fn ($group) => $group->whereNotNull('settlement_date')],
    ],
    startDate: now()->subMonth(),
    endDate: now(),
    groupByColumn: fn ($item) => [$item->created_at->format('Y-m'), $item->status]
);
```

🧠 Output will be grouped by month + status, and metrics are aggregated accordingly.

---

### 2️⃣ `getMetricChartData()`

Returns an array of values suitable for charting over time (daily, weekly, monthly).

```php
$chartData = $this->getMetricChartData(
    models: [Invoice::class],
    metricType: 'sum',
    column: 'amount',
    startDate: now()->subMonths(3),
    endDate: now()
);
```

Returns something like:

```php
[1200, 1500, 1800, 2100] // One value per interval
```

---

### 3️⃣ `getMetricData()`

Calculates a final, formatted metric (e.g. grand total or count) over time and multiple models.

```php
$totalRevenue = $this->getMetricData(
    models: [Invoice::class],
    metricType: 'sum',
    column: 'amount',
    startDate: now()->subQuarter(),
    endDate: now()
);
```

✅ Automatically returns formatted string using `numberToIndianFormat()` helper.

---

### 🧠 Advanced Techniques

#### 📆 Auto Interval Selection

The trait automatically picks an interval based on date range:

-   ≤ 7 days → `daily`
-   ≤ 90 days → `weekly`
-   ≤ 365 days → `monthly`
-   > 365 days → `quarterly`

Override logic manually by customizing the `determineDateInterval()` method.

---

#### 🧪 Conditional Metrics

Use closures or arrays to apply conditional logic to a group:

```php
'only_pending' => ['amount', 'sum', fn ($group) => $group->where('status', 'pending')]
```

Or:

```php
[
    'field' => 'status',
    'operator' => '=',
    'value' => 'pending',
]
```

---

#### 🧬 Nested Grouping

Support multi-dimensional grouping via `groupByColumn` closure:

```php
fn ($item) => [$item->created_at->format('Y-m'), $item->user_id]
```

---

## 🧰 Helper Methods (Internal Use)

| Method                      | Description                                   |
| --------------------------- | --------------------------------------------- |
| `prepareDateRange()`        | Defaults to current month if no date provided |
| `generateDateRange()`       | Builds list of intervals for aggregation      |
| `calculateIntervalMetric()` | Aggregates values within each interval        |
| `deepMerge()`               | Recursively merges numeric/array values       |
| `recursiveGroupBy()`        | Handles multi-dimensional grouping            |
| `processMetricGroup()`      | Applies aggregate functions on grouped data   |

---

### 📦 Real-World Use Cases

-   Filament dashboard KPIs with date filters
-   Line/bar/pie charts with `Chart.js` or `ApexCharts`
-   Admin exports of grouped metrics (per user, per status, etc.)
-   Aggregating metrics across multi-model data lakes (e.g. `Invoice`, `Payment`, `Transaction`)

---

## 🧪 Testing

Install PHP dependencies and run the test suite with [Pest](https://pestphp.com/):

```bash
composer install
composer test
```

The tests are configured via [`phpunit.xml.dist`](phpunit.xml.dist). This ensures that all traits, resources, commands, and integrations function as expected across environments.

To verify coding standards, run the style fixer:

```bash
composer lint
```

This uses PHP CS Fixer and Laravel Pint to enforce consistent formatting.

---

## 📄 Changelog

Refer to the [CHANGELOG](CHANGELOG.md) for a full history of updates, bug fixes, and feature releases.

---

## 🤝 Contributing

We welcome contributions! Please review our [CONTRIBUTING](CONTRIBUTING.md) guide before submitting issues or pull requests.

---

## 🔐 Security

If you discover any security vulnerabilities, please refer to our [security policy](../../security/policy) for responsible disclosure guidelines.

---

## 🧠 Credits

-   [@abbasmashaddy72](https://github.com/abbasmashaddy72) – Project Lead
-   [Zephyr-IT Team](https://github.com/zephyr-it) – Core Maintainers
-   [All Contributors](../../contributors) – Special thanks to everyone who has contributed!

---

## 📜 License

This project is open-source software licensed under the [MIT License](LICENSE.md).
