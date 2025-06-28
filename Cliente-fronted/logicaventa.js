// let carrito = [];
//     let transportesDisponibles = [];
//     let transporteSeleccionado = null;
//     let totalTransporte = 0;
//     const usuario_id = localStorage.getItem("cedula");


//     async function cargarProductos() {
//         const res = await fetch("http://127.0.0.1:8002/api/productos");
//         const productos = await res.json();
//         const contenedor = document.getElementById("productos");
//         contenedor.innerHTML = "";
//         productos.forEach(p => {
//             const div = document.createElement("div");
//             div.className = "producto";
//             div.innerHTML = `
//                 <h4>${p.nombre}</h4>
//                 <p>${p.descripcion}</p>
//                 <p><b>Precio:</b> $${p.precio}</p>
//                 <p><b>Stock:</b> ${p.stock}</p>
//                 <input type="number" id="cantidad_${p.codigo_producto}" value="1" min="1" max="${p.stock}" style="width:60px;">
//                 <button onclick='agregarProducto("${p.codigo_producto}", "${p.nombre}", ${p.precio}, ${p.peso_kg})'>Agregar</button>
//             `;
//             contenedor.appendChild(div);
//         });
//     }

//     function agregarProducto(codigo, nombre, precio, peso) {
//         const cantidad = parseInt(document.getElementById("cantidad_" + codigo).value);
//         const item = carrito.find(p => p.codigo === codigo);
//         if (item) {
//             item.cantidad += cantidad;
//         } else {
//             carrito.push({ codigo, nombre, precio, peso, cantidad });
//         }
//         actualizarCarrito();
//     }

//     function actualizarCarrito() {
//         const lista = document.getElementById("carrito");
//         lista.innerHTML = "";
//         let total = 0;
//         carrito.forEach(p => {
//             const li = document.createElement("li");
//             li.innerText = `${p.nombre} x ${p.cantidad}`;
//             lista.appendChild(li);
//             total += p.precio * p.cantidad;
//         });
//         document.getElementById("total").innerText = `$${(parseFloat(total) + parseFloat(precioTransporte)).toFixed(2)}`;
//     }

//     let direccion = "";
//     let zona = "";

//     async function cargarTransportes() {
//     const clienteRes = await fetch(`http://localhost:3000/usuarios/${usuario_id}`);
//         const cliente = await clienteRes.json();
//         direccion = cliente.direccion;
//         zona = cliente.zona;

//         const res = await fetch(`http://localhost:3001/transportes/${zona}`);
//         transportesDisponibles = await res.json();

//         const selector = document.getElementById("selectorTransporte");
//         selector.innerHTML = '<option value="">-- Selecciona un transporte --</option>';
//         transportesDisponibles.forEach(t => {
//             const opt = document.createElement("option");
//             opt.value = t.id;
//             opt.textContent = `${t.nombre} - $${t.precio}`;
//             selector.appendChild(opt);
//         });
//     }

//     function actualizarPrecioTransporte() {
//         const id = document.getElementById("selectorTransporte").value;
//         const transporte = transportesDisponibles.find(t => t.id == id);
//         if (transporte) {
//             transporteSeleccionado = transporte;
//             totalTransporte = parseFloat(transporte.precio);
//             document.getElementById("precioTransporte").innerText = `Precio Transporte: $${totalTransporte.toFixed(2)}`;
//             actualizarCarrito();
//         } else {
//             totalTransporte = 0;
//             transporteSeleccionado = null;
//         }
//     }
//     let precioTransporte = 0;

//     paypal.Buttons({
//         createOrder: function(data, actions) {
//             const subtotal = carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0);
//             const subtotalFinal = (subtotal + totalTransporte)* 0.15;
//             const totalFinal = subtotal + totalTransporte + subtotalFinal;

//             if (totalFinal <= 0) {
//                 alert("No puedes pagar un total de $0.00. Agrega productos al carrito.");
//                 return;
//             }
//             return actions.order.create({
//                 purchase_units: [{ amount: { value: totalFinal.toFixed(2) } }]
//             });
//         },
         
