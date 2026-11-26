//Pagina Mis citas (citas.html)
//Usa textoEstado y formatearFecha, que estan en comun.js

const buttonCancelarCita = document.getElementById("cancelarCita");
buttonCancelarCita.addEventListener("click", (event) => {
    cancelarCita();
});

//Nada mas cargar se piden las citas al servidor
cargarCitas();

//Mis citas
//Pide las citas al servidor, rellena la tabla y el desplegable de las que se pueden cancelar
function cargarCitas() {
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
            let filas = "";
            let opciones = '<option value="">Elige la cita que quieres cancelar</option>';
            let cancelables = 0;

            for (let i = 0; i < citas.length; i++) {
                const cita = citas[i];
                const vehiculo = cita.matricula + " - " + cita.marca + " " + cita.modelo;

                filas = filas + "<tr>" +
                    "<td>" + formatearFecha(cita.fecha_cita) + "</td>" +
                    "<td>" + vehiculo + "</td>" +
                    '<td class="celdaMotivo">' + cita.motivo + "</td>" +
                    '<td><span class="etiqueta">' + textoEstado(cita.estado) + "</span></td>" +
                    "</tr>";

                //Solo se pueden cancelar las que el taller todavia no ha empezado
                if (cita.estado == "pendiente" || cita.estado == "confirmada") {
                    opciones = opciones + '<option value="' + cita.id_cita + '">' +
                        formatearFecha(cita.fecha_cita) + " - " + vehiculo + "</option>";
                    cancelables = cancelables + 1;
                }
            }

            if (citas.length == 0) {
                filas = '<tr><td colspan="4">Todavía no has pedido ninguna cita</td></tr>';
            }
            if (cancelables == 0) {
                opciones = '<option value="">No tienes citas que se puedan cancelar</option>';
            }

            document.getElementById("tablaCitas").innerHTML = filas;
            document.getElementById("citaCancelar").innerHTML = opciones;
        } else {
            document.getElementById("mensaje").textContent = data.msg;
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Cancelar cita
function cancelarCita() {
    console.log("Pulsado botón cancelar cita");

    const id_cita = document.getElementById("citaCancelar").value;
    if (id_cita == "") {
        document.getElementById("mensaje").textContent = "Elige primero la cita que quieres cancelar";
        return;
    }

    let request = {};
    request.action = "cancelarCita";
    request.data = [{
        id_cita: id_cita
    }];

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
        document.getElementById("mensaje").textContent = data.msg;

        //Si se ha cancelado se vuelve a pedir la lista para que salga el nuevo estado
        if (data.success) {
            cargarCitas();
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}