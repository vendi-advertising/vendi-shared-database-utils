<?php
/*
Plugin Name: Vendi Shared Database Utility Class
Description: Helper class shared across all Vendi-controlled properties for database work.
Version: 1.0.0
Author: Vendi
*/

namespace Vendi\Shared;

if( class_exists( '\Vendi\Shared\database' ) )
{
    return;
}

/**
 *
 * History:
 *
 * 1.0.0 - Initial version
 * 1.0.1 - When dropping a table possibly disable foreight key checks.
 * 
 * @version  1.0.1
 */

class database
{
    private function __construct()
    {

    }
    /**
     * Drop a table if it already exists.
     *
     * @since  1.0.0
     *
     * @since  1.0.1 Add $disable_foreign_key_checks
     * 
     * @param  string $table_name The table name to possibly drop.
     */
    public static function maybe_drop_old_table( $full_table_table, $disable_foreign_key_checks = false )
    {
        global $wpdb;

        if( $disable_foreign_key_checks )
        {
            $sql =   "SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS `$full_table_table`; SET FOREIGN_KEY_CHECKS=1;";
        }
        else
        {
            $sql =   "DROP TABLE IF EXISTS `$full_table_table`;";
        }

        $result = $wpdb->query( $sql );
    }

    /**
     * Check if a table exists in the current database.
     *
     * @since  1.0.0
     * 
     * @param  string  $table_name The table name to check.
     * @return boolean             True if the table exists with the exact name, otherwise false.
     */
    public static function does_table_exist( $full_table_table )
    {
        global $wpdb;

        $query = $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->esc_like( $full_table_table ) );

        return $wpdb->get_var( $query ) === $full_table_table;
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
}