//         onApprove: async function (data, actions) {
//     await actions.order.capture();
//     alert("¡Pago exitoso!");

//     try {
//         const pedidoRes = await fetch("http://127.0.0.1:8000/api/pedidos", {
//             method: "POST",
//             headers: { "Content-Type": "application/json" },
//             body: JSON.stringify({
//                 usuario_id,
//                 productos: carrito.map(item => ({
//                     codigo_producto: item.codigo,
//                     cantidad: item.cantidad
//                 }))
//             })
//         });
//                 if (!pedidoRes.ok) throw new Error("❌ Error en /api/pedidos");

//         const pedidoData = await pedidoRes.json();
//         const id_pedido = pedidoData.id_pedido;

//         await fetch("http://127.0.0.1:4000/facturas", {
//             method: "POST",
//             headers: { "Content-Type": "application/json" },
//             body: JSON.stringify({ id_pedido, transporte_precio: totalTransporte })
//         });

//         // Validar transporte
//         if (!transporteSeleccionado) {
//             console.error("❌ No se seleccionó un transporte.");
//             alert("Error: debes seleccionar un transporte antes de pagar.");
//             return;
//         }

//                 if (!facturaRes.ok) throw new Error("❌ Error en /facturas");

//         await new Promise(resolve => setTimeout(resolve, 5000)); // Esperar 0.5 segundos
//         const envioRes = await fetch("http://127.0.0.1:3001/envios", {
//             method: "POST",
//             headers: { "Content-Type": "application/json" },
//             body: JSON.stringify({
//                 id_pedido,
//                 direccion_entrega: direccion,
//                 zona_entrega: zona,
//                 transporte_id: transporteSeleccionado.id
//             })
//         });

//         if (!envioRes.ok) {
//             const errorText = await envioRes.text();
//             console.error("❌ Error al registrar el envío:", errorText);
//             alert("Hubo un problema al registrar el envío.");
//             return;
//         }
        
//         if (!envioRes.ok) throw new Error("❌ Error en /envios");

//         const envioData = await envioRes.json();
//         console.log("✅ Envío registrado:", envioData);

//         alert("Pedido, factura y envío registrados correctamente.");
//     } catch (err) {
//         console.error("❌ Error en onApprove:", err.message);
//         mostrarEnConsola("❌ Error en onApprove: " + err.message);
//         alert("Error al procesar la compra: " + err.message);
//     }
// }

//     }).render("#paypal-button-container");

//     (async () => {
//         await cargarProductos();
//         await cargarTransportes();
//     })();



let carrito = [];
let transportesDisponibles = [];
let transporteSeleccionado = null;
let totalTransporte = 0;
const usuario_id = localStorage.getItem("cedula");

async function cargarProductos() {
    const res = await fetch("http://127.0.0.1:8002/api/productos");
    const productos = await res.json();
    const contenedor = document.getElementById("productos");
    contenedor.innerHTML = "";
    productos.forEach(p => {
        const div = document.createElement("div");
        div.className = "producto";
        div.innerHTML = `
            <h4>${p.nombre}</h4>
            <p>${p.descripcion}</p>
            <p><b>Precio:</b> $${p.precio}</p>
            <p><b>Stock:</b> ${p.stock}</p>
            <input type="number" id="cantidad_${p.codigo_producto}" value="1" min="1" max="${p.stock}" style="width:60px;">
            <button onclick='agregarProducto("${p.codigo_producto}", "${p.nombre}", ${p.precio}, ${p.peso_kg})'>Agregar</button>
        `;
        contenedor.appendChild(div);
    });
}

function agregarProducto(codigo, nombre, precio, peso) {
    const cantidad = parseInt(document.getElementById("cantidad_" + codigo).value);
    const item = carrito.find(p => p.codigo === codigo);
    if (item) {
        item.cantidad += cantidad;
    } else {
        carrito.push({ codigo, nombre, precio, peso, cantidad });
    }
    actualizarCarrito();
}

