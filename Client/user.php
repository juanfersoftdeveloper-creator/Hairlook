 <?php
  /**
   * Test script – register a user and login without a browser.
   *
   * What it does:
   *  1️⃣   Includes funciones_barberia.php (so all DB helpers are available)
   *  2️⃣   Calls registrar_usuario() with hard‑coded data
   *  3️⃣   Calls iniciar_sesion() with the same credentials
   *  4️⃣   Prints the result to STDOUT (console)
   *  5️⃣   Calls traer_usuarios() to prove the user is really in the DB
   *  6️⃣   Crea un servicio nuevo, crea una cita y lo asocia al servicio
   *
   * Run with: php Client/user.php
   */

  require __DIR__ . '/../Funciones Hairlook/funciones_barberia.php';

  echo "=== Script iniciado ===\n";

  // ---------------------------------------------------------------
  // 1️⃣    DATA TO INSERT
  // ---------------------------------------------------------------
  $nombre   = "Juan Fernando";              // client name
  $correo   = "juan@email.com";        // must be a valid e‑mail
  $contrasena = "clave123";         // password (plain text)
  // ---------------------------------------------------------------

  // ---------------------------------------------------------------
  // 2️⃣    REGISTER THE USER
  // ---------------------------------------------------------------
  echo "=== Registro de usuario ===\n";

  $registroExitoso = registrar_usuario($nombre, $correo, $contrasena);

  if ($registroExitoso) {
      echo "✅ Usuario registrado correctamente.\n";
  } else {
      echo "❌ Error al registrar el usuario.\n";
  }

  // ---------------------------------------------------------------
  // 3️⃣    LOG IN THE SAME USER
  // ---------------------------------------------------------------
  echo "\n=== Inicio de sesión ===\n";

  $loginResult = iniciar_sesion($correo, $contrasena);

  if ($loginResult) {
      echo "✅ Login exitoso. Usuario ID: {$loginResult['ID_Usuario']}\n";
      echo "   Nombre: {$loginResult['Nombre']}\n";
      echo "   Correo: {$loginResult['Correo']}\n";
  } else {
      echo "❌ Login falló.\n";
      exit;
  }

  // ---------------------------------------------------------------
  // 4️⃣    VERIFY USERS IN DB
  // ---------------------------------------------------------------
  echo "\n=== Verificación directa en la tabla `usuario` ===\n";

  $usuarios = traer_usuarios();               // brings *all* rows from the table

  if (empty($usuarios)) {
      echo "⚠️   No hay usuarios en la tabla.\n";
  } else {
      foreach ($usuarios as $u) {
          $highlight = ($u['Correo'] === $correo) ? ' <-- ¡ESTE ES EL NUEVO USUARIO!' : '';
          printf(
              "ID:%6d | Nombre:%-12s | Correo:%s %s\n",
              $u['ID_Usuario'],
              $u['Nombre'],
              $u['Correo'],
              $highlight
          );
      }
  }

  // ---------------------------------------------------------------
  // 5️⃣    CLIENT FLOW: obtener servicios, profesional y crear cita
  // ---------------------------------------------------------------
  echo "\n=== Flujo del cliente: obtener servicios y profesional ===\n";

  $servicios = traer_servicios();          // <-- lista los servicios existentes
  if (empty($servicios)) {
      echo "❌ No se encontraron servicios en la DB.\n";
      exit;
  }
 $id_servicio = $servicios[0]['ID_Servicio'];   // tomamos el primero
 echo "✅ Servicio seleccionado: {$servicios[0]['Nombre']} (ID: $id_servicio)\n";

  $profesionales = traer_profesionales();   // <-- lista los barberos
  if (empty($profesionales)) {
      echo "❌ No hay barberos en la DB.\n";
      exit;
  }
  $id_profesional = $profesionales[0]['ID_Profesional']; // usamos el primero
  echo "✅ Profesional seleccionado: {$profesionales[0]['Nombre']} (ID: $id_profesional)\n";

  // ---------- CREAR EL SERVICIO DE PRUEBA ----------
  echo "\n=== Creando servicio de prueba (si no existía) ===\n";
  $servicioRegistro = administrar_servicios(
       null,                // id (null → insertar)
      'actualizar',        // acción a ejecutar
      [
          'Nombre'        => 'Servicio Prueba Premium',
          'Descripcion'   => 'Servicio de prueba añadido vía script',
          'Precio'        => 75.00,
          'Duracion_min'  => 45
      ]
  );

  if ($servicioRegistro === false) {
      echo "❌ No se pudo crear el servicio de prueba.\n";
      exit;
  }
  echo "✅ Servicio de prueba creado.\n";

  //este condicional devuelve el ID del servicio recién creado, o el ID del servicio 
  //existente si ya estaba en la DB (si el nombre es único)
  if ($servicioRegistro !== null) {
      $id_servicio = $servicioRegistro; // el ID generado
  }

  echo "✅ ID del servicio usado: $id_servicio\n";

  // ---------- CREAR CITA ----------
  echo "\n=== Creando cita ===\n";
  $fecha     = '2026-12-15';   // agenda ficticia
  $hora      = '16:30';
  $tipo      = 'local';      // puede ser 'local' o 'domicilio'

  $cita_id = crear_cita(
      $loginResult['ID_Usuario'],
      $id_profesional,
      $fecha,
      $hora,
      $tipo
  );

  if (!$cita_id) {
      echo "❌ No se pudo crear la cita.\n";
      exit;
  }
  echo "✅ Cita creada (ID: $cita_id)\n";

  echo "\n=== Agregar servicio a la cita ===\n";
  $precio_especifico = 75.00;   // precio que acordamos para esta cita
  $agregado = agregar_servicio_a_cita($cita_id, $id_servicio, $precio_especifico);
  if ($agregado === false) {
      echo "❌ No se pudo asociar el servicio a la cita.\n";
      exit;
  }
  echo "✅ Servicio añadido a la cita (ID servicio: $id_servicio, Precio: $precio_especifico)\n";

  // ---------- VERIFICAR RESULTADO ----------
  echo "\n=== Verificando la agenda generada ===\n";
  $citaCompleta = traer_agenda_por_cita($cita_id);
  echo json_encode($citaCompleta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  echo "\n=== Resumen visual ===\n";
  echo "📅 Fecha / Hora   : {$citaCompleta['Fecha_hora']}\n";
  echo "👨‍ Profesional   : {$citaCompleta['Profesional']}\n";
  echo "🛠️   Servicios     : ";
  foreach ($citaCompleta['Servicios'] as $s) {
      echo "- {$s['Nombre']} (Precio: \${$s['Precio_aplicado']}) ";
  }
  echo "\n⚙️   Estado inicial : {$citaCompleta['Estado']}\n";

  echo "\n=== Script finalizado ===\n";
  ?>
