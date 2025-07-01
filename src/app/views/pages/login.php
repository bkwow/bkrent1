<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Iniciar Sesión' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="col-md-6 login-logo-section">
            <img src="public/img/logo-1.jpg" alt="Logo Lesly Rent a Car">
        </div>
        <div class="col-md-6 login-form-section">
            <h2 class="fw-light">BIENVENIDO a</h2>
            <h2 class="highlight mb-3"><?= e($settings['nombre_sistema'] ?? 'Sistema Rent a Car') ?></h2>
            <p class="text-muted mb-4">Bienvenido de nuevo, inicie sesión en una cuenta</p>
            
            <!-- ======================================================== -->
            <!--     AQUÍ SE MOSTRARÁN LOS MENSAJES DE ERROR/ÉXITO      -->
            <!-- ======================================================== -->
            <?php display_flash_messages(); ?>
            
            <form action="index.php?action=do_login" method="POST">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="identifier" placeholder="Usuario o Email" required>
                </div>
                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Ingresar contraseña" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
                    </button>
                </div>
            </form>
            <div class="text-center mt-3 small">
                <?php if ($settings['forg1'] == '1'): ?>
                    <a href="index.php?page=forgot_password">¿Olvidaste tu contraseña?</a>
                <?php endif; ?>
                
                <?php if ($settings['on_registro'] == '1' && $settings['forg1'] == '1'): ?>
                     | 
                <?php endif; ?>

                <?php if ($settings['on_registro'] == '1'): ?>
                    <a href="index.php?page=register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>