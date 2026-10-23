import os
from typing import List, Union
from pydantic_settings import BaseSettings
from pydantic import AnyHttpUrl, field_validator


class Settings(BaseSettings):
    PROJECT_NAME: str = "Vevey Doces API"
    ENVIRONMENT: str = "development"
    API_V1_STR: str = "/api/v1"

    # PostgreSQL / Supabase
    DATABASE_URL: str = "postgresql://postgres:postgres@localhost:5432/vevey_db"

    # Segurança & JWT
    SECRET_KEY: str = "chave_temporaria_dev_vevey_tcc_2026"
    ALGORITHM: str = "HS256"
    ACCESS_TOKEN_EXPIRE_MINUTES: int = 60 * 24  # 1 dia

    # CORS
    BACKEND_CORS_ORIGINS: List[str] = [
        "http://localhost",
        "http://localhost:3000",
        "http://localhost:5500",
        "http://127.0.0.1:5500",
        "https://vevey.vercel.app",
    ]

    @field_validator("SECRET_KEY")
    @classmethod
    def validate_secret_key(cls, v: str) -> str:
        env = os.getenv("ENVIRONMENT", "development").lower()
        if env == "production" and v == "chave_temporaria_dev_vevey_tcc_2026":
            raise ValueError(
                "SECRET_KEY insegura em produção! Defina a variável de ambiente SECRET_KEY no Render/servidor."
            )
        return v


    class Config:
        env_file = ".env"
        case_sensitive = True
        extra = "ignore"


settings = Settings()
