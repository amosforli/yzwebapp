<?php
namespace app\index\controller;

use think\Db;
class Index extends \BaseController
{
    public function index()
    {
        $input = input('get.name');
        return $input;
//        $site = Db::table('tbl_site')->where('SiteID',1070)->find();
//        return json($site);
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
