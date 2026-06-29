# Hairlook — Roadmap de prompts para Claude Code

Plan de trabajo dividido en bloques pequeños. Pega cada prompt en Claude Code
**en orden**, uno a la vez, y verifica que corra (`npm run dev`) antes de pasar
al siguiente. No mezcles bloques — cada uno depende del anterior.

---

## Bloque 1 — App.jsx con rutas y AuthProvider

```
Contexto: proyecto Hairlook, frontend en React (Vite) en frontend/.
Ya existen estos componentes en frontend/src/components/auth/:
- Login.jsx
- RegisterCliente.jsx
- RegisterProfesional.jsx

Y estos en frontend/src/context/ y frontend/src/services/:
- AuthContext.jsx (exporta AuthProvider y useAuth)
- authService.js

TAREA:
1. Instala react-router-dom si no está: npm install react-router-dom
2. Crea/edita frontend/src/App.jsx con:
   - BrowserRouter envolviendo todo
   - AuthProvider envolviendo las rutas
   - Rutas:
     "/" → Login
     "/registro-cliente" → RegisterCliente
     "/registro-profesional" → RegisterProfesional
     "/inicio" → un componente placeholder temporal que diga
       "Home Cliente — en construcción" (lo reemplazaremos en el siguiente bloque)
     "/pro/inicio" → un componente placeholder temporal que diga
       "Home Profesional — en construcción"
3. Verifica que frontend/src/main.jsx renderice <App /> correctamente
4. Importa la fuente 'DM Sans' de Google Fonts en frontend/index.html (en el <head>)
5. Corre npm run dev y confirma que no hay errores en consola

No modifiques el contenido de Login.jsx, RegisterCliente.jsx ni RegisterProfesional.jsx,
solo conéctalos a las rutas.
```

---

## Bloque 2 — Home Cliente

```
Contexto: proyecto Hairlook, React en frontend/. Ya existe el sistema de login
(AuthContext, rutas en App.jsx). El diseño visual de referencia está en el
archivo hairlook_pro-2.html adjunto — usa esa estructura/colores exactos para
la pantalla "Home" del cliente (sección id="tab-inicio" del HTML).

Paleta de colores (variables CSS):
--navy: #1e2d4a; --bg: #f2f3f7; --white: #fff; --gray: #8a95a3;
--lgray: #f2f3f5; --border: #eeeff2; --green: #22c55e; --red: #ef4444;

TAREA:
Crea frontend/src/components/cliente/Home.jsx + Home.css con:
1. Header con logo Hairlook + avatar
2. Saludo "Hola, [nombre] 👋" usando el nombre del usuario desde useAuth()
3. Card grande de "Agendar cita" (CTA principal, navega a /agendar)
4. Grid 2x2 de accesos rápidos: Peluqueros, Mis citas, Perfil, Notificaciones
5. Card de promoción/oferta (estático por ahora)
6. Bottom navigation con 5 botones: Inicio, Cercanos, Agendar, Mis citas, Perfil
   (usar react-router-dom NavLink o useNavigate, resaltando el activo)

Por ahora usa datos hardcodeados para el contador de notificaciones (ej: 2).
Conecta la ruta "/inicio" en App.jsx a este nuevo componente, reemplazando el
placeholder temporal.

Mantén el mismo patrón de comentarios JSDoc que tienen Login.jsx y RegisterCliente.jsx.
```

---

## Bloque 3 — Agendar Cita (cliente)

```
Contexto: proyecto Hairlook, React en frontend/. Referencia visual en
hairlook_pro-2.html, sección id="tab-agendar" (flujo de 3 pasos: Servicio,
Fecha y Profesional, Estilo).

TAREA:
Crea frontend/src/components/cliente/AgendarCita.jsx + AgendarCita.css con:
1. Stepper visual de 3 pasos (círculos numerados, línea conectora)
2. Paso 1: lista de servicios seleccionables (radio) + modalidad (local/domicilio)
   Datos hardcodeados por ahora:
   [
     { id:'corte', nombre:'Corte de cabello', dur:'45 min', precio:25000 },
     { id:'barba', nombre:'Corte y barba', dur:'60 min', precio:35000 },
     { id:'tinte', nombre:'Tinte', dur:'90 min', precio:55000 },
     { id:'tratamiento', nombre:'Tratamiento capilar', dur:'60 min', precio:45000 },
   ]
3. Paso 2: selección de profesional (lista hardcodeada) + selector de día
   (próximos 5 días) + grid de horas disponibles (hardcodeadas)
4. Paso 3: selección de estilo de referencia (4 opciones visuales) + resumen
   de la cita completa + botón "Confirmar reserva"
5. Botones "Atrás" / "Continuar" que avanzan el stepper
6. Al confirmar, muestra una pantalla de éxito simple y navega a /mis-citas
   después de 1.5 segundos

Usa useState para manejar: paso actual, servicio elegido, modalidad, profesional,
día, hora, estilo elegido.

Mantén el mismo patrón de comentarios JSDoc que el resto del proyecto.
Conecta la ruta "/agendar" en App.jsx.
```

