<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
class Orm extends Model
{
    public $db;
    public $transaction;
    public function __construct()
    {
        $this->db = new Capsule();
        $this->db->addConnection(\Yaf\Registry::get("dbconfig")['default']);
        $this->db->setEventDispatcher(new Dispatcher(new Container));
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        // 将数据库连接类存入全局对象中
        $this->transaction = $this->db->getConnection()->getPdo();
    }
}