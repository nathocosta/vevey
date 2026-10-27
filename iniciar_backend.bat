@echo off
chcp 65001 > nul
echo ========================================================
echo   Iniciando Backend Vevey Doces (FastAPI / Uvicorn)
echo ========================================================
cd backend
if exist venv\Scripts\activate.bat (
    call venv\Scripts\activate.bat
) else (
    if exist ..\venv\Scripts\activate.bat (
        call ..\venv\Scripts\activate.bat
    )
)
echo Servidor iniciando em http://localhost:8000 ...
echo Documentacao Swagger em http://localhost:8000/api/v1/docs
uvicorn app.main:app --reload --port 8000
pause
