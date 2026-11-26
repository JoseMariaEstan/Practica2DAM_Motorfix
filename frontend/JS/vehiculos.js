//Pagina Mis vehiculos (vehiculos.html)

//Botones de la pagina Mis vehiculos
const buttonInsertarVehiculo = document.getElementById("insertarVehiculo");
buttonInsertarVehiculo.addEventListener("click", (event) => {
    insertarVehiculo();
});

const buttonActualizarVehiculo = document.getElementById("actualizarVehiculo");
buttonActualizarVehiculo.addEventListener("click", (event) => {
    actualizarVehiculo();
});

const buttonBorrarVehiculo = document.getElementById("borrarVehiculo");
buttonBorrarVehiculo.addEventListener("click", (event) => {
    borrarVehiculo();
});

//Desplegable para elegir el vehiculo. El evento "change" salta cuando se elige otra opcion
const selectVehiculo = document.getElementById("vehiculoElegido");
selectVehiculo.addEventListener("change", (event) => {
    elegirVehiculo();
});

//Aqui se guardan los vehiculos que devuelve el servidor,
//para rellenar el formulario cuando se elige uno en el desplegable
let listaVehiculos = [];

//Nada mas cargar se piden los vehiculos al servidor
cargarVehiculos();

//Mis vehiculos
//Pide al servidor los vehiculos del cliente y rellena la tabla y el desplegable
function cargarVehiculos() {
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
            listaVehiculos = data.data;

            //Se va montando el HTML de las filas de la tabla y de las opciones del desplegable.
            //Se usan comillas simples para poder poner las comillas dobles del HTML dentro
            let filas = "";
            let opciones = '<option value="">Nuevo vehículo</option>';

            for (let i = 0; i < listaVehiculos.length; i++) {
                const vehiculo = listaVehiculos[i];

                filas = filas + "<tr>" +
                    "<td>" + vehiculo.matricula + "</td>" +
                    "<td>" + vehiculo.marca + "</td>" +
                    "<td>" + vehiculo.modelo + "</td>" +
                    "<td>" + mostrarDato(vehiculo.anio) + "</td>" +
                    "<td>" + mostrarDato(vehiculo.color) + "</td>" +
                    "<td>" + mostrarDato(vehiculo.kilometros) + "</td>" +
                    "<td>" + mostrarDato(vehiculo.combustible) + "</td>" +
                    "</tr>";

                //El value de cada opcion es la posicion del vehiculo en listaVehiculos
                opciones = opciones + '<option value="' + i + '">' +
                    vehiculo.matricula + " - " + vehiculo.marca + " " + vehiculo.modelo + "</option>";
            }

            if (listaVehiculos.length == 0) {
                filas = '<tr><td colspan="7">Todavía no tienes vehículos registrados</td></tr>';
            }

            //innerHTML cambia el contenido de la etiqueta por el HTML que le pasamos
            document.getElementById("tablaVehiculos").innerHTML = filas;
            document.getElementById("vehiculoElegido").innerHTML = opciones;
        } else {
            document.getElementById("mensaje").textContent = data.msg;
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Los datos que no son obligatorios llegan como null si no se rellenaron. En la tabla se pone un guion
function mostrarDato(valor) {
    if (valor == null) {
        return "-";
    }
    return valor;
}

//Al elegir un vehiculo en el desplegable se ponen sus datos en el formulario
function elegirVehiculo() {
    const elegido = document.getElementById("vehiculoElegido").value;

    //Si se elige "Nuevo vehiculo" se vacia el formulario
    if (elegido == "") {
        limpiarFormulario();
        return;
    }

    const vehiculo = listaVehiculos[elegido];
    document.getElementById("matricula").value = vehiculo.matricula;
    document.getElementById("marca").value = vehiculo.marca;
    document.getElementById("modelo").value = vehiculo.modelo;
    document.getElementById("anio").value = vehiculo.anio;
    document.getElementById("color").value = vehiculo.color;
    document.getElementById("kilometros").value = vehiculo.kilometros;
    //Si no tiene combustible guardado se elige la opcion "Sin indicar"
    if (vehiculo.combustible == null) {
        document.getElementById("combustible").value = "";
    } else {
        document.getElementById("combustible").value = vehiculo.combustible;
    }
    document.getElementById("mensaje").textContent = "";
}

//Vacia todas las casillas del formulario
function limpiarFormulario() {
    document.getElementById("vehiculoElegido").value = "";
    document.getElementById("matricula").value = "";
    document.getElementById("marca").value = "";
    document.getElementById("modelo").value = "";
    document.getElementById("anio").value = "";
    document.getElementById("color").value = "";
    document.getElementById("kilometros").value = "";
    document.getElementById("combustible").value = "";
    document.getElementById("mensaje").textContent = "";
}

//Coge los datos del formulario. Lo usan insertar y actualizar
function leerFormulario() {
    let vehiculo = {};
    vehiculo.matricula = document.getElementById("matricula").value;
    vehiculo.marca = document.getElementById("marca").value;
    vehiculo.modelo = document.getElementById("modelo").value;
    vehiculo.anio = document.getElementById("anio").value;
    vehiculo.color = document.getElementById("color").value;
    vehiculo.kilometros = document.getElementById("kilometros").value;
    vehiculo.combustible = document.getElementById("combustible").value;
    return vehiculo;
}

//Insertar vehiculo
function insertarVehiculo() {
    console.log("Pulsado botón añadir vehículo");

    let vehiculo = leerFormulario();

    //Comprobamos que no falten los datos obligatorios antes de enviar
    if (vehiculo.matricula == "" || vehiculo.marca == "" || vehiculo.modelo == "") {
        document.getElementById("mensaje").textContent = "La matrícula, la marca y el modelo son obligatorios";
        return;
    }

    let request = {};
    request.action = "insertVehiculo";
    request.data = [vehiculo];

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

        //Si se ha guardado se vacia el formulario y se vuelve a pedir la lista para que salga en la tabla
        if (data.success) {
            limpiarFormulario();
            cargarVehiculos();
        }
        document.getElementById("mensaje").textContent = data.msg;
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Actualizar vehiculo
function actualizarVehiculo() {
    console.log("Pulsado botón guardar cambios");

    const elegido = document.getElementById("vehiculoElegido").value;
    if (elegido == "") {
        document.getElementById("mensaje").textContent = "Elige primero el vehículo que quieres modificar";
        return;
    }

    let vehiculo = leerFormulario();
    //El servidor necesita el id para saber que vehiculo modificar
    vehiculo.id_vehiculo = listaVehiculos[elegido].id_vehiculo;

    if (vehiculo.matricula == "" || vehiculo.marca == "" || vehiculo.modelo == "") {
        document.getElementById("mensaje").textContent = "La matrícula, la marca y el modelo son obligatorios";
        return;
    }

    let request = {};
    request.action = "updateVehiculo";
    request.data = [vehiculo];

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
            limpiarFormulario();
            cargarVehiculos();
        }
        document.getElementById("mensaje").textContent = data.msg;
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Borrar vehiculo
function borrarVehiculo() {
    console.log("Pulsado botón borrar vehículo");

    const elegido = document.getElementById("vehiculoElegido").value;
    if (elegido == "") {
        document.getElementById("mensaje").textContent = "Elige primero el vehículo que quieres borrar";
        return;
    }

    let request = {};
    request.action = "deleteVehiculo";
    request.data = [{
        id_vehiculo: listaVehiculos[elegido].id_vehiculo
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

        if (data.success) {
            limpiarFormulario();
            cargarVehiculos();
        }
        document.getElementById("mensaje").textContent = data.msg;
    })
    .catch(function (error) {
        console.error(error);
    });
}