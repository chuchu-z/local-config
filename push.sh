#!/bin/sh

#================================================================
#   Copyright (C) 2020 Zhou. All rights reserved.
#   
#   FileName：push.sh
#   Author：Zhou
#   Create：2020-05-15 15:20:15
#   LastModified：2020-06-05 17:57:02
#   Description：
#
#================================================================

if [ x$1 != x ]

then
    echo -e  "\n===================push Begin===================\n"

    git status
    git add .
    git commit -m "$1"
    if [ $? == 0 ]
    then
        #echo -e  "\n提交信息:$1\n"

        git push charge develop

        echo -e "\n===================push End===================\n"

        echo -e "\n===================commit log===================\n"

        git log -1

        echo -e "\n"
    fi
else
    echo -e "git commit -m 缺少参数\n"
fi

exit 0
