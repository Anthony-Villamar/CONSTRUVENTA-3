
import express from "express";
    import dotenv from "dotenv";
    import cors from "cors";
    import mysql from "mysql2/promise";

    import envioRoutes from "./routes/envioRoutes.js";
    dotenv.config();
    const app = express();
    app.use(cors());
    app.use(express.json());
    
    // 💾 Conexión a la base de datos
   const db = await mysql.createConnection({
     host: process.env.DB_HOST || "localhost",
     user: process.env.DB_USER || "root",
     password: process.env.DB_PASSWORD || "",
     database: process.env.DB_NAME || "logistica_transp",
     port: process.env.DB_PORT || 3306
   });
   


app.use("/", envioRoutes);


// ▶️ Iniciar el servidor
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`🚀 Servidor de envíos corriendo en http://localhost:${PORT}`);
});
