@echo off
chcp 65001 > nul
echo ========================================================
echo   Iniciando Frontend Vevey Doces (Porta 3000)
echo ========================================================
cd frontend
echo Abrindo catalogo no navegador...
start http://localhost:3000/catalogo.html
python -m http.server 3000
pause
