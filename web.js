// --- NAVBAR MOBILE MENU ---
const menu = document.querySelector("#mobile-menu");
const menuLinks = document.querySelector(".navbar__menu");

if (menu && menuLinks) {
  menu.addEventListener("click", () => {
    menu.classList.toggle("is-active");
    menuLinks.classList.toggle("active");
  });
}

// --- MODAL ELEMENTS ---
const loginModal = document.getElementById("login-modal");
const signupModal = document.getElementById("signup-modal");

// --- OPENING & CLOSING LOGIC ---
const getStartedBtn = document.querySelector("#get-started");
if (getStartedBtn && loginModal) {
  getStartedBtn.addEventListener("click", () => {
    loginModal.style.display = "flex";
  });
}

const signupBtn = document.querySelector(".navbar__btn .button");
if (signupBtn && signupModal) {
  signupBtn.addEventListener("click", (e) => {
    e.preventDefault();
    signupModal.style.display = "flex";
  });
}

const toLoginLink = document.getElementById("to-login");
if (toLoginLink && signupModal && loginModal) {
  toLoginLink.addEventListener("click", (e) => {
    e.preventDefault();
    signupModal.style.display = "none";
    loginModal.style.display = "flex";
  });
}

document.querySelectorAll(".close-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    if (loginModal) loginModal.style.display = "none";
    if (signupModal) signupModal.style.display = "none";
  });
});

window.addEventListener("click", (e) => {
  if (e.target === loginModal) loginModal.style.display = "none";
  if (e.target === signupModal) signupModal.style.display = "none";
});

// --- FORM SUBMISSIONS ---
window.addEventListener("DOMContentLoaded", () => {
  // --- SIGN UP SUBMISSION ---
  const signupForm = document.getElementById("signup-form");
  if (signupForm) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const formData = new FormData(signupForm);
      fetch("signup.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.text())
        .then((data) => {
          if (data.trim() === "success") {
            alert("Account created successfully!");
            signupForm.reset();
            signupModal.style.display = "none";
            loginModal.style.display = "flex"; // Automatically open login after signup
          } else {
            alert("PHP Error: " + data);
          }
        })
        .catch((err) => console.error("Fetch Error:", err));
    });
  }

  // --- LOGIN LOGIC (UPDATED FOR PROFILE SETUP) ---
// --- LOGIN LOGIC ---
const loginForm = document.getElementById("login-form");
if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(loginForm);

    fetch("login.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((data) => {
        const response = data.trim(); // Cleans up any hidden spaces
        console.log("Server says:", response);

        if (response === "success") {
          window.location.href = "dashboard.php";
        } 
        else if (response === "setup_required") {
          // THIS IS THE REDIRECT YOU NEED:
          window.location.href = "profile_setup.php"; 
        } 
        else {
          alert("Login Failed: " + response);
        }
      })
      .catch((err) => console.error("Fetch Error:", err));
  });
}
});
