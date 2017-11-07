<?php
class AdminsModel extends Orm {
	/**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'admins';

	// 不可以批量赋值的字段，为空则表示都可以
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

}
?>