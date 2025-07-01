@echo off
echo Limpando arquivos problemáticos do macOS...

echo Removendo arquivos ._*...
for /r %%i in (._*) do del "%%i" 2>nul

echo Removendo arquivos .DS_Store...
for /r %%i in (.DS_Store) do del "%%i" 2>nul

echo Removendo diretórios __MACOSX...
for /d /r %%i in (__MACOSX) do rmdir /s /q "%%i" 2>nul

echo Limpeza concluída!
echo Agora você pode fazer commit e push para o Git.
pause 