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

/* Respuesta
{
    data: [{}, {}],
    success: true/false,
    msg: "",
    error: [{msg: , errro:}, {}...]
}

// Envío
{
    action: login/registro/insert/delete/update/get,
    data: [{}],
    filters: {},
    page: ,
    offset: ,
}
*/