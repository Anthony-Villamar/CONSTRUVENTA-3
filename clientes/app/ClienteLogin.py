from pydantic import BaseModel


class ClienteLogin(BaseModel):
    cedula: str
    password: str
