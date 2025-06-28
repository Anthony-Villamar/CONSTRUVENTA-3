import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import facturaRoutes from "./routes/factura.js";

dotenv.config();

const app = express();
app.use(cors());
app.use(express.json());

app.use("/", facturaRoutes);

const PORT = 4000;
app.listen(PORT, () => {
    console.log(`Servidor de facturación en http://localhost:${PORT}`);
});
