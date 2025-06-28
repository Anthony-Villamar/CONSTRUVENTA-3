from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import pymysql
from app.database import get_connection
from app.models import Usuario

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
async def root():
    return {"mensaje": "API Usuarios funcionando"}

# 🟢 Registro de usuario
@app.post("/usuarios")
def registrar_usuario(usuario: Usuario):
    conn = get_connection()
    cursor = conn.cursor()

    try:
        cursor.execute("""
            INSERT INTO Usuario (id_cliente, nombre, apellido, telefono, direccion, zona, email, password, rol)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            usuario.cedula, usuario.nombre, usuario.apellido,
            usuario.telefono, usuario.direccion, usuario.zona,
            usuario.email, usuario.password, usuario.rol
        ))

        conn.commit()
        return {"mensaje": "Usuario registrado correctamente"}

    except pymysql.err.IntegrityError:
        raise HTTPException(status_code=400, detail="La cédula o el correo ya existen")

    finally:
        cursor.close()
        conn.close()

# 🟢 Login de usuario
@app.post("/login")
def login(email: str, password: str):
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("""
        SELECT id_cliente, nombre, apellido, rol FROM Usuario
        WHERE email = %s AND password = %s
    """, (email, password))

    user = cursor.fetchone()

    cursor.close()
    conn.close()

    if user:
        return {
            "cedula": user[0],
            "nombre": user[1],
            "apellido": user[2],
            "rol": user[3],
            "mensaje": "Inicio de sesión exitoso"
        }
    else:
        raise HTTPException(status_code=401, detail="Credenciales inválidas")

# 🟢 Consultar usuario
@app.get("/usuarios/{cedula}")
def consultar_usuario(cedula: str):
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("""
        SELECT id_cliente, nombre, apellido, telefono, direccion, zona, email
        FROM Usuario WHERE id_cliente = %s
    """, (cedula,))
    row = cursor.fetchone()

    cursor.close()
    conn.close()

    if row:
        return {
            "cedula": row[0],
            "nombre": row[1],
            "apellido": row[2],
            "telefono": row[3],
            "direccion": row[4],
            "zona": row[5],
            "email": row[6]
        }
    else:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")

# 🟢 Actualizar usuario
@app.put("/usuarios/{cedula}")
def actualizar_usuario(cedula: str, usuario: Usuario):
    conn = get_connection()
    cursor = conn.cursor()

    cursor.execute("""
        UPDATE Usuario
        SET nombre=%s, apellido=%s, telefono=%s, direccion=%s, zona=%s, email=%s, password=%s
        WHERE id_cliente=%s
    """, (usuario.nombre, usuario.apellido, usuario.telefono, usuario.direccion, usuario.zona, usuario.email, usuario.password, cedula))

    conn.commit()
    cursor.close()
    conn.close()

    return {"mensaje": "Usuario actualizado correctamente"}