---

## Bloque 4 — Mis Citas (cliente)

```
Contexto: proyecto Hairlook, React en frontend/. Referencia visual en
hairlook_pro-2.html, sección id="tab-citas" (tabs Próximas/Historial) y el
modal de calificación con estrellas.

TAREA:
Crea frontend/src/components/cliente/MisCitas.jsx + MisCitas.css con:
1. Tabs "Próximas" / "Historial" (toggle con useState)
2. Tab Próximas: lista de citas con estado (Confirmada/Pendiente), botón
   "Cancelar". Datos hardcodeados de ejemplo (2-3 citas).
3. Tab Historial: lista de citas completadas con precio, botón "⭐ Calificar"
   si no tiene calificación aún
4. Modal de calificación (overlay desde abajo) con:
   - 5 estrellas interactivas (click para seleccionar puntuación 1-5)
   - Textarea de comentario opcional
   - Botón "Enviar calificación" (por ahora solo cierra el modal y muestra alert)
5. Botón "+ Nueva cita" que navega a /agendar

Usa useState para: tab activo, modal abierto/cerrado, puntuación seleccionada.

Mantén el mismo patrón de comentarios JSDoc.
Conecta la ruta "/mis-citas" en App.jsx.
```

---

## Bloque 5 — Home Profesional

```
Contexto: proyecto Hairlook, React en frontend/. Referencia visual en
hairlook_pro-2.html, sección id="pro-tab-inicio" (hero card con stats,
alertas de citas nuevas, citas de hoy, accesos rápidos).

TAREA:
Crea frontend/src/components/profesional/Home.jsx + Home.css con:
1. Hero card navy con: nombre del profesional, 3 stats (ingresos del mes,
   rating, servicios completados) — datos hardcodeados
2. Alerta visual si hay citas pendientes de aprobar (banner amarillo)
3. Lista de "Citas de hoy" con estado (nueva/confirmada)
4. Grid de accesos rápidos: Ver agenda, Mis reviews, Historial, Mi perfil
5. Bottom navigation con 4 botones: Inicio, Citas, Agenda, Mi Perfil

Conecta la ruta "/pro/inicio" en App.jsx, reemplazando el placeholder.
Mantén el mismo patrón de comentarios JSDoc.
```

---

## Bloque 6 — Agenda y Perfil Profesional

```
Contexto: proyecto Hairlook, React en frontend/. Referencia visual en
hairlook_pro-2.html, secciones id="pro-tab-agenda" y id="pro-tab-perfil"
(sub-tabs Información / Reviews / Cuenta).

TAREA:
1. Crea frontend/src/components/profesional/Agenda.jsx + Agenda.css:
   - Resumen visual de días activos de la semana
   - Por cada día: toggle on/off + inputs de hora inicio/fin (si está activo)
   - Botón "Guardar disponibilidad" (por ahora solo muestra confirmación visual)

2. Crea frontend/src/components/profesional/Perfil.jsx + Perfil.css:
   - Banner con avatar, nombre, especialidad, rating, # reviews, # servicios
   - Sub-tabs: Información (bio + especialidades + certificaciones),
     Reviews (gráfico de barras de distribución de estrellas + lista de
     comentarios), Cuenta (menú de configuración + botón cerrar sesión)

Usa datos hardcodeados realistas para ambos componentes.
Conecta las rutas "/pro/agenda" y "/pro/perfil" en App.jsx.
Mantén el mismo patrón de comentarios JSDoc.
```

---

## Bloque 7 — Notificaciones (profesional)

```
Contexto: proyecto Hairlook, React en frontend/. Referencia visual en
hairlook_pro-2.html, sección id="tab-notificaciones".

TAREA:
Crea frontend/src/components/profesional/Notificaciones.jsx + .css con:
1. Lista de notificaciones, cada una con tipo (nueva_cita, recordatorio,
   confirmacion) y color de borde distinto según tipo
2. Si es "nueva_cita": botones "Aceptar" / "Rechazar"
3. Estado de leída/no leída (punto indicador)
4. Datos hardcodeados de 3-4 notificaciones de ejemplo

Conecta la ruta "/pro/notificaciones" en App.jsx.
Mantén el mismo patrón de comentarios JSDoc.
```

---

## Bloque 8 — Adaptar controladores PHP a JSON

