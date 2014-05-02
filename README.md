简介:
	
	一个面向api的php分布式框架，不需要安装C扩展，依靠http实现远程PRC

	灵活的多机器，多机房部署，只需要更改不同环境配置，对接口逻辑编写人员基本透明

	version: 0.1

	base php version: 5.0 or later

example:
	
	暂时未添加 ~

todo list:
	
	1. 实现 不需要同步的数据，批量异步请求回调
	2. 替换curl，用socket_select实现
	3. 远程调用数据 gzip 压缩
	4. 时间统计和debug模块
	5. 远程调用本地cache，初步以来memcache
	6. ajax 框架内实现
	7. 安全防护，基于只限内网调用或者开放http安全调用

author:

	hihu , coldsolo@gmail.com , @hihus ~

