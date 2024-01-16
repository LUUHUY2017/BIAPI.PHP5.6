<?php

use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        //pages
        DB::table('pages')->truncate();
        
        $i=1;
        DB::table('pages')->insert([
            'id' => 1,
            'page_name' => 'pages',
            'page_detail' => 'Pages',
            'page_order' => $i++
        ]);
        DB::table('pages')->insert([
            'id' => 2,
            'page_name' => 'permissions',
            'page_detail' => 'Permissions',
            'page_order' => $i++
        ]);
        DB::table('pages')->insert([
            'id' => 3,
            'page_name' => 'users',
            'page_detail' => 'Users',
            'page_order' => $i++
        ]);
        DB::table('pages')->insert([
            'id' => 4,
            'page_name' => 'organizations',
            'page_detail' => 'Organizations',
            'page_order' => $i++
        ]);
        DB::table('pages')->insert([
            'id' => 5,
            'page_name' => 'sites',
            'page_detail' => 'Sites',
            'page_order' => $i++
        ]);
		DB::table('pages')->insert([
            'id' => 6,
            'page_name' => 'location_type',
            'page_detail' => 'Location Type',
            'page_order' => $i++
        ]);
		DB::table('pages')->insert([
            'id' => 7,
            'page_name' => 'locations',
            'page_detail' => 'Locations',
            'page_order' => $i++
        ]);
        DB::table('pages')->insert([
            'id' => 8,
            'page_name' => 'roles',
            'page_detail' => 'Roles',
            'page_order' => $i++
        ]);


        //page_permission
        DB::table('page_permission')->truncate();
    	
        DB::table('page_permission')->insert([
            'page_id' => '1',
            'permission_id' => '1'
        ]);
        DB::table('page_permission')->insert([
            'page_id' => '1',
            'permission_id' => '2'
        ]);
        DB::table('page_permission')->insert([
            'page_id' => '1',
            'permission_id' => '3'
        ]);
        DB::table('page_permission')->insert([
            'page_id' => '1',
            'permission_id' => '4'
        ]);
        DB::table('page_permission')->insert([
            'page_id' => '1',
            'permission_id' => '5'
        ]);


        

        //

        DB::table('permissions')->truncate();
    	
        DB::table('permissions')->insert([
            'id' => 1,
            'permission_name' => 'view',
            'permission_detail' => 'View'
        ]);
        DB::table('permissions')->insert([
            'id' => 2,
            'permission_name' => 'add',
            'permission_detail' => 'Add'
        ]);
        DB::table('permissions')->insert([
            'id' => 3,
            'permission_name' => 'update',
            'permission_detail' => 'Update'
        ]);
        DB::table('permissions')->insert([
            'id' => 4,
            'permission_name' => 'delete',
            'permission_detail' => 'Delete'
        ]);
        DB::table('permissions')->insert([
            'id' => 5,
            'permission_name' => 'print',
            'permission_detail' => 'Print'
        ]);
        DB::table('permissions')->insert([
            'id' => 6,
            'permission_name' => 'export',
            'permission_detail' => 'Export'
        ]);
        DB::table('permissions')->insert([
            'id' => 7,
            'permission_name' => 'email',
            'permission_detail' => 'Email'
        ]);

        //users
        DB::table('users')->truncate();
    	
        DB::table('users')->insert([
            'name' => 'Nguyễn Công Quyết',
            'email' => 'quyet@acs.vn',
            'password' => Hash::make('@cs@2018'),
            'level' =>  0,
            'trial' =>  0,
        ]);
        DB::table('users')->insert([
            'name' => 'Lê Văn Vĩnh',
            'email' => 'vinh@acs.vn',
            'password' => Hash::make('@cs@2018'),
            'trial' =>  0,
        ]);
    }
}
