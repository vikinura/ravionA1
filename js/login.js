function login() {
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value;
  const error = document.getElementById('error');

  // Simulasi data login — sementara hardcode dulu
  const admin = { username: "admin", password: "admin123" };
  const user = { username: "user", password: "user123" };

  if (username === admin.username && password === admin.password) {
    // login sebagai admin
    localStorage.setItem("role", "admin");
    window.location.href = "admin/admin_page.html";
  } 
  else if (username === user.username && password === user.password) {
    // login sebagai user
    localStorage.setItem("role", "user");
    window.location.href = "products.html";
  } 
  else {
    error.textContent = "Username atau password salah!";
  }
}
