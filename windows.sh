#!/bin/bash
#cd ./workspace/www

# 在 windows 下的 Git bash 使用 ln 命令 会变成 cp 的效果
# windows系统创建软链接方法: mklink /d "需要创建链接文件的绝对路径" "指向文件的绝对路径" cmd下管理员身份运行
# 下面的路径记得改成你的项目路径
# 删除软链接使用 rmdir
# 例: rmdir D:\MyPHP\WWW\project\workspace\www\aex.com

#rm -rf aex.com
mklink /d D:\MyPHP\WWW\project\workspace\www\aex.com D:\MyPHP\WWW\project\workspace\www\svr

#cd svr
#rm  -rf page
mklink /d D:\MyPHP\WWW\project\workspace\www\svr\page D:\MyPHP\WWW\project\workspace\www\page
#rm -rf admin
mklink /d D:\MyPHP\WWW\project\workspace\www\svr\admin D:\MyPHP\WWW\project\workspace\www\newadmin\bitcc_administrator
