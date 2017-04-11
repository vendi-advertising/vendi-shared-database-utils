<?php

use Vendi\Shared\database;

class test_schema_v2 extends \PHPUnit_Framework_TestCase
{

    // /**
    //  * @covers Vendi\Shared\database::maybe_drop_old_table
    //  */
    // public function test_maybe_drop_old_table()
    // {
    //     $this->assertTrue( true );
    // }

    /**
     * @covers Vendi\Shared\database::__construct
     */
    public function test_constructor()
    {
        $reflection = new \ReflectionClass('\Vendi\Shared\database');
        $constructor = $reflection->getConstructor();
        $this->assertFalse( $constructor->isPublic() );
    }

    /**
     * @covers Vendi\Shared\database::make_db_object_name_safe
     */
    public function test_make_db_object_name_safe()
    {
        $this->assertSame( '`alpha`', database::make_db_object_name_safe( 'alpha' ) );
        $this->assertSame( '`alpha`', database::make_db_object_name_safe( '`alpha`' ) );
    }

    /**
     * @covers Vendi\Shared\database::make_db_object_name_safe
     * @expectedException Exception
     */
    public function test_make_db_object_name_safe_with_internal_backtick()
    {
        database::make_db_object_name_safe( '`al`pha`' );
    }

    /**
     * @covers Vendi\Shared\database::does_table_exist
     * @covers Vendi\Shared\database::drop_table_if_exists
     */
    public function test_does_table_exist()
    {
        global $wpdb;

        foreach( [ true, false ] as $is_temporary_table )
        {
            //Cleanup old tests, just in case
            database::drop_table_if_exists( 'CHEESE2', $is_temporary_table );

            //Table shouldn't exists
            $this->assertFalse( database::does_table_exist( 'CHEESE2' ) );

            //Create it
            $wpdb->query( 'CREATE TABLE CHEESE2( `column` int );' );

            //Should now exist
            $this->assertTrue( database::does_table_exist( 'CHEESE2' ) );

            //Cleanup
            database::drop_table_if_exists( 'CHEESE2', $is_temporary_table );
        }
    }

    /**
     * @covers Vendi\Shared\database::maybe_drop_old_table
     * @expectedException Exception
     */
    public function test_maybe_drop_old_table_deprecated()
    {
        database::maybe_drop_old_table( 'CHEESE2' );
    }

    /**
     * @covers Vendi\Shared\database::maybe_drop_old_table
     */
    public function test_maybe_drop_old_table()
    {
        add_filter( 'deprecated_function_trigger_error', [ $this, '__hide_deprecated_function_trigger_error' ] );

        database::maybe_drop_old_table( 'CHEESE2' );

        remove_filter( 'deprecated_function_trigger_error', [ $this, '__hide_deprecated_function_trigger_error' ] );
    }

    public function __hide_deprecated_function_trigger_error()
    {
        return false;
    }

}


    /**
     * @covers Vendi\HealthTradition\Click4Rates\Database\schema_v2::drop_all_foreign_keys
     * @expectedException Vendi\HealthTradition\Click4Rates\Exceptions\GeneralClick4RatesException
     */
