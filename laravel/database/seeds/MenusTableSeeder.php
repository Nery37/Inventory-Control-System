<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MenusTableSeeder extends Seeder
{
    private $menuId = null;
    private $dropdownId = array();
    private $dropdown = false;
    private $sequence = 1;
    private $joinData = array();
    private $adminRole = null;
    private $userRole = null;

    public function join($roles, $menusId){
        $roles = explode(',', $roles);
        foreach($roles as $role){
            array_push($this->joinData, array('role_name' => $role, 'menus_id' => $menusId));
        }
    }

    /*
        Function assigns menu elements to roles
        Must by use on end of this seeder
    */
    public function joinAllByTransaction(){
        DB::beginTransaction();
        foreach($this->joinData as $data){
            DB::table('menu_role')->insert([
                'role_name' => $data['role_name'],
                'menus_id' => $data['menus_id'],
            ]);
        }
        DB::commit();
    }

    public function insertLink($roles, $name, $href, $icon = null){
        if($this->dropdown === false){
            DB::table('menus')->insert([
                'slug' => 'link',
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'sequence' => $this->sequence
            ]);
        }else{
            DB::table('menus')->insert([
                'slug' => 'link',
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'menu_id' => $this->menuId,
                'parent_id' => $this->dropdownId[count($this->dropdownId) - 1],
                'sequence' => $this->sequence
            ]);
        }
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        $permission = Permission::where('name', '=', $name)->get();
        if(empty($permission)){
            $permission = Permission::create(['name' => 'visit ' . $name]);
        }
        $roles = explode(',', $roles);
        if(in_array('user', $roles)){
            $this->userRole->givePermissionTo($permission);
        }
        if(in_array('admin', $roles)){
            $this->adminRole->givePermissionTo($permission);
        }
        return $lastId;
    }

    public function insertTitle($roles, $name){
        DB::table('menus')->insert([
            'slug' => 'title',
            'name' => $name,
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence
        ]);
        $this->sequence++;
        $lastId = DB::getPdo()->lastInsertId();
        $this->join($roles, $lastId);
        return $lastId;
    }

    public function beginDropdown($roles, $name, $href, $icon){
        if(count($this->dropdownId)){
            $parentId = $this->dropdownId[count($this->dropdownId) - 1];
        }else{
            $parentId = null;
        }
        DB::table('menus')->insert([
            'slug' => 'dropdown',
            'name' => $name,
            'icon' => $icon,
            'menu_id' => $this->menuId,
            'sequence' => $this->sequence,
            'parent_id' => $parentId,
            'href' => $href
        ]);
        $lastId = DB::getPdo()->lastInsertId();
        array_push($this->dropdownId, $lastId);
        $this->dropdown = true;
        $this->sequence++;
        $this->join($roles, $lastId);
        return $lastId;
    }

    public function endDropdown(){
        $this->dropdown = false;
        array_pop( $this->dropdownId );
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* Get roles */
        $this->adminRole = Role::where('name' , '=' , 'admin' )->first();
        $this->userRole = Role::where('name', '=', 'user' )->first();
        $dropdownId = array();
        /* sidebar menu */
        $this->menuId = 1;
        /* guest menu */
        $this->insertLink('user,admin', 'Dashboard', '/', 'cil-speedometer');
        $this->insertLink('guest', 'Login', '/login', 'cil-account-logout');
        $this->insertLink('guest', 'Register', '/register', 'cil-account-logout');
        $this->beginDropdown('admin', 'Settings', '/', 'cil-puzzle');
            $this->insertLink('admin', 'Media',    '/media');
            $this->insertLink('admin', 'Users',    '/users');
            $this->insertLink('admin', 'Menu',    '/menu');
            $this->insertLink('admin', 'BREAD',    '/bread');
            $this->insertLink('admin', 'Email',    '/email');
        $this->endDropdown();
        $this->insertTitle('admin', 'Theme');
        $this->insertLink('admin', 'Colors', '/colors', 'cil-drop');
        $this->insertLink('admin', 'Typography', '/typography', 'cil-pencil');
        //$this->insertTitle('user,admin', 'Components');
        $this->beginDropdown('admin', 'Base', '/base', 'cil-puzzle');
            $this->insertLink('admin', 'Breadcrumb',    '/base/breadcrumb');
            $this->insertLink('admin', 'Cards',         '/base/cards');
            $this->insertLink('admin', 'Carousel',      '/base/carousel');
            $this->insertLink('admin', 'Collapse',      '/base/collapse');
            $this->insertLink('admin', 'Forms',         '/base/forms');
            $this->insertLink('admin', 'Jumbotron',     '/base/jumbotron');
            $this->insertLink('admin', 'List group',    '/base/list-group');
            $this->insertLink('admin', 'Navs',          '/base/navs');
            $this->insertLink('admin', 'Pagination',    '/base/pagination');
            $this->insertLink('admin', 'Popovers',      '/base/popovers');
            $this->insertLink('admin', 'Progress',      '/base/progress');
           // $this->insertLink('user,admin', 'Scrollspy',     '/base/scrollspy');  
            $this->insertLink('admin', 'Switches',      '/base/switches');
            $this->insertLink('admin', 'Tables',        '/base/tables');
            $this->insertLink('admin', 'Tabs',          '/base/tabs');
            $this->insertLink('admin', 'Tooltips',      '/base/tooltips');
        $this->endDropdown();
        $this->beginDropdown('admin', 'Buttons', '/buttons', 'cil-cursor');
            $this->insertLink('admin', 'Buttons',           '/buttons/buttons');
            $this->insertLink('admin', 'Buttons Group',     '/buttons/button-group');
            $this->insertLink('admin', 'Dropdowns',         '/buttons/dropdowns');
            $this->insertLink('admin', 'Brand Buttons',     '/buttons/brand-buttons');
        $this->endDropdown();
        $this->insertLink('admin', 'Charts', '/charts', 'cil-chart-pie');
        $this->beginDropdown('admin', 'Icons', '/icon', 'cil-star');
            $this->insertLink('admin', 'CoreUI Icons',      '/icon/coreui-icons');
            $this->insertLink('admin', 'Flags',             '/icon/flags');
            $this->insertLink('admin', 'Brands',            '/icon/brands');
        $this->endDropdown();
        $this->beginDropdown('admin', 'Notifications', '/notifications', 'cil-bell');
            $this->insertLink('admin', 'Alerts',     '/notifications/alerts');
            $this->insertLink('admin', 'Badge',      '/notifications/badge');
            $this->insertLink('admin', 'Modals',     '/notifications/modals');
        $this->endDropdown();
        $this->insertLink('user,admin', 'Products', '/products', 'cil-calculator');
        $this->insertLink('user,admin', 'Reports', '/reports', 'cil-calculator');
        //$this->insertTitle('user,admin', 'Extras');
        $this->beginDropdown('admin', 'Pages', '/pages', 'cil-star');
            $this->insertLink('admin', 'Login',         '/login');
            $this->insertLink('admin', 'Register',      '/register');
            $this->insertLink('admin', 'Error 404',     '/404');
            $this->insertLink('admin', 'Error 500',     '/500');
        $this->endDropdown();

        $this->joinAllByTransaction(); ///   <===== Must by use on end of this seeder
    }
}
