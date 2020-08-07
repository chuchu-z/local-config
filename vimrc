" Setting some decent VIM settings for programming
" This source file comes from git-for-windows build-extra repository (git-extra/vimrc)
set nu
set autoindent
"set paste
set hlsearch
set cul

"set softtabstop=2
"set shiftwidth=4
"set tabstop=2

set ts=4
set sw=4 "缩进
set expandtab "制表符换空格

"autocmd FileType c,cpp set shiftwidth=4 | set expandtab
syntax on
ru! defaults.vim                " Use Enhanced Vim defaults
set mouse=                      " Reset the mouse setting from defaults
aug vimStartup | au! | aug END  " Revert last positioned jump, as it is defined below
let g:skip_defaults_vim = 1     " Do not source defaults.vim again (after loading this system vimrc)

set ai                          " set auto-indenting on for programming
set showmatch                   " automatically show matching brackets. works like it does in bbedit.
set vb                          " turn on the "visual bell" - which is much quieter than the "audio blink"
set laststatus=2                " make the last line where the status is two lines deep so you can see status always
set showmode                    " show the current mode
set clipboard=unnamed           " set clipboard to unnamed to access the system clipboard under windows
set wildmode=list:longest,longest:full   " Better command line completion

" Show EOL type and last modified timestamp, right after the filename
" Set the statusline
set statusline=%f               " filename relative to current $PWD
set statusline+=%h              " help file flag
set statusline+=%m              " modified flag
set statusline+=%r              " readonly flag
set statusline+=\ [%{&ff}]      " Fileformat [unix]/[dos] etc...
set statusline+=\ (%{strftime(\"%H:%M\ %d/%m/%Y\",getftime(expand(\"%:p\")))})  " last modified timestamp
set statusline+=%=              " Rest: right align
set statusline+=%l,%c%V         " Position in buffer: linenumber, column, virtual column
set statusline+=\ %P            " Position in buffer: Percentage

if &term =~ 'xterm-256color'    " mintty identifies itself as xterm-compatible
  if &t_Co == 8
    set t_Co = 256              " Use at least 256 colors
  endif
  " set termguicolors           " Uncomment to allow truecolors on mintty
endif
"------------------------------------------------------------------------------
" Only do this part when compiled with support for autocommands.
if has("autocmd")
    " Set UTF-8 as the default encoding for commit messages
    autocmd BufReadPre COMMIT_EDITMSG,MERGE_MSG,git-rebase-todo setlocal fileencodings=utf-8

    " Remember the positions in files with some git-specific exceptions"
    autocmd BufReadPost *
      \ if line("'\"") > 0 && line("'\"") <= line("$")
      \           && &filetype !~# 'commit\|gitrebase'
      \           && expand("%") !~ "ADD_EDIT.patch"
      \           && expand("%") !~ "addp-hunk-edit.diff" |
      \   exe "normal g`\"" |
      \ endif

      autocmd BufNewFile,BufRead *.patch set filetype=diff

      autocmd Filetype diff
      \ highlight WhiteSpaceEOL ctermbg=red |
      \ match WhiteSpaceEOL /\(^+.*\)\@<=\s\+$/
endif " has("autocmd")


" 当新建 *.sh,*.php,*.js,*.cpp 等文件时自动调用SetTitle 函数
autocmd BufNewFile *.sh,*.php,*.js,*.cpp exec ":call SetTitle()" |normal 16Go 

" 加入注释 
func SetComment()
	call append(line("."), "")
	call append(line(".")+1, "")
	call append(line(".")+2, "/*================================================================") 
	call append(line(".")+3,   "*   Copyright (C) ".strftime("%Y")." Zhou. All rights reserved.")
	call append(line(".")+4, "*   ") 
	call append(line(".")+5, "*   FileName：".expand("%:t")) 
	call append(line(".")+6, "*   Author：Zhou")
	call append(line(".")+7, "*   Create：".strftime("%Y-%m-%d %H:%M:%S"))
	call append(line(".")+8, "*   LastModified：".strftime("%Y-%m-%d %H:%M:%S"))
	call append(line(".")+9, "*   Description：") 
	call append(line(".")+10, "*")
	call append(line(".")+11, "================================================================*/") 
	call append(line(".")+12, "")
	call append(line(".")+13, "")
endfunc

" shell
func SetComment_sh()
	call setline(4, "#================================================================") 
	call setline(5, "#   Copyright (C) ".strftime("%Y")." Zhou. All rights reserved.")
	call setline(6, "#   ") 
	call setline(7, "#   FileName：".expand("%:t")) 
	call setline(8, "#   Author：Zhou")
	call setline(9, "#   Create：".strftime("%Y-%m-%d %H:%M:%S"))
	call setline(10, "#   LastModified：".strftime("%Y-%m-%d %H:%M:%S"))
	call setline(11, "#  Description：") 
	call setline(12, "#")
	call setline(13, "#================================================================")
	call setline(14, "")
	call setline(15, "")
endfunc

" 定义函数SetTitle，自动插入文件头 
func SetTitle()

	if expand("%:e") == 'php'
	    call setline(1, "<?php")
	    call SetComment()
	elseif expand("%:e") == 'js'
	    call setline(1, '//JavaScript file')
	    call SetComment()

	elseif expand("%:e") == 'cpp'
	    call setline(1, '//C++ file')
	    call SetComment()

	elseif &filetype == 'sh' 
		call setline(1,"#!/bin/sh") 
		call setline(2,"")
		call setline(3,"")
		call SetComment_sh()
		
	else
	     call SetComment()
	endif
endfunc

"map映射绑定F2
map <F2> :call SetComment()<CR>:16<CR>o


"SET Last Modified Time START

func DataInsert()

    call cursor(10,1)

    if search ('LastModified：') != 0

        let line = line('.')
        let str = search ('*') != 0 ? '*' : '#'
        call setline(line, str."   LastModified：".strftime("%Y-%m-%d %H:%M:%S"))

    endif
   
endfunc

autocmd FileWritePre,BufWritePre *.sh,*.php,*.js,*.cpp ks|call DataInsert() |'s

"SET Last Modified Time END
