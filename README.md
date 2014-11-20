# jk-payport

## release 1.0.8 on 2014-11-16
* 新增 site/setting 管理页面用来操作配置文件，从此再也不需要去服务器上改配置文件了。
* 新增邮件服务器功能，通过设置可以向付款中心进行请求进行通知邮件的发送。
* 统一修正JSON 返回数据格式，为{"result":"success","back_value":true} 或者 "result":"failure","error_code":1000851,"error_info":"database operation error."} 

## release 1.0.7 on 2014-11-16
* 修改 .gitignore 文件，解决配置文件pull 后冲突的问题。

## release 1.0.6 on 2014-11-16
* 数据库表，支持记录付款人信息
* pay do 地址会接收到post_json，payerinfo 两组POST 数据，其中payerinfo 是新添加，用于paydo 程序进行付款人核实，比如email 的发送。
* 如果你使用版本小于 1.0.6 请升级你的数据库 ALTER TABLE `payport_payment` ADD `payerinfo` TEXT NOT NULL COMMENT '付款者的信息，付款成功才会有。' AFTER `id` 

## release 1.0.5 on 2014-11-05
* 支持管理员功能，请务必改掉默认的管理员密码，这是你升级1.0.5 必须要做的事儿！
* 增加内部付款记录数据列表
* 支持将do_url 数据记录到数据库，如果是老系统或许你需要进行升级你的数据库。
* 升级你的老数据库表：ALTER TABLE `payport_payment` ADD `do_response` TEXT NOT NULL COMMENT 'Record do_url return content.' AFTER `do_response` 

## release 1.0.4 on 2014-10-31
* feature 支持将do_url 数据记录到日志，当使用测试连接时。

## release 1.0.3 on 2014-10-31
* fixbug 金额31.5 的404 bug 得到修复。

## release 1.0.2 on 2014-10-29 
* 支持Paypal 接口调用。

