# vim ~/.bashrc
# Git-Bash 初始化加载此配置文件
# shopt -s expand_aliases
# 允许shell脚本中使用 alias 命令
# 经测试, 在 #!/bin/sh 下, 该命令可有可无, 不影响 alias 的使用
# 在 !/bin/bash 下，才有影响

yy

# https://github.com/git/git/blob/master/contrib/completion/git-completion.bash
source /usr/share/git/completion/git-completion.bash
source /etc/bash_completion
