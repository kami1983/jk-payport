# jk-payport

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

