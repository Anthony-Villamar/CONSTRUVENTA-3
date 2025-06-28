import express from "express";
import mysql from "mysql2/promise";
import axios from "axios";

const router = express.Router();

// Conexión a base de datos plataforma_construventa
const db = await mysql.createConnection({
    host: "localhost",
    user: "root",
    password: "",
    database: "plataforma_construventa"
});

// Ruta POST /facturas
router.post("/facturas", async (req, res) => {
    console.log("✅ Recibido POST /facturas:", req.body);

    const { id_pedido, transporte_precio } = req.body;
    if (!id_pedido) return res.status(400).json({ mensaje: "Falta id_pedido" });

    try {
        // Obtener detalles del pedido desde Laravel
        const pedidoRes = await axios.get(`http://localhost:8000/api/pedidos/${id_pedido}`);
        const pedido = pedidoRes.data;

        if (!pedido || !pedido.producto) {
            return res.status(400).json({ mensaje: "El pedido no tiene producto" });
        }

        // Obtener precio del producto desde la base de datos producto
        const [productoRows] = await db.execute(
            `SELECT precio FROM producto WHERE codigo_producto = ?`,
            [pedido.producto]
        );

        if (productoRows.length === 0) {
            return res.status(400).json({ mensaje: "Producto no encontrado" });
        }

        const precioProducto = parseFloat(productoRows[0].precio);
        const subtotalProductos = precioProducto * pedido.cantidad;

        const subtotalConTransporte = subtotalProductos + (parseFloat(transporte_precio) || 0);
        const iva = subtotalConTransporte * 0.15;
        const monto_total = subtotalConTransporte + iva;

        console.log("🧾 Insertando factura con:", {
            id_pedido,
            subtotal: subtotalConTransporte,
            iva,
            monto_total
        });

        // Insertar en la tabla de factura
        await db.execute(
            `INSERT INTO factura (id_pedido, fecha_emision, total, transporte_precio)
             VALUES (?, NOW(), ?, ?)`,
            [id_pedido, monto_total, transporte_precio]
        );

        console.log("✅ Factura generada correctamente");
        res.json({
            mensaje: "Factura generada",
            subtotal: subtotalConTransporte,
            iva,
            monto_total
        });

    } catch (err) {
        console.error("❌ Error al generar factura:", err.response?.data || err.message || err);
        res.status(500).json({ mensaje: "Error al generar factura" });
    }
});

export default router;
