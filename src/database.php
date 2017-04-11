<?php

namespace Vendi\Shared;
/**
 *
 * History:
 *
 * 1.0.0 - Initial version
 * 1.0.1 - When dropping a table possibly disable foreight key checks.
 * 1.1.0 - Better support for temporary tables
 *
 * @version  1.0.1
 */

/**
 * Helper class shared across all Vendi-controlled properties for database work.
 */
class database
{
    private function __construct()
    {
    }

    /**
     * Drop a table if it already exists.
     *
     * @param  string $full_table_name    The table to drop.
     * @param  bool   $is_temporary_table Whether or not the table is a
     *                                    temporary table.
     */
    public static function drop_table_if_exists( string $full_table_name, bool $is_temporary_table = false )
    {
        global $wpdb;

        $full_table_name = self::make_db_object_name_safe( $full_table_name );

        $key = $is_temporary_table ? 'TEMPORARY' : '';

        $sql =   "DROP {$key} TABLE IF EXISTS {$full_table_name};";

        // TODO: Not sure if we need this anymore
        // if( $disable_foreign_key_checks )
        // {
        //     $sql =   "SET FOREIGN_KEY_CHECKS=0; {$sql}; SET FOREIGN_KEY_CHECKS=1;";
        // }

        $last_hide     = $wpdb->hide_errors();
        $last_suppress = $wpdb->suppress_errors();

        $result = $wpdb->query( $sql );

        $wpdb->show_errors( $last_hide );
        $wpdb->suppress_errors( $last_suppress );

        if( false === $result )
        {
            throw new \Exception( sprintf( 'There was an error dropping the table %1$s. The error was [%2$s].', esc_html( $full_table_table ), $wpdb->last_error ) );
        }
    }

    /**
     * Drop a table if it already exists.
     *
     * Time will tell if this is a good idea or bad.
     *
     * @since  1.0.0
     * @since  1.0.1 Add $disable_foreign_key_checks
     * @since  1.1.0 Better support for temporary tables.
     *
     * @param  string $table_name The table name to possibly drop.
     */
    public static function maybe_drop_old_table( $full_table_name, $disable_foreign_key_checks = false )
    {
        _deprecated_function( 'maybe_drop_old_table', '1.1.0', 'drop_table_if_exists' );

        self::drop_table_if_exists( $full_table_name );
    }

    /**
     * Check if a table exists in the current database.
     *
     * This check works with both regular tables as well as TEMPDB tables. Do
     * not change this method to use INFORMATION_SCHEMA unless you specifically
     * target a version of MySQL/MariaDB that support it.
     *
     * @since  1.0.0
     *
     * @param  string  $table_name The table name to check.
     * @return boolean             True if the table exists with the exact name, otherwise false.
     */
    public static function does_table_exist( $full_table_table )
    {
        global $wpdb;

        $full_table_table = self::make_db_object_name_safe( $full_table_table );

        //Hide internal errors because this query will fail if the table
        //does not exist
        $last_hide     = $wpdb->hide_errors();
        $last_suppress = $wpdb->suppress_errors();
        $query = "SHOW FULL COLUMNS FROM {$full_table_table}";

        $result = $wpdb->query( $query );

        $wpdb->show_errors( $last_hide );
        $wpdb->suppress_errors( $last_suppress );

        return false !== $result;
    }

    /**
     * Check if a table has a specific column.
     *
     * This method does not check if the table already exists.
     *
     * @since  1.0.0
     *
     * @param  string  $table_name   The table name to check.
     * @param  string  $column_name  The column name to check.
     * @return boolean               True if the table has a column with that name, otherwise false.
     */
    public static function does_column_exist( $full_table_table, $column_name )
    {
        global $wpdb;

        $sql = "SHOW FULL COLUMNS FROM `$full_table_table` WHERE `field`='$column_name';";

        return $wpdb->get_results( $sql ) ? true : false;
    }

    /**
     * Add a new varchar column to the supplied table if it doesn't exist already.
     *
     *
     * @since  1.0.0
     *
     * @param  string               $table_name      The table name to add the column to.
     * @param  string               $column_name     The column name to add.
     * @param  int                  $varchar_length  The maximum length of the varchar column.
     * @return boolean\|WP_Error                     True if the column was added, false if it wasn't,
     *                                               \WP_Error if the table does not exist.
     */
    public static function maybe_add_column_to_table_varchar( $full_table_table, $column_name, $varchar_length )
    {
        global $wpdb;

        if( ! self::does_table_exist( $full_table_table ) )
        {
            return new \WP_Error( 'database', sprintf( 'Attempt to add column %1$s to table %2$s failed because the table does not exist', esc_html( $column_name ), esc_html( $full_table_table ) ) );
        }

        if( self::does_column_exist( $full_table_table, $column_name ) )
        {
            return false;
        }

        $sql = "ALTER TABLE `$table` ADD COLUMN `$column_name` varchar($varchar_length) NULL DEFAULT ''";

        return true === $wpdb->query( $sql );
    }

    public static function does_table_have_primary_key( $full_table_table )
    {
        global $wpdb;

        return $wpdb->get_var( "SHOW INDEX FROM `$full_table_table` WHERE Key_name = 'PRIMARY';" ) ? true : false;
    }

    public static function drop_primary_key_from_table( $full_table_table )
    {
        global $wpdb;

        $sql =  "ALTER TABLE `$full_table_table` DROP PRIMARY KEY;";

        $wpdb->query( $sql );
    }

    public static function add_primary_key_to_table( $full_table_table, $column_name )
    {
        global $wpdb;

        $sql =  "ALTER TABLE `$full_table_table` ADD PRIMARY KEY (`$column_name`);";

        $wpdb->query( $sql );
    }

    public static function drop_column_from_table( $full_table_table, $column_name )
    {
        global $wpdb;

        $sql =  "ALTER TABLE `$full_table_table` DROP COLUMN `$column_name`;";

        $wpdb->query( $sql );
    }

    /**
     * Surround the name with backticks if needed .
     *
     * @param  string $name The database object name to surround.
     * @return string
     */
    public static function make_db_object_name_safe( string $name ) : string
    {
        $name = trim( $name, '`' );

        if( false !== strpos( $name, '`' ) )
        {
            throw new \Exception( sprintf( __( 'Database identifier %1 internally contained a backtick.', 'c4r' ), esc_html( $name ) ) );
        }

        return '`' . $name . '`';
    }
}
