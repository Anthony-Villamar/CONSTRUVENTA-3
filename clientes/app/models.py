from pydantic import BaseModel
from typing import Optional

class Usuario(BaseModel):
    cedula: str
    nombre: str
    apellido: str
    telefono: str
    direccion: str
    zona: str
    email: str
    password: str
    rol: Optional[str] = "cliente"
