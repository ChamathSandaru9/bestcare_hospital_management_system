
document.addEventListener("DOMContentLoaded", function () {

    // Show / Hide Password

    const togglePassword = document.getElementById("togglePassword");
    const password = document.getElementById("password");


    if(togglePassword && password){

        togglePassword.addEventListener("click", function(){

            const icon = this.querySelector("i");


            if(password.type === "password"){

                password.type = "text";

                icon.classList.remove("fa-eye");

                icon.classList.add("fa-eye-slash");

            }

            else{

                password.type = "password";

                icon.classList.remove("fa-eye-slash");

                icon.classList.add("fa-eye");

            }


        });

    }


    // Login Form Validation

    const loginForm = document.getElementById("loginForm");


    if(loginForm){

        loginForm.addEventListener("submit", function(event){


            const loginInput = document.getElementById("login").value.trim();

            const passwordInput = document.getElementById("password").value.trim();



            // Empty username/email check

            if(loginInput === ""){

                alert("Please enter your username or email.");

                event.preventDefault();

                return;

            }



            // Empty password check

            if(passwordInput === ""){

                alert("Please enter your password.");

                event.preventDefault();

                return;

            }



            // Password minimum length

            if(passwordInput.length < 6){

                alert("Password must contain at least 6 characters.");

                event.preventDefault();

                return;

            }


        });

    }


});