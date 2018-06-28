# 日志类

特性：

* 支持选择文件、数据库等类型的驱动分别处理 common,error,trace三种不同的日志
* trace的日志支持app,lib,sys三个级别
* 支持自定义分割、保存方式

## 自定义分割、保存方式

日志分割上，可以使用 {year},{month},{day},{hour},{minute},{second},{type}变量

文件可以用 "/var/log/{year}-{month}/{hour}-{type}.log" 作为文件名模板
数据库可以用  "db_log.{type}_{year}{month}{day}" 作为表明模板

保存数据上，Sooh\LogClasses\LogParts 的所有属性都可以用比如{time},{message}


## 基本用法

关于支持按时间、类型分割日志


## 更多driver

Sooh\LogClasses\Synchronous 是同步写日志的类，比如要同步写文件和数据库

## 自定义日志分割 & 保存日志
以常见文件日志来说：

1. 可以使用 {year},{month},{day},{hour},{minute},{second},{type}定义完整路径的文件名
2. Sooh\LogClasses\LogParts 的所有属性（比如{time},{message}）都可以作为每行的记录的格式定义