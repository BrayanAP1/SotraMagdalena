<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', 
    'secure' => true,     
    'httponly' => true,   
    'samesite' => 'Strict' 
]);
session_start();

// Conexión
$host = "localhost";
$user = "root";
$pass = "";
$db = "enviosdb";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE username = ? AND estado = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            //REGENERAR ID DE SESIÓN
            session_regenerate_id(true);

            $_SESSION['id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            if ($user['rol'] === 'administrador') {
                header("Location: pages/dashboardAdmin.php");
                exit();
            } else {
                header("Location: softwares.php");
                exit();
            }
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - SotraMagdalena</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="css/styleIndex.css">
</head>
<body>
<div class="container">
  <div class="left">
    <h2>Digitaliza tu operación con<br>SotraMagdalena S.A.</h2>
    <p>Simplifica la gestión de envíos y tarifas con una plataforma inteligente diseñada para empresas de transporte.<br>
    Precisión, eficiencia y control, todo en un solo lugar.</p>
    <img src="img/logo.png" alt="Logo SotraMagdalena" width="150" />
  </div>

  <div class="right">
    <div class="login-box">
      <h2>Welcome!</h2>
      <p>Please login to your account.</p>
      
      <form method="POST" action="index.php">
        <div class="input-group">
          <i class="fa fa-user"></i>
          <input type="text" name="username" placeholder="USERNAME" required>
        </div>
        <div class="input-group">
          <i class="fa fa-lock"></i>
          <input type="password" name="password" id="password" placeholder="PASSWORD" required>
        </div>
        <?php if ($error != "") { ?>
          <div class="error-message"><?= $error ?></div>
        <?php } ?>
        <button type="submit">LOGIN</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
