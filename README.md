简介:
	
	一个面向api的 php分布式 框架，不需要安装C扩展，依靠http实现远程PRC

	灵活的多机器，多机房部署，只需要更改不同环境配置，对接口逻辑编写人员基本透明

	version: 0.1

	base php version: 5.0 or later

get start: 
	
	目录说明:
	
		/tests 测试文件目录
		/tools 帮助文档，如分布式下的nginx配置文件示例
		/pri   核心源码

		/pri/api       远程调用入口，也可以自己在api下开发无pri约束的api远程调用
		/pri/com       外部类引入，自动include此目录下的文件：如cookie，二维码功能类
		/pri/config    配置目录，按照项目的不同环境作部署，如测试环境，本地环境，线上环境
		/pri/dbg       每个模块的单元化测试用例目录
		/pri/interface 接口目录，文档和接口写在此目录，方便开发，维护。统一项目开发规范
		/pri/module    逻辑实现目录，每个模块一个目录，目录下可以分为主模块和辅助模块

	example:

		$GLOBAL['pri'] 为框架全局变量，初始化一次
		$pri = $GLOBAL['pri'];
		$hello = $pri->load('hello');
		$hello->sayHello();//根据配置文件内容判断是否走远程rpc

		可运行案例详见：test/test.php , test模块	已配置为远程调用

todo list:
	
	1. 实现 不需要同步的数据，批量异步请求回调
	2. 替换curl，用socket_select实现
	3. 远程调用数据 gzip 压缩
	4. 时间统计和debug模块
	5. 远程调用本地cache，初步以来memcache
	6. ajax 框架内实现
	7. 安全防护，基于只限内网调用或者开放http安全调用

about me:

	author: hihu
	mail: coldsolo@gmail.com 
	weibo: @hihus
