<?php

namespace Blasttech\WherePlus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * Trait WherePlusTrait
 *
 * @method $this whereOrEmptyOrNull(string $column, string $value = '', string $ignore = null)
 * @method $this whereInColumn(string $column, string $value)
 * @method $this whereNotInColumn(string $column, string $value)
 * @method $this whereStarts(string $column, string $value)
 * @method $this whereNotStarts(string $column, string $value)
 * @method $this whereEnds(string $column, string $value)
 * @method $this whereNotEnds(string $column, string $value)
 * @method $this whereContains(string $column, string $value)
 * @method $this whereNotContains(string $column, string $value)
 * @method $this whereIfNull(string $column, $ifNull, $operator = null, $value = null, $boolean = 'and')
 *
 * @package Blasttech\WherePlus
 */
trait WherePlusTrait
{
    /**
     * If $value = '' add where ($column is null or column = '') statement
     * If $value != '' add where $column = $value statement
     *
     * @param static|$this|Builder $query
     * @param string|array $column
     * @param string $value
     * @param string $ignore - if value = ignore, don't search on this column
     *
     * @return static|$this|Builder
     */
    public function scopeWhereOrEmptyOrNull(Builder $query, $column, $value = '', $ignore = null)
    {
        if (is_array($column)) {
            foreach ($column as $where_col => $where_val) {
                $query->whereOrEmptyOrNull($where_col, $where_val, $ignore);
            }

            return $query;
        }

        if (!is_null($ignore) && $value != $ignore) {
            if ($value == '') {
                return $query->where(function ($query) use ($column) {
                    /* @var Builder $query */
                    $query->where($column, '')
                        ->orWhereNull($column);
                });
            }

            $query->where($column, $value);
        }

        return $query;
    }

    /**
     * Scope a query to only include records where $value in $column
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereInColumn(Builder $query, $column, $value)
    {
        return $query->whereRaw('CONCAT(\',\', ' . $this->addTicks($column) . ', \',\') LIKE \'%,' . $value . ',%\'');
    }

    /**
     * Add ticks to a table and column
     *
     * @param string $column
     *
     * @return string
     */
    private function addTicks($column)
    {
        if (preg_match('/^[0-9a-zA-Z\.]*$/', $column)) {
            return '`' . str_replace(['`', '.'], ['', '`.`'], $column) . '`';
        }

        return $column;
    }

    /**
     * Scope a query to exclude records where $value in $column
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereNotInColumn(Builder $query, $column, $value)
    {
        return $query->whereRaw(
            'CONCAT(\',\', ' . $this->addTicks($column) . ', \',\') NOT LIKE \'%,' . $value . ',%\''
        );
    }

    /**
     * Scope a query to only include records where $column starts with $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereStarts(Builder $query, $column, $value)
    {
        return $query->where($column, 'LIKE', $value . '%');
    }

    /**
     * Scope a query to only include records where $column doesn't start with $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereNotStarts(Builder $query, $column, $value)
    {
        return $query->where($column, 'NOT LIKE', $value . '%');
    }

    /**
     * Scope a query to only include records where $column ends with $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereEnds(Builder $query, $column, $value)
    {
        return $query->where($column, 'LIKE', '%' . $value);
    }

    /**
     * Scope a query to only include records where $column doesn't end with $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereNotEnds(Builder $query, $column, $value)
    {
        return $query->where($column, 'NOT LIKE', '%' . $value);
    }

    /**
     * Scope a query to only include records where $column contains $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereContains(Builder $query, $column, $value)
    {
        return $query->where($column, 'LIKE', '%' . $value . '%');
    }

    /**
     * Scope a query to only include records where $column doesn't contain $value
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string $value
     *
     * @return static|$this|Builder
     */
    public function scopeWhereNotContains(Builder $query, $column, $value)
    {
        return $query->where($column, 'NOT LIKE', '%' . $value . '%');
    }

    /**
     * Scope a query to include an IFNULL in a where statement
     * $ifNull will be the result if $column is null
     *
     * @param static|$this|Builder $query
     * @param string $column
     * @param string|Expression $ifNull
     * @param null $operator
     * @param null $value
     * @param string $boolean
     *
     * @return static|$this|Builder
     */
    public function scopeWhereIfNull($query, $column, $ifNull, $operator = null, $value = null, $boolean = 'and')
    {
        $bind = (!$ifNull instanceof Expression);

        return $query->where(
            DB::raw('IFNULL(' . $column . ', ' . ($bind ? '?' : $ifNull) . ')'),
            $operator,
            ($bind ? [$ifNull, $value] : $value),
            $boolean
        );
    }
}
