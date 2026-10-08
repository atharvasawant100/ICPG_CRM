<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Driver-aware SQL date expressions.
 *
 * The dashboard and leads dashboards group by month using DATE_FORMAT(), which
 * exists on MySQL/MariaDB but not on SQLite. Without this helper those pages
 * cannot be exercised by the test suite, which runs on sqlite.
 *
 * On MySQL/MariaDB this returns exactly the expression used before, so the
 * production SQL is byte-identical.
 */
final class MonthExpression
{
    /**
     * An expression yielding 'YYYY-MM' for the given column.
     */
    public static function for(string $column): string
    {
        if (self::isMySql()) {
            return "DATE_FORMAT({$column}, '%Y-%m')";
        }

        // SQLite (and anything else): strftime('%Y-%m', column)
        return "strftime('%Y-%m', {$column})";
    }

    /**
     * GREATEST() with an SQLite equivalent (MAX()).
     */
    public static function greatest(string $a, string $b): string
    {
        return self::isMySql() ? "GREATEST({$a}, {$b})" : "MAX({$a}, {$b})";
    }

    private static function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
}