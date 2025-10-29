document.addEventListener("DOMContentLoaded", () => {
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm-password");
    const togglePassword = document.getElementById("togglePassword");
    const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");

    const regex = {
        upper: /[A-Z]/,
        number: /[0-9]/,
        special: /[!@#$%^&*(),.?":{}|<>]/
    };

    // Inicializar tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

    // Función para actualizar tooltip dinámicamente
    function updateTooltip(el, messages) {
        const tooltip = bootstrap.Tooltip.getInstance(el);
        el.setAttribute("data-bs-original-title", messages);
        tooltip.setContent({ '.tooltip-inner': messages });
    }

    // Validar seguridad de la contraseña
    passwordInput.addEventListener("input", () => {
        const value = passwordInput.value;
        let messages = [];

        if (value.length < 8) messages.push("Mínimo 8 caracteres");
        if (!regex.upper.test(value)) messages.push("Al menos una mayúscula");
        if (!regex.number.test(value)) messages.push("Al menos un número");
        if (!regex.special.test(value)) messages.push("Al menos un caracter especial");

        if (messages.length === 0) {
            updateTooltip(passwordInput, "✔ Contraseña válida");
        } else {
            updateTooltip(passwordInput, messages.join(" • "));
        }
    });

    // Validar coincidencia
    function checkPasswordMatch() {
        if (passwordInput.value === confirmPasswordInput.value) {
            updateTooltip(confirmPasswordInput, "✔ Las contraseñas coinciden");
        } else {
            updateTooltip(confirmPasswordInput, "✖ Las contraseñas no coinciden");
        }
    }

    passwordInput.addEventListener("input", checkPasswordMatch);
    confirmPasswordInput.addEventListener("input", checkPasswordMatch);

    // Botón ojito para contraseña
    togglePassword.addEventListener("click", function () {
        const icon = this.querySelector("i");
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            passwordInput.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    });

    // Botón ojito para repetir contraseña
    toggleConfirmPassword.addEventListener("click", function () {
        const icon = this.querySelector("i");
        if (confirmPasswordInput.type === "password") {
            confirmPasswordInput.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            confirmPasswordInput.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    });

    // Validar al enviar formulario
    document.querySelector("form").addEventListener("submit", (e) => {
        let pass = passwordInput.value;
        if (
            pass.length < 8 ||
            !regex.upper.test(pass) ||
            !regex.number.test(pass) ||
            !regex.special.test(pass)
        ) {
            e.preventDefault();
            alert("La contraseña no cumple los requisitos.");
            return;
        }
        if (pass !== confirmPasswordInput.value) {
            e.preventDefault();
            alert("Las contraseñas no coinciden.");
        }
    });
});