```
Contexto: proyecto Hairlook, backend PHP en backend/. Los controladores
actuales usan header("location: ...") para redirigir, lo cual no funciona
con fetch() desde React.

TAREA:
Para los controladores backend/public/c_login.php, c_registro_usuario.php,
c_registro_profesional.php (créalo si no existe, basado en el patrón de
c_registro_usuario.php pero usando registrar_profesional() de funciones_barberia.php):

1. Reemplaza cualquier header("location: ...") por:
   header('Content-Type: application/json');
   echo json_encode(['ok' => true/false, 'data' => ..., 'error' => '...']);
   exit();

2. Agrega al inicio de cada archivo, antes de cualquier salida:
   header('Access-Control-Allow-Origin: http://localhost:5173');
   header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
   header('Access-Control-Allow-Headers: Content-Type');
   if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

3. Asegúrate de leer el body como JSON si viene de fetch:
   $input = json_decode(file_get_contents('php://input'), true);
   $correo = $input['correo'] ?? $_POST['correo'] ?? '';
   (esto permite que funcione tanto con fetch como con forms tradicionales)

4. Mantén las validaciones existentes, solo cambia el formato de respuesta.

No modifiques funciones_barberia.php, solo los controladores.
```

---

## Bloque 9 — Conectar authService.js al backend real

```
Contexto: proyecto Hairlook. frontend/src/services/authService.js tiene
funciones simuladas (login, registerCliente, registerProfesional) con
comentarios TODO indicando el fetch real a implementar. El backend PHP
en backend/public/c_login.php, c_registro_usuario.php y
c_registro_profesional.php ya responden en JSON (bloque anterior).

TAREA:
Reemplaza las funciones simuladas en authService.js por fetch reales:

1. login(correo, password, tipo):
   POST a http://localhost/Hairlook/backend/public/c_login.php
   Body JSON: { correo, password, tipo }
   Retorna { ok, data, error } según la respuesta del backend

2. registerCliente(datos):
   POST a http://localhost/Hairlook/backend/public/c_registro_usuario.php
   Body JSON con todos los campos del formulario

3. registerProfesional(datos):
   POST a http://localhost/Hairlook/backend/public/c_registro_profesional.php
   Body JSON con todos los campos del formulario

Usa try/catch para manejar errores de red (servidor caído, CORS, etc.) y
retorna { ok: false, error: 'mensaje amigable' } en esos casos.

Prueba el flujo completo:
1. npm run dev en frontend/
2. XAMPP corriendo con Apache + MySQL activos
3. Registrar un cliente nuevo desde el formulario React
4. Verificar en phpMyAdmin que se guardó en la tabla usuario
5. Iniciar sesión con ese usuario recién creado
6. Confirmar que redirige a /inicio

Reporta cualquier error de CORS o conexión que aparezca en la consola del navegador.
```

---

## Bloque 10 — Preparar entrega para SENA

```
Contexto: proyecto Hairlook completo (backend PHP + frontend React conectados).
Evidencia: GA7-220501096-AA4-EV03

TAREA:
1. Verifica que exista un .gitignore en la raíz del proyecto que excluya:
   node_modules/, .env, vendor/ (si aplica)

2. Si el proyecto no está en git todavía:
   git init
   git add .
   git commit -m "Hairlook: frontend React conectado a backend PHP"

3. Crea un repositorio en GitHub (manualmente desde la web) y conéctalo:
   git remote add origin <URL_DEL_REPO>
   git push -u origin main

4. Crea un archivo enlace_repositorio.txt en la raíz con el link del repo

5. Crea una carpeta comprimida (ZIP) con TODO el proyecto (backend/ + frontend/
   + enlace_repositorio.txt + README.md), nombrada exactamente:
   NOMBRE_APELLIDO_AA4_EV03.zip
   (reemplaza NOMBRE_APELLIDO con el nombre real del aprendiz)

Confirma que el ZIP no incluya node_modules/ (debe excluirse según .gitignore,
pero verifica el tamaño del ZIP — si pesa más de 50MB probablemente se incluyó
por error).
```

---

## Orden recomendado de ejecución

1. Bloque 1 (App.jsx) — **hazlo ya**, desbloquea todo lo demás
2. Bloque 2 (Home Cliente)
3. Bloque 3 (Agendar Cita)
4. Bloque 4 (Mis Citas)
5. Bloque 8 (Controladores a JSON) — antes del 9
6. Bloque 9 (Conectar authService real) — prueba el flujo cliente completo aquí
7. Bloque 5 (Home Profesional)
8. Bloque 6 (Agenda y Perfil Pro)
9. Bloque 7 (Notificaciones)
10. Bloque 10 (Entrega final)

Después de cada bloque, prueba en el navegador antes de seguir. Si algo se
rompe, pégame el error de consola y lo resolvemos antes de avanzar.
