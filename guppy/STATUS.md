---
estado: bloqueado
siguiente_paso: Esperar la revisión manual y atender lo que pidan respondiendo a su email (no reenviar salvo que lo pidan ellos); si lo aprueban, subir el código a trunk, crear el tag 1.0.0 y subir assets/ al SVN.
bloqueo: "Revisión manual del equipo de plugins de WordPress.org (reenviado por tercera vez el 2026-10-14; el revisor que lleva la apelación dijo que continuará él la revisión)."
actualizado: 2026-10-14
---

# Estado

Fork GPL de Honeypot for Contact Form 7 2.1.1 (Nocean), renombrado como Classic Honeypot for CF7 y sin publicidad. Hitos 1 a 5 terminados y mergeados en `main`. El hito 5 montó el entorno de desarrollo (Playground con blueprint, sitio de Studio enlazado al repo y accesible desde la red local, MCP Adapter conectado a Claude Code), que servirá de base para una skill pública. Carpeta local y repo de GitHub renombrados al slug `classic-honeypot-for-cf7`.

## Diario

- 2026-10-14: Otro revisor cerró el envío sin ver las notas de la apelación (segundo rechazo). Al preguntar, el revisor de la apelación pidió volver a subirlo y avisarle en el hilo para continuar él la revisión. Reenviado.

- 2026-10-13: Apelación aceptada a trámite: WordPress.org pidió volver a subir el zip desde el formulario de alta y que un revisor contactará. Reenviado. Esperando la revisión manual.

- 2026-10-12: WordPress.org lo rechaza por ser un duplicado de Honeypot for Contact Form 7. Apelado respondiendo a su email con `classic-honeypot-for-cf7-1.0.0.zip`: es una copia directa de la 2.1.1 (mayo de 2021, la última de Nocean), el original se compró en julio de 2024 y desde la 2.1.1 es un desarrollo propio en paralelo sin código de los cambios posteriores. También se explicó por qué se mantienen el tag `[honeypot]` y los filtros `wpcf7_honeypot_*`. Esperando respuesta.

- 2026-10-11: Enviado a WordPress.org (zip 1.0.0 desde `312c621`). Slug inicial `classic-honeypot-for-cf7`. Escaneo automático: Pass. En la nota a revisores se explica el fork y por qué se mantienen el tag `[honeypot]` y los filtros `wpcf7_honeypot_*`.

- 2026-10-11: Icono nuevo (assets y pantalla de ajustes a 64x64), capturas repetidas con él y banners de 772x250 y 1544x500 generados desde `assets-src/` (HTML + script, excluido del zip). `assets/` completa. Plugin listo para enviar.

- 2026-10-11: Preparando la release (rama `feature/release-assets`): borrada `languages/` (y la cabecera Domain Path), creada `assets/` para el SVN de WordPress.org (excluida del zip) con las dos capturas del readme y placeholders de icono y banner. Falta el diseño final de icono y banner.

- 2026-10-11: Rama `docs/readme` publicada en GitHub para revisión de Nilo: readme.txt reescrito en tono neutro (fork independiente en paralelo desde la 2.1.1, sin críticas al original) y README.md nuevo (excluido del zip). Plugin Check: 0 errores, 0 avisos (los filtros `wpcf7_honeypot_*` se mantienen por compatibilidad con `phpcs:ignore` justificado; puede que lo pidan cambiar en la revisión de WordPress.org). Revisión de arreglos del original hasta 3.7.2: todo lo aplicable ya está incorporado.

- 2026-10-11: Rama `fix/settings-menu`: la página de ajustes vuelve a salir en el menú Contact como "Honeypot settings", al final (CF7 6.2 cambió el slug del menú a `wpcf7-dashboard` y la había dejado oculta). Pendiente de revisión de Nilo.

- 2026-10-11: Decidido no añadir abilities (Abilities API) al plugin. Queda: revisión de Nilo, cambios menores (icono) y publicación en WordPress.org.

- 2026-10-11: Hito 5 cerrado: MCP probado desde una sesión nueva de Claude Code; `feature/dev-environment` mergeada en `main`.

- 2026-10-11: Hito 5, paso 3: sitio de Studio `classic-honeypot.local` con el plugin enlazado al repo (junction), CF7 y página de prueba; accesible desde la red local añadiendo la IP del equipo al hosts. MCP Adapter 0.7.0 conectado a Claude Code por STDIO (`studio wp mcp-adapter serve`, alcance local, sin contraseña de aplicación). Playground también accesible en la red local con `--site-url`.

- 2026-10-11: Repo de GitHub renombrado a `nilovelez/classic-honeypot-for-cf7`; remote y Plugin URI actualizados.
- 2026-10-11: Carpeta local renombrada a `classic-honeypot-for-cf7` (el slug); memoria de Claude Code migrada y verificada.
- 2026-10-11: Hito 5, paso 2: blueprint.json de desarrollo; plugin probado en Playground (honeypot, control de tiempo e importación de ajustes, todo correcto).
- 2026-10-11: guppy/STATUS.md creado.
