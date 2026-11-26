//Codigo comun que usan todas las paginas. Se carga ANTES que el js de cada pagina

//URL de cada servicio web. Son relativas a la pagina HTML (carpeta frontend), no a este archivo,
//asi funciona da igual en que ruta de localhost tengas el proyecto
const urlUsuario = "../backend/modelo/SW/sw.usuario.php";
const urlVehiculo = "../backend/modelo/SW/sw.vehiculo.php";
const urlCita = "../backend/modelo/SW/sw.cita.php";

//El boton de cerrar sesion esta en el menu de todas las paginas del area de cliente
const buttonCerrarSesion = document.getElementById("cerrarSesion");
if (buttonCerrarSesion) {
    buttonCerrarSesion.addEventListener("click", (event) => {
        cerrarSesion();
    });
}

//Si estamos en el area de cliente (tiene el boton de cerrar sesion),
//nada mas cargar se comprueba que hay una sesion iniciada
if (buttonCerrarSesion) {
    comprobarSesion();
}

//Comprobar sesion
//Se pregunta al servidor quien ha iniciado sesion. Si no hay nadie, se vuelve al login
function comprobarSesion() {
    let request = {};
    request.action = "sesion";
    request.data = [];

    fetch(urlUsuario, {
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

    fetch(urlUsuario, {
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

//El estado se guarda como "en_reparacion", "pendiente"... Aqui se pasa a un texto para mostrarlo
//Lo usan Mis citas y el Resumen
function textoEstado(estado) {
    if (estado == "pendiente") {
        return "Pendiente";
    }
    if (estado == "confirmada") {
        return "Confirmada";
    }
    if (estado == "en_reparacion") {
        return "En reparación";
    }
    if (estado == "finalizada") {
        return "Finalizada";
    }
    if (estado == "facturada") {
        return "Facturada";
    }
    if (estado == "cancelada") {
        return "Cancelada";
    }
    return estado;
}

//La fecha llega como "2026-12-02 10:30:00" y se muestra como "02/12/2026 10:30".
//substring(inicio, fin) coge el trozo del texto entre esas dos posiciones
//Lo usan Mis citas y el Resumen
function formatearFecha(fecha) {
    const anio = fecha.substring(0, 4);
    const mes = fecha.substring(5, 7);
    const dia = fecha.substring(8, 10);
    const hora = fecha.substring(11, 16);
    return dia + "/" + mes + "/" + anio + " " + hora;
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
    action: login/registro/sesion/logout/pedirCita/misCitas/cancelarCita/misVehiculos/insertVehiculo/updateVehiculo/deleteVehiculo,
    data: [{}],
    filters: {},
    page: ,
    offset: ,
}
*/