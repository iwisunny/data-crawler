<?php
/**
 * Summary
 * Description
 * @package
 * @author    Wang Xi <iwisunny@gmail.com>
 * @copyright (C) 2017 Wang Xi. All rights reserved.
 * @version 0.1
 * Date 17-2-20
 */

namespace Tests;

use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testDbConnection()
    {
        $capsule=new Capsule();

        $db_conf=require __DIR__.'/../config/database.php';

        $capsule->addConnection($db_conf);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

//        dump(get_class_methods($capsule));

        //use schema builder
        Capsule::schema()->create('from_sites', function($table){
            $table->increments('id');
            $table->string('site_name');
            $table->string('fetch_url');
            $table->timestamps();
        });

        Capsule::schema()->dropIfExists('from_sites');

//        Capsule::schema()->table('from_sites', function($table){
//           $table->string('site_name', 100)->change();
//        });
    }
}
