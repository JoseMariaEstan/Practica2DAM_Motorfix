//URL del servicio web. Es relativa a la carpeta frontend, asi funciona
//da igual en que ruta de localhost tengas el proyecto
const url = "../backend/modelo/sw.pagina.php";

//Botones que llaman a las funciones concretas cuando se produce el evento "click"
//Este archivo lo usan varias paginas, por eso solo enganchamos
//el evento si el boton existe en la pagina actual
const buttonEntrar = document.getElementById("entrar");
if (buttonEntrar) {
    buttonEntrar.addEventListener("click", (event) => {
        loginUsuario();
    });
}

const buttonRegistrar = document.getElementById("registrar");
if (buttonRegistrar) {
    buttonRegistrar.addEventListener("click", (event) => {
        registrarUsuario();
    });
}

const buttonCerrarSesion = document.getElementById("cerrarSesion");
if (buttonCerrarSesion) {
    buttonCerrarSesion.addEventListener("click", (event) => {
        cerrarSesion();
    });
}

//Botones de la pagina Mis vehiculos
const buttonInsertarVehiculo = document.getElementById("insertarVehiculo");
if (buttonInsertarVehiculo) {
    buttonInsertarVehiculo.addEventListener("click", (event) => {
        insertarVehiculo();
    });
}

const buttonActualizarVehiculo = document.getElementById("actualizarVehiculo");
if (buttonActualizarVehiculo) {
    buttonActualizarVehiculo.addEventListener("click", (event) => {
        actualizarVehiculo();
    });
}

const buttonBorrarVehiculo = document.getElementById("borrarVehiculo");
if (buttonBorrarVehiculo) {
    buttonBorrarVehiculo.addEventListener("click", (event) => {
        borrarVehiculo();
    });
}

//Desplegable para elegir el vehiculo. El evento "change" salta cuando se elige otra opcion
const selectVehiculo = document.getElementById("vehiculoElegido");
if (selectVehiculo) {
    selectVehiculo.addEventListener("change", (event) => {
        elegirVehiculo();
    });
}

//Aqui se guardan los vehiculos que devuelve el servidor,
//para rellenar el formulario cuando se elige uno en el desplegable
let listaVehiculos = [];

//Si estamos en el area de cliente (tiene el boton de cerrar sesion),
//nada mas cargar se comprueba que hay una sesion iniciada
if (buttonCerrarSesion) {
    comprobarSesion();
}

//Si estamos en Mis vehiculos, nada mas cargar se piden los vehiculos al servidor
if (selectVehiculo) {
    cargarVehiculos();
}

//Funciones de los botones
//Login
function loginUsuario() {
    console.log("Pulsado botón entrar");

    //A cada propiedad del objeto se le asigna el valor de la casilla del form correspondiente
    let usuario = {};
    usuario.email = document.getElementById("email").value;
    usuario.password = document.getElementById("password").value;

    //Comprobamos que no haya campos vacios antes de enviar
    if (usuario.email == "" || usuario.password == "") {
        document.getElementById("mensaje").textContent = "Introduce el email y la contraseña";
        return;
    }

    //Este es el objeto peticion que usa el backend para saber que accion tiene que realizar y con que datos
    let request = {};
    request.action = "login";
    request.data = [usuario];

    fetch(url, {
        method: "POST",
        body: JSON.stringify(request),
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Muestra la respuesta del servidor en la consola del navegador
        document.getElementById("mensaje").textContent = data.msg;

        if (data.success) {
            const usuarioLogueado = data.data[0];
            console.log("Nombre: " + usuarioLogueado.nombre);
            console.log("Rol: " + usuarioLogueado.rol);

            //Segun el rol del usuario le mandamos a su panel
            if (usuarioLogueado.rol == "cliente") {
                window.location.href = "cliente.html";
            }
            if (usuarioLogueado.rol == "oficina") {
                window.location.href = "oficina.html";
            }
            if (usuarioLogueado.rol == "mecanico") {
                window.location.href = "mecanico.html";
            }
            if (usuarioLogueado.rol == "admin") {
                window.location.href = "admin.html";
            }
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Registro
function registrarUsuario() {
    console.log("Pulsado botón registrar");

    //A cada propiedad del objeto se le asigna el valor de la casilla del form correspondiente
    let usuario = {};
    usuario.nombre = document.getElementById("nombre").value;
    usuario.apellidos = document.getElementById("apellidos").value;
    usuario.dni = document.getElementById("dni").value;
    usuario.telefono = document.getElementById("telefono").value;
    usuario.email = document.getElementById("email").value;
    usuario.password = document.getElementById("password").value;
    usuario.password2 = document.getElementById("password2").value;

    //Comprobamos que no haya campos vacios antes de enviar
    if (usuario.nombre == "" || usuario.apellidos == "" || usuario.dni == "" ||
        usuario.telefono == "" || usuario.email == "" || usuario.password == "") {
        document.getElementById("mensaje").textContent = "Rellena todos los campos";
        return;
    }
    if (usuario.password != usuario.password2) {
        document.getElementById("mensaje").textContent = "Las contraseñas no coinciden";
        return;
    }

    //Este es el objeto peticion que usa el backend para saber que accion tiene que realizar y con que datos
    let request = {};
    request.action = "registro";
    request.data = [usuario];

    //el fetch lo hacemos para enviar al servidor los datos del usuario en formato JSON,
    //ya que el backend espera recibir los datos en este formato para poder procesarlos correctamente
    fetch(url, {
        method: "POST",
        body: JSON.stringify(request),
        headers: {
            "Content-Type": "application/json",
        },
    })
    .then(response => response.json())
    .then(data => {
        console.log(data); // Muestra la respuesta del servidor en la consola del navegador
        document.getElementById("mensaje").textContent = data.msg;

        //Si se ha creado la cuenta, entramos directamente al area de cliente
        if (data.success) {
            window.location.href = "cliente.html";
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Comprobar sesion
//Se pregunta al servidor quien ha iniciado sesion. Si no hay nadie, se vuelve al login
function comprobarSesion() {
    let request = {};
    request.action = "sesion";
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
            const usuarioLogueado = data.data[0];
            //Se pone el nombre de quien ha entrado en lugar del de ejemplo
            document.getElementById("nombreUsuario").textContent = usuarioLogueado.nombre;
            //El saludo "Hola, ..." solo esta en el Resumen (cliente.html)
            const saludo = document.getElementById("saludo");
            if (saludo) {
                saludo.textContent = "Hola, " + usuarioLogueado.nombre;
            }
        } else {
            window.location.href = "index.html";
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}

//Cerrar sesion
function cerrarSesion() {
    console.log("Pulsado botón cerrar sesión");

    let request = {};
    request.action = "logout";
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
        window.location.href = "index.html";
    })
    .catch(function (error) {
        console.error(error);
    });
}

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

/* Respuesta
{
    data: [{}, {}],
    success: true/false,
    msg: "",
    error: [{msg: , errro:}, {}...]
}

// Envío
{
    action: login/registro/sesion/logout/pedirCita/misCitas/misVehiculos/insertVehiculo/updateVehiculo/deleteVehiculo,
    data: [{}],
    filters: {},
    page: ,
    offset: ,
}
*/