//Pagina Resumen (cliente.html)
//Usa textoEstado y formatearFecha, que estan en comun.js

//Nada mas cargar se rellenan las tarjetas con los datos reales
resumenVehiculos();
resumenCitas();

//Tarjeta "Mis vehiculos": cuantos tiene y cuales son
function resumenVehiculos() {
    let request = {};
    request.action = "misVehiculos";
    request.data = [];

    fetch(url, {
        method: "POST",
        body: JSON.stringify(request),
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);

        if (data.success) {
            const vehiculos = data.data;
            document.getElementById("numeroVehiculos").textContent = vehiculos.length;

            //Se juntan los nombres separados por comas: "Seat León, Renault Clio"
            let nombres = "";
            for (let i = 0; i < vehiculos.length; i++) {
                if (i > 0) {
                    nombres = nombres + ", ";
                }
                nombres = nombres + vehiculos[i].marca + " " + vehiculos[i].modelo;
            }
            if (vehiculos.length == 0) {
                nombres = "Todavía no has añadido ninguno";
            }
            document.getElementById("nombresVehiculos").textContent = nombres;
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Tarjeta "Proxima cita": la cita activa con la fecha mas cercana
function resumenCitas() {
    let request = {};
    request.action = "misCitas";
    request.data = [];

    fetch(url, {
        method: "POST",
        body: JSON.stringify(request),
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);

        if (data.success) {
            const citas = data.data;
            let proxima = null;

            for (let i = 0; i < citas.length; i++) {
                const cita = citas[i];
                //Solo cuentan las citas que siguen activas
                if (cita.estado == "pendiente" || cita.estado == "confirmada" || cita.estado == "en_reparacion") {
                    //Las fechas "2026-12-02 10:30:00" se pueden comparar como texto:
                    //la que es menor es la que va antes
                    if (proxima == null || cita.fecha_cita < proxima.fecha_cita) {
                        proxima = cita;
                    }
                }
            }

            if (proxima == null) {
                document.getElementById("proximaFecha").textContent = "No tienes citas pendientes";
                document.getElementById("proximaEstado").textContent = "Sin citas";
                document.getElementById("proximaVehiculo").textContent = "";
                document.getElementById("proximaMotivo").textContent = "Pide una cita cuando la necesites";
            } else {
                document.getElementById("proximaFecha").textContent = formatearFecha(proxima.fecha_cita);
                document.getElementById("proximaEstado").textContent = textoEstado(proxima.estado);
                document.getElementById("proximaVehiculo").textContent = proxima.matricula + " - " + proxima.marca + " " + proxima.modelo;
                document.getElementById("proximaMotivo").textContent = proxima.motivo;
            }
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}