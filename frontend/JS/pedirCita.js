//Pagina Pedir cita (Pedircita.html)

const buttonPedirCita = document.getElementById("pedirCita");
buttonPedirCita.addEventListener("click", (event) => {
    pedirCita();
});

//Nada mas cargar se rellena el desplegable con los vehiculos
cargarVehiculosCita();

//Pedir cita
//Rellena el desplegable de Pedir cita con los vehiculos del cliente
function cargarVehiculosCita() {
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

            //Si no tiene vehiculos no puede pedir cita
            if (vehiculos.length == 0) {
                document.getElementById("vehiculoCita").innerHTML = '<option value="">No tienes vehículos registrados</option>';
                document.getElementById("mensaje").textContent = "Primero añade un vehículo en Mis vehículos";
                return;
            }

            //Aqui el value de cada opcion es el id_vehiculo, que es lo que necesita el servidor
            let opciones = '<option value="">Elige un vehículo</option>';
            for (let i = 0; i < vehiculos.length; i++) {
                const vehiculo = vehiculos[i];
                opciones = opciones + '<option value="' + vehiculo.id_vehiculo + '">' +
                    vehiculo.matricula + " - " + vehiculo.marca + " " + vehiculo.modelo + "</option>";
            }
            document.getElementById("vehiculoCita").innerHTML = opciones;
        } else {
            document.getElementById("mensaje").textContent = data.msg;
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Envia la cita al servidor
function pedirCita() {
    console.log("Pulsado botón pedir cita");

    let cita = {};
    cita.id_vehiculo = document.getElementById("vehiculoCita").value;
    cita.fecha_cita = document.getElementById("fechaHora").value;
    cita.motivo = document.getElementById("motivo").value;

    //Comprobamos que no haya campos vacios antes de enviar.
    //Que la fecha sea futura lo comprueba el servidor
    if (cita.id_vehiculo == "" || cita.fecha_cita == "" || cita.motivo == "") {
        document.getElementById("mensaje").textContent = "Elige el vehículo, la fecha y escribe el motivo";
        return;
    }

    let request = {};
    request.action = "pedirCita";
    request.data = [cita];

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

        //Si se ha guardado se vacia el formulario para poder pedir otra
        if (data.success) {
            document.getElementById("vehiculoCita").value = "";
            document.getElementById("fechaHora").value = "";
            document.getElementById("motivo").value = "";
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}