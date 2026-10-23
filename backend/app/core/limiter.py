from slowapi import Limiter
from slowapi.util import get_remote_address

# Inicializa limiter global baseado no IP do cliente
limiter = Limiter(key_func=get_remote_address, default_limits=["120/minute"])
