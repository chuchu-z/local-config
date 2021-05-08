# 一言

# 返回普通文本
#`curl -ks https://v1.hitokoto.cn/?encode=text`

url='https://v1.hitokoto.cn'
if [ x$1 != x ]; then
    url='https://v1.hitokoto.cn?'$1
fi

# echo $url

# 处理 json 数据
json=`curl -ks $url`

date=`date`

# 写入日志
`echo "[" $date "]" $json >> ${local}/yy.log`

# content=${json} | jq '.hitokoto' | sed 's/"//g' | tr -d '\n'
# 失败原因：管道无法直接赋值给变量
# 在 Shell 中使用 jq 解析 JSON 格式文本，通过管道读取出 value 后无法存储为 Shell 中的值
# 只需要将读取出的 value 通过 echo 打印出来，再利用``将值写入到变量中即可

random=$[RANDOM%7+31]
# color="\033[1;${random}m%s\033[0m"

echo -e
content=`echo ${json} | jq '.hitokoto' | sed 's/"//g' | tr -d '\n'`
printf "\033[1;${random}m%s\033[0m" "『 " $content " 』"
from_who=`echo ${json} | jq '.from_who' | sed 's/"//g' | tr -d '\n'`

echo -e

if [ $from_who == 'null' ]
then
    from_who='匿名'
fi

length=`expr ${#content} \* 2`
printf "\033[1;${random}m%${length}s\033[0m" "—— "$from_who

from=`echo ${json} | jq '.from' | sed 's/"//g' | tr -d '\n'`
printf "\033[1;${random}m%s\033[0m" "「" $from "」"

echo -e

# {"id":6175,"uuid":"db2c4ea6-ce7d-4637-8aa8-456f2e4ec874","hitokoto":"知识有两种，一种是你知道的，一种是你知道在哪里能找到的！","type":"k","from":"塞缪尔·约翰逊","from_who":null,"creator":"Mr96","creator_uid":4362,"reviewer":1044,"commit_from":"web","created_at":"1589939352","length":28}
