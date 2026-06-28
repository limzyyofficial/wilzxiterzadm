"""
main_app.py  —  mounts api.py + vpn_check.py into one uvicorn process

Uvicorn menjalankan file INI (bukan api.py langsung):
    uvicorn main_app:app --host 127.0.0.1 --port 8000 --workers 2

Routing:
    /          → api.py      (semua action= parameter)
    /vpn_check/ → vpn_check.py
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from api import app as api_app
from vpn_check import app as vpn_app

app = FastAPI(docs_url=None, redoc_url=None)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["GET", "POST"],
    allow_headers=["Content-Type"],
)

# Mount sub-apps
app.mount("/vpn_check", vpn_app)
app.mount("/", api_app)
