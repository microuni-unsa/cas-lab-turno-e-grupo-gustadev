== III. Resultados Obtenidos: Ejercicios Resueltos

=== a) Instalación y Configuración de Herramientas para Gestión de Cambios

Para dar soporte a la gestión integral de requerimientos y cambios de software, se desplegaron dos herramientas bajo paradigmas complementarios: una solución empresarial on-premise orientada a CMDB e ITIL (*i-doit*) y una plataforma ágil y moderna en la nube (*GitHub Projects* a través de `gh` CLI).

*1. Despliegue de i-doit (Open Edition 38) mediante Docker:*
- *Arquitectura Contenerizada:* Se diseñó un entorno orquestado con Docker Compose compuesto por dos servicios aislados:
  - `idoit-app`: Contenedor basado en `php:8.3-apache` provisto de extensiones críticas (`mysqli`, `pdo_mysql`, `gd`, `ldap`, `mbstring`, `sockets`, `zip`, `xml`, `opcache`), soporte para reescritura de cabeceras (`mod_rewrite`, `headers`, `expires`) y configuración afinada de memoria y variables de entrada (`max_input_vars = 10000`, `post_max_size = 128M`, `memory_limit = 512M`).
  - `idoit-db`: Servidor de base de datos relacional `mariadb:10.11` configurado con tamaño de paquete extendido (`max_allowed_packet = 128M`), modo SQL flexible y formato dinámico de filas InnoDB.
- *Instalación e Inicialización:* Mediante la interfaz de línea de comandos de i-doit (`console.php install` y `console.php tenant-create`), se inicializó la base de datos del sistema (`idoit_system`) y se aprovisionó el mandante de trabajo *Laboratorio 2* con su base de datos operacional (`idoit_data`), validando el acceso en `http://localhost:8080`.

// PLACEHOLDER: Captura de pantalla de la interfaz de i-doit (Login o Vista General del CMDB)
// #figure(
//   image("/l2/img/idoit-dashboard.png", width: 85%),
//   caption: [Panel principal de control y CMDB de i-doit en ejecución contenerizada.],
// ) <fig-idoit-dashboard>

*2. Configuración de GitHub Projects y Repositorio Institucional:*
- *Infraestructura en GitHub:* Se creó y enlazó el repositorio `cas-lab-turno-e-grupo-gustadev` dentro de la organización universitaria `microuni-unsa`.
- *Modelado del Flujo de Estados con GitHub CLI:* A través de la CLI `gh`, se definieron las etiquetas de severidad y ciclo de vida de los cambios:
  - `rfc`: Identificador general de Solicitud de Cambio (*Request for Change*).
  - `estado:pendiente`, `estado:en-revision`, `estado:en-desarrollo`, `estado:en-pruebas`, `estado:cerrado`.
  - `prioridad:baja`, `prioridad:media`, `prioridad:alta`, `prioridad:critica`.

// PLACEHOLDER: Captura de pantalla del repositorio y listado de issues/etiquetas en GitHub
// #figure(
//   image("/l2/img/github-issues-list.png", width: 85%),
//   caption: [Listado de requerimientos de cambio (RFC) gestionados en el repositorio de GitHub.],
// ) <fig-gh-issues>

=== b) Diagrama de Flujo del Proceso de Gestión de Cambios (Anexo 21 e ITIL)

El proceso formal de gestión de cambios implementado en la práctica se fundamenta en las directrices de la norma IEEE Std 828-2012 @ieee828, el marco ITIL 4 para la habilitación del cambio @itil4 y el flujo de referencia del Anexo 21. La estructura secuencial garantiza que ninguna alteración ingrese al código fuente o a la documentación sin evaluación previa:

+ *Solicitud de Cambio (RFC - Request for Change):* Se origina ante nuevas necesidades del cliente, defectos encontrados en pruebas o actualizaciones normativas. Se registran sus atributos: identificador, requisito afectado, justificación técnica y prioridad.
+ *Análisis de Impacto Técnico y Económico:* El equipo de desarrollo evalúa el impacto sobre la arquitectura, la base de datos, el esfuerzo estimado en horas y los riesgos potenciales.
+ *Revisión y Decisión del Comité de Control de Cambios (CCB / CAB):* El comité colegiado revisa la solicitud y emite un veredicto formal: _Aprobado_, _Rechazado_ o _Diferido_.
+ *Planificación e Implementación:* Los cambios aprobados entran al ciclo de desarrollo activo (rama de trabajo o sprint correspondiente).
+ *Pruebas de Regresión y Aseguramiento de Calidad (QA):* El equipo de calidad valida que la modificación cumpla el criterio de aceptación y no altere funcionalidades existentes.
+ *Despliegue y Actualización de Línea Base:* Tras la aprobación de QA, se fusiona el cambio, se actualiza la especificación formal y se cierra el ciclo de vida del cambio.

// PLACEHOLDER: Diagrama de flujo del proceso de gestión de cambios (Anexo 21 / IEEE 828)
// #figure(
//   image("/l2/img/flujo-gestion-cambios.png", width: 80%),
//   caption: [Diagrama de flujo del ciclo de vida de una Solicitud de Cambio (RFC).],
// ) <fig-flujo-cambios>
