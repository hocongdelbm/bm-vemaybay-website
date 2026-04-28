<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once 'custom/entrypoints/entryClass.php';

/**
 * Class entryExportClass
 *
 * Exports flight booking records as SQL INSERT statements,
 * including all related itineraries, details, and passengers.
 */
class entryExportClass extends entryClass
{
    private const MAX_BOOKINGS_PER_REQUEST = 10;

    private const BOOKING_TABLE = 'ec_flight_bookings';

    private const RELATED_TABLES = [
        'ec_booking_itineraries',
        'ec_booking_details',
        'ec_booking_passengers',
    ];

    private array $columnTypesByTable = [];

    /**
     * Export bookings by name and return SQL INSERT statements.
     *
     * @param  string[] $bookingNames  List of booking names to export
     * @return string                  SQL INSERT statements or error message
     */
    public function exportBookings(array $bookingNames = []): string
    {
        try {
            if (count($bookingNames) > self::MAX_BOOKINGS_PER_REQUEST) {
                return 'Only a maximum of ' . self::MAX_BOOKINGS_PER_REQUEST . ' bookings are supported per request';
            }

            $output = '';
            foreach ($bookingNames as $bookingName) {
                $bookingName = trim($bookingName);
                if (empty($bookingName)) {
                    continue;
                }

                $output .= $this->exportSingleBooking($bookingName);
            }

            return $output;
        } catch (Throwable $th) {
            return "{$th->getMessage()} on line {$th->getLine()} in {$th->getFile()}";
        }
    }

    // -------------------------------------------------------------------------
    // Private methods
    // -------------------------------------------------------------------------

    /**
     * Export a single booking and all its related records.
     */
    private function exportSingleBooking(string $bookingName): string
    {
        $booking = $this->findBookingByName($bookingName);
        if (empty($booking['id'])) {
            return '';
        }

        $output  = $this->buildInsertStatement(self::BOOKING_TABLE, $booking);
        $output .= $this->exportRelatedTables($booking['id']);
        $output .= "\n";

        return $output;
    }

    /**
     * Export all related tables for a given booking ID.
     */
    private function exportRelatedTables(string $bookingId): string
    {
        global $db;

        $output = '';
        foreach (self::RELATED_TABLES as $table) {
            $sql = "SELECT * FROM {$table} WHERE booking_id = '" . $db->quote($bookingId) . "'";
            $res = $db->query($sql);

            while ($row = $db->fetchByAssoc($res)) {
                $output .= $this->buildInsertStatement($table, $row);
            }
        }

        return $output;
    }

    /**
     * Build a single SQL INSERT statement from a table name and a data row.
     * @param  array<string, mixed> $row
     * @param  string $table
     * @return string
     */
    private function buildInsertStatement(string $table, array $row): string
    {
        $columns = implode(', ', array_keys($row));
        $values  = $this->buildValuesList($table, $row);

        return "INSERT INTO {$table} ({$columns}) VALUES ({$values});\n";
    }

    /**
     * Build a comma-separated list of SQL-safe values for all columns in a row.
     *
     * @param  array<string, mixed> $row
     */
    private function buildValuesList(string $table, array $row): string
    {
        $formatted = [];
        foreach ($row as $column => $value) {
            $columnType   = $this->getColumnType($table, $column);
            $formatted[]  = $this->formatValue($value, $columnType);
        }

        return implode(', ', $formatted);
    }

    /**
     * Format a single value according to its SQL column type.
     */
    private function formatValue($value, string $columnType): string
    {
        global $db;

        $columnType = strtolower($columnType);
        $value      = $value === null ? null : trim((string) $value);

        if ($value === null || $value === '') {
            return 'NULL';
        }

        if ($this->isDateType($columnType)) {
            return $this->formatDateValue($value, $columnType);
        }

        return "'" . $db->quote($value) . "'";
    }

    /**
     * Format a date/datetime/timestamp value as a SQL string, or NULL if invalid.
     */
    private function formatDateValue(string $value, string $columnType): string
    {
        global $db;

        // Treat all-zero dates (e.g. 0000-00-00) as NULL
        if (preg_match('/^0{4}-0{2}-0{2}(?:\s+0{2}:0{2}:0{2})?$/', $value) === 1) {
            return 'NULL';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return 'NULL';
        }

        $isDateOnly = preg_match('/^date\b/', $columnType) === 1
            && preg_match('/^(datetime|timestamp)\b/', $columnType) !== 1;

        $formatted = $isDateOnly
            ? date('Y-m-d', $timestamp)
            : date('Y-m-d H:i:s', $timestamp);

        return "'" . $db->quote($formatted) . "'";
    }

    /**
     * Return true if the column type is date, datetime, or timestamp.
     */
    private function isDateType(string $columnType): bool
    {
        return preg_match('/^(date|datetime|timestamp)\b/', $columnType) === 1;
    }

    /**
     * Get the SQL type of a column, with per-table caching.
     */
    private function getColumnType(string $table, string $column): string
    {
        $this->loadColumnTypes($table);

        return $this->columnTypesByTable[$table][$column] ?? '';
    }

    /**
     * Load and cache column metadata for a table if not already loaded.
     */
    private function loadColumnTypes(string $table): void
    {
        if (isset($this->columnTypesByTable[$table])) {
            return;
        }

        global $db;

        $this->columnTypesByTable[$table] = [];
        $res = $db->query("SHOW COLUMNS FROM {$table}");
        while ($row = $db->fetchByAssoc($res)) {
            $this->columnTypesByTable[$table][$row['Field']] = strtolower($row['Type'] ?? '');
        }
    }

    /**
     * Find a booking record by its name.
     *
     * @return array<string, mixed>
     */
    private function findBookingByName(string $bookingName): array
    {
        global $db;

        $sql = "SELECT * FROM " . self::BOOKING_TABLE . " WHERE name = '" . $db->quote($bookingName) . "'";
        $res = $db->query($sql);

        return $db->fetchByAssoc($res) ?: [];
    }
}
