---
estado: bloqueado
siguiente_paso: Aplicar los cambios menores que indique Nilo (p. ej. nuevo icono) y preparar la publicación en el directorio de plugins de WordPress.org.
bloqueo: "Nilo revisa en GitHub la rama docs/readme (readme.txt y README.md) y pasa los cambios menores pendientes (icono)."
actualizado: 2026-10-11
---

# Estado

Fork GPL de Honeypot for Contact Form 7 2.1.1 (Nocean), renombrado como Classic Honeypot for CF7 y sin publicidad. Hitos 1 a 5 terminados y mergeados en `main`. El hito 5 montó el entorno de desarrollo (Playground con blueprint, sitio de Studio enlazado al repo y accesible desde la red local, MCP Adapter conectado a Claude Code), que servirá de base para una skill pública. Carpeta local y repo de GitHub renombrados al slug `classic-honeypot-for-cf7`.

## Diario

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
