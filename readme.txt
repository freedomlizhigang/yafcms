项目规划

|-app  项目功能文件夹
	|-controllers  控制器
	|-models  模型
	|-modules  模块，如后台、用户等多个模块用
	|-plugins  插件
	|-views  视图模板

	|-下面的根据需求不同
	|-common  存放一些公用函数等内容（直接在bootstrap中加载进来）
	|-lib  自己针对写的类库

|-cache  数据缓存文件夹

|-conf
	|-app.ini  Yaf的配置文件
	|-config.php  项目一些目录定义、cookie、session等
	|-datebase.php  数据库连接配置
	|-mimes.php  文件头信息数据，安全验证用

|-core  核心类库
	|-Db  数据库实现类
		|-Db.php  数据库工厂类，用来转换数据库具体实现，现在写死为mysqli
		|-Mysqli.php  mysql数据库的具体操作
	|-Cache.php  缓存操作类，存储为php文件，内部结构为array
	|-Checkcode.php  验证码类
	|-Model.php  数据模型，各表模型继承此类，不能空实例化
	|-Session.php  Session数据库操作类，此类将session的存储方式改为了mysql数据库，需要/app/models/sessiondb.							php类（目前默认方式，直接写死到bootstrap中了）
	|-Upload.php  文件上传类，目前此类没有生成子目录功能，在使用时生成

|-public  公用静态文件
	|-css
	|-js
	|-images
	|-font

|-uploads  上传文件存储位置

|-index.php   项目入口文件


注意事项（重要）：
	1、命名空间自动加载，只能在app目录下使用，如：/app/lib/Test.php的namespace lib; class Test{}; 使用new lib\Test();类是可以的;MVC及本地类在自动加载前要加"\"表示根命名空间，否则加载不出来（这是个大坑）

	2、多个类库取自phpcms v9/CI