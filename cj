# 诗词

url='https://open.saintic.com/api/sentence/'
if [ x$1 != x ]; then
    url='https://open.saintic.com/api/sentence/'$1
fi

# 处理 json 数据
json=`curl -ks $url`

# --compact-output: 使输出紧凑，而不是把每一个JSON对象输出在一行
data=`echo ${json} | jq --compact-output '.data'`

date=`date`

# 写入日志
`echo "[" $date "]" ${data} >> ${local}/cj.log`

# 查看日志写入列表
# cat cj.log | cut -d":" -f 11 | cut -d'"' -f 2

random=$[RANDOM%7+31]
color="\033[1;${random}m%s\033[0m"

echo -e

content=`echo ${json} | jq '.data.sentence' | sed 's/"//g' | tr -d '\n'`

printf "\033[1;${random}m%s\033[0m" "『 " $content " 』"
from_who=`echo ${json} | jq '.data.author' | sed 's/"//g' | tr -d '\n'`

echo -e

if [ "$from_who" == 'null' ]
then
    from_who='匿名'
fi

length=`expr ${#content} \* 2`
printf "\033[1;${random}m%${length}s\033[0m" "—— $from_who"

from=`echo ${json} | jq '.data.name' | sed 's/"//g' | tr -d '\n'`
printf "\033[1;${random}m%s\033[0m" "「" $from "」"
echo -e

exit 0
