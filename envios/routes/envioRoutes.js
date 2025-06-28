import express from "express";
import mysql from "mysql2/promise";
import dotenv from "dotenv";


const router = express.Router();
dotenv.config();

const db = await mysql.createConnection({
  host: process.env.DB_HOST || "localhost",
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASSWORD || "",
  database: process.env.DB_NAME || "logistica_transp",
  port: process.env.DB_PORT || 3306
});

// Obtener transportes disponibles para una zona
router.get("/transportes/:zona", async (req, res) => {
    const zona = req.params.zona;
    const [filas] = await db.execute(`SELECT * FROM transportes WHERE zonas_disponibles LIKE ?`, [`%${zona}%`]);
    res.json(filas);
});


// Registrar envío (lo llamas cuando pagas)
router.post("/envios", async (req, res) => {
    const { id_pedido, direccion_entrega, zona_entrega, transporte_id } = req.body;

    console.log("📦 Body recibido:", req.body);

    if (!id_pedido || !direccion_entrega || !transporte_id) {
        console.log("⛔ Faltan campos requeridos:", { id_pedido, direccion_entrega, transporte_id });
        return res.status(400).json({ mensaje: "Faltan campos requeridos" });
    }

    const fecha = new Date();
    fecha.setDate(fecha.getDate() + 2);
    const fecha_estimada = fecha.toISOString().split("T")[0];

    try {
        console.log("🚧 Intentando insertar en envíos...");
        await db.execute(`
            INSERT INTO envios (id_pedido, direccion_entrega, transporte_id, estado, fecha_estimada, zona_entrega)
            VALUES (?, ?, ?, 'pendiente', ?, ?)
        `, [id_pedido, direccion_entrega, transporte_id, fecha_estimada,zona_entrega]);

        console.log("✅ Envío registrado exitosamente.");
        res.json({ mensaje: "Envío registrado", id_pedido, fecha_estimada });
    } catch (error) {
        console.error("❌ Error al registrar el envío:", error.message);
        res.status(500).json({ mensaje: "Error al registrar envío", error: error.message });
    }
});


export default router;