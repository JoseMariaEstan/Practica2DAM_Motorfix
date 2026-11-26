//Pagina de login (index.html)

const buttonEntrar = document.getElementById("entrar");
buttonEntrar.addEventListener("click", (event) => {
    loginUsuario();
});

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

    fetch(urlUsuario, {
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
            //El admin usa el mismo panel que oficina
            if (usuarioLogueado.rol == "admin") {
                window.location.href = "oficina.html";
            }
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}