<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UniqueEncryptedRule implements ValidationRule
{
    protected string $modelClass;

    protected string $column;

    protected ?Model $ignorable;

    protected string $displayColumn;

    /**
     * @param  string  $modelClass  The model class to query against
     * @param  string  $column  The column to check uniqueness on
     * @param  Model|null  $ignorable  A model to ignore during uniqueness check
     * @param  string|null  $displayColumn  Optional column to display in error message (default: 'name')
     */
    public function __construct(
        string $modelClass,
        string $column,
        ?Model $ignorable = null,
        ?string $displayColumn = 'name'
    ) {
        $this->modelClass = $modelClass;
        $this->column = $column;
        $this->ignorable = $ignorable;
        $this->displayColumn = $displayColumn ?? 'name';
    }

    /**
     * Run the validation rule.
     *
     * Validates whether a given value is unique within an encrypted column,
     * considering a potential record to ignore.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        if (! is_subclass_of($this->modelClass, Model::class)) {
            throw new InvalidArgumentException("The class {$this->modelClass} is not a valid Eloquent model.");
        }

        $query = $this->modelClass::query()
            ->when($this->ignorable, fn ($q) => $q->whereKeyNot($this->ignorable->getKey()))
            ->get([$this->column, $this->displayColumn]);

        $matched = $query->first(fn ($row) => $row->{$this->column} === $value);

        if ($matched) {
            $formattedAttribute = Str::of($attribute)->afterLast('.')->replace('_', ' ')->lcfirst();

            $params = [
                'attribute' => $formattedAttribute,
                'existing' => $matched->{$this->displayColumn} ?? null,
            ];

            $fail(__('shared::messages.rules.unique_encrypted', $params));
        }
    }
}
