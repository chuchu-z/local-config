

## gittag脚本说明



>   作用:   简化打tag步骤,  为以test开头的分支和dev分支环境生成发版需要的tag,  并把tag提交推送到远程,  脚本实际执行命令为

```bash
git tag -a tag -m 说明 commitId
git push origin --tags
```



## 添加环境变量

>   把gittag文件目录路径添加进path
>
>   目的:　根据环境变量中的path值，找到对应的指令可执行文件进行执行 (不配置直接敲绝对路径也行)



## gittag参数说明

|   参数   |     含义     |                             说明                             |
| :------: | :----------: | :----------------------------------------------------------: |
| 第一参数 | 发版内容说明 |                           必传参数                           |
| 第二参数 | 今日发版次数 |    可选参数, 默认是1, 同一个commitId同一天内多次打tag时用    |
| 第三参数 | 发版commitId | 可选参数,  默认是取当前分支最新的commitId, 也可传参指定commitId |



## gittag使用



>   一般使用方式

```
gittag 发版内容说明
```

![image1](https://aexphoto-1251755124.file.myqcloud.com/img/2021/08/329120757196a7481ec76afbd430535e.png)



>    指定发版次数为第二次和commitId方式

```
gittag 测试可选参数 2 d72f640f2a5e34980dee8d8e7076dc74eb814795
```

![image2](https://aexphoto-1251755124.file.myqcloud.com/img/2021/08/47b8f8df6d9ab1c216b458fb4f7107b7.png)



>   test开头的分支, 生成前面会带上 当前分支名#

```bash
gittag 测试可选参数 2 6e7e90928bba03cbd9d66d05b8ef209ff8e885b3
```

![image3](https://aexphoto-1251755124.file.myqcloud.com/img/2021/08/f19be76b86841b137d7cf41cd2729fff.png)