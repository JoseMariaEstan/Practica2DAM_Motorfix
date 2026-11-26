//Pagina de crear cuenta (registroUsuario.html)

const buttonRegistrar = document.getElementById("registrar");
buttonRegistrar.addEventListener("click", (event) => {
    registrarUsuario();
});

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

        //Si se ha creado la cuenta, entramos directamente al area de cliente
        if (data.success) {
            window.location.href = "cliente.html";
        }
    })
    .catch(function (error) {
        console.error(error);
    });
}