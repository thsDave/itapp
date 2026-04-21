<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Error interno — IT App</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
         background:#1a1a2e;color:#e0e0e0;display:flex;align-items:center;
         justify-content:center;min-height:100vh;padding:2rem}
    .box{background:#16213e;border:1px solid #0f3460;border-radius:10px;
         max-width:560px;width:100%;padding:2.5rem;text-align:center}
    h1{font-size:5rem;font-weight:700;color:#e74c3c;line-height:1}
    h2{font-size:1.3rem;margin:1rem 0 .5rem;color:#fff}
    p{color:#999;font-size:.9rem;line-height:1.6}
    .ref{font-size:.75rem;color:#555;margin-top:1.5rem}
    a{display:inline-block;margin-top:1.5rem;padding:.6rem 1.5rem;
      background:#e74c3c;color:#fff;border-radius:6px;text-decoration:none;
      font-size:.9rem}
    a:hover{background:#c0392b}
    pre{text-align:left;background:#0d0d1a;border:1px solid #333;border-radius:6px;
        padding:1rem;margin-top:1.5rem;overflow:auto;font-size:.78rem;
        color:#e74c3c;max-height:300px}
  </style>
</head>
<body>
  <div class="box">
    <h1>500</h1>
    <h2>Error interno del servidor</h2>
    <p>Algo salió mal. El error ha sido registrado y será atendido a la brevedad.</p>

    <?php if (defined('APP_DEBUG') && APP_DEBUG && isset($exception)): ?>
      <pre><?= htmlspecialchars(
          $exception->getMessage() . "\n\n" . $exception->getTraceAsString(),
          ENT_QUOTES, 'UTF-8'
      ) ?></pre>
    <?php endif; ?>

    <?php if (isset($refId)): ?>
      <p class="ref">Referencia: <?= htmlspecialchars($refId) ?></p>
    <?php endif; ?>

    <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>/dashboard">
      ← Volver al inicio
    </a>
  </div>
</body>
</html>
