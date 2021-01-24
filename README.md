Json Convert
============================

> 目录说明

    .
    │  cli.php
    ├─config                    #入口文件
    │      config.php           #配置文件，含数据库配置
    ├─lib                       
    │      JsonReader.php       #读取Json文件的主要实现
    ├─storage
    │      sample.json          #原始json
    │      sample.sql           #转化后导出的SQL
    └─tasks
            MainTask.php        #主任务入口

### 执行方式与结果

```console
[root@test json_convert]# php cli.php 
---- json convert tasks start ----
memory usage at beginning :429584
process ended, and 499999 cols has been inserted
memory usage at last :642768
total execution time :28.019049882889
---- json convert tasks end ----