function actualizarCarrito() {
    const lista = document.getElementById("carrito");
    lista.innerHTML = "";
    let total = 0;
    carrito.forEach(p => {
        const li = document.createElement("li");
        li.innerText = `${p.nombre} x ${p.cantidad}`;
        lista.appendChild(li);
        total += p.precio * p.cantidad;
    });
    document.getElementById("total").innerText = `$${(parseFloat(total) + parseFloat(totalTransporte)).toFixed(2)}`;
}

let direccion = "";
let zona = "";

async function cargarTransportes() {
    const clienteRes = await fetch(`http://localhost:3000/usuarios/${usuario_id}`);
    const cliente = await clienteRes.json();
    direccion = cliente.direccion;
    zona = cliente.zona;

    const res = await fetch(`http://localhost:3001/transportes/${zona}`);
    transportesDisponibles = await res.json();

    const selector = document.getElementById("selectorTransporte");
    selector.innerHTML = '<option value="">-- Selecciona un transporte --</option>';
    transportesDisponibles.forEach(t => {
        const opt = document.createElement("option");
        opt.value = t.id;
        opt.textContent = `${t.nombre} - $${t.precio}`;
        selector.appendChild(opt);
    });
}

function actualizarPrecioTransporte() {
    const id = document.getElementById("selectorTransporte").value;
    const transporte = transportesDisponibles.find(t => t.id == id);
    if (transporte) {
        transporteSeleccionado = transporte;
        totalTransporte = parseFloat(transporte.precio);
        document.getElementById("precioTransporte").innerText = `Precio Transporte: $${totalTransporte.toFixed(2)}`;
        actualizarCarrito();
    } else {
        totalTransporte = 0;
        transporteSeleccionado = null;
    }
}

paypal.Buttons({
    createOrder: function(data, actions) {
        const subtotal = carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0);
        const subtotalFinal = (subtotal + totalTransporte)* 0.15;
        const totalFinal = subtotal + totalTransporte + subtotalFinal;

        if (totalFinal <= 0) {
            alert("No puedes pagar un total de $0.00. Agrega productos al carrito.");
            return;
        }
        return actions.order.create({
            purchase_units: [{ amount: { value: totalFinal.toFixed(2) } }]
        });
    },

    onApprove: async function(data, actions) {
        await actions.order.capture();
        alert("¡Pago exitoso!");

        try {
            // 1. Crear pedido
            const pedidoRes = await fetch("http://127.0.0.1:8000/api/pedidos", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    usuario_id,
                    productos: carrito.map(item => ({
                        codigo_producto: item.codigo,
                        cantidad: item.cantidad
                    }))
                })
            });
            if (!pedidoRes.ok) throw new Error("❌ Error en /api/pedidos");

            const pedidoData = await pedidoRes.json();
            const id_pedido = pedidoData.ids_pedidos[0];

            console.log("📝 id_pedido recibido:", id_pedido);

            // 2. Generar factura
            const facturaRes = await fetch("http://127.0.0.1:4000/facturas", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id_pedido, transporte_precio: totalTransporte })
            });
            if (!facturaRes.ok) throw new Error("❌ Error en /facturas");

            // 3. Registrar envío
            const envioRes = await fetch("http://127.0.0.1:3001/envios", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id_pedido,
                    direccion_entrega: direccion,
                    zona_entrega: zona,
                    transporte_id: transporteSeleccionado.id
                })
            });
            if (!envioRes.ok) throw new Error("❌ Error en /envios");

            const envioData = await envioRes.json();
            console.log("✅ Envío registrado:", envioData);

            alert("Pedido, factura y envío registrados correctamente.");
        } catch (err) {
            console.error("❌ Error en onApprove:", err.message);
            alert("Error al procesar la compra: " + err.message);
        }
    }
}).render("#paypal-button-container");

(async () => {
    await cargarProductos();
    await cargarTransportes();
})();