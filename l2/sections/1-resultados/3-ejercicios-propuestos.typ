== III. Resultados Obtenidos: Ejercicios Propuestos

=== a) Gestión de 10 Requerimientos de Cambio en el Proyecto

A partir de los requerimientos analizados en el Laboratorio 1, se estructuraron diez Solicitudes de Cambio (*RFC*) para simular la evolución natural de un proyecto de software. Cada solicitud fue registrada asignándole su nivel de prioridad, responsable dentro del equipo, plazo de entrega y estado de avance:

#table(
  columns: (0.9fr, 1fr, 3.2fr, 1.1fr, 1.4fr, 1.4fr, 1fr),
  align: (center, center, left, center, center, left, center),
  table.header([*ID*], [*Origen*], [*Solicitud de Cambio*], [*Prioridad*], [*Estado Actual*], [*Responsable*], [*Plazo*]),
  [CHG-001], [RF-01], [Importación masiva en JSON y CSV para agilizar la captura de requisitos.], [Alta], [En desarrollo], [L. Sequeiros], [15/09],
  [CHG-002], [RF-02], [Extensión de la taxonomía del proyecto hacia la norma ISO/IEC/IEEE 29148.], [Media], [En revisión], [Arq. Software], [18/09],
  [CHG-003], [RF-03], [Grafo dinámico de dependencias para detectar requisitos huérfanos.], [Alta], [En pruebas], [Auditor QA], [20/09],
  [CHG-004], [RF-04], [Webhooks de notificación para alertar al comité de cambios (CCB).], [Media], [Pendiente], [DevOps], [22/09],
  [CHG-005], [RNF-02], [Autenticación multifactor (2FA) para autorizar cambios en la línea base.], [Crítica], [Cerrado], [Ciberseguridad], [10/09],
  [CHG-006], [RF-05], [Validación semántica de enunciados con reglas de completitud.], [Alta], [En desarrollo], [Ing. Calidad], [25/09],
  [CHG-007], [RF-06], [Generación automatizada de reportes en Markdown y especificación OpenAPI.], [Media], [Cerrado], [Dev Backend], [12/09],
  [CHG-008], [RNF-01], [Optimización de consultas a la matriz para soportar catálogos grandes.], [Alta], [En pruebas], [DBA / Backend], [24/09],
  [CHG-009], [RNF-03], [Rediseño visual accesible con soporte de modo oscuro.], [Baja], [En revisión], [Diseñador UI], [28/09],
  [CHG-010], [RC-01], [Desacoplamiento de persistencia para compatibilidad multi-nube.], [Alta], [Pendiente], [Arq. Cloud], [30/09],
)

#figure(
  image("/l2/img/github-projects-board.png", width: 92%),
  caption: [Tablero Kanban interactivo en GitHub Projects para la asignación y seguimiento visual de tareas.],
) <fig-kanban-board>

#figure(
  image("/l2/img/github-projects-table.png", width: 92%),
  caption: [Vista tabular estructurada en GitHub Projects con campos de estado, asignación y progreso.],
) <fig-projects-table>

=== b) Estados y Ciclo de Vida en la Gestión del Proyecto

Para mantener el control sobre los compromisos del equipo y evitar sobrecarga de trabajo, cada requerimiento transita a través de un ciclo de vida definido:

#figure(
  image("/l2/img/ciclo-vida-estados.png", width: 68%),
  caption: [Diagrama de estados del ciclo de vida de los cambios en el proyecto (generado en Mermaid).],
) <fig-estados-cambio>

- *Pendiente / Backlog:* Solicitudes registradas que esperan priorización y evaluación de impacto.
- *En Revisión (CCB):* Tareas bajo evaluación de viabilidad por parte de los líderes del proyecto.
- *En Desarrollo:* Tareas aprobadas con responsable asignado y ejecución en curso.
- *En Pruebas (QA):* Tareas implementadas sometidas a verificación de cumplimiento.
- *Cerrado:* Tareas verificadas formalmente e integradas en la línea base del proyecto.

=== c) Comparación de Herramientas para la Gestión del Proyecto

Se contrastan *i-doit* y *GitHub Projects* evaluando su utilidad práctica para conducir y monitorear proyectos de software, profundizando en los criterios de gestión:

#table(
  columns: (1.8fr, 2.6fr, 2.6fr),
  align: (left, left, left),
  table.header([*Criterio de Gestión*], [*i-doit (CMDB / ITIL)*], [*GitHub Projects (Ágil)*]),
  [Enfoque Primario], [Control formal de configuración de activos, servicios y módulos (CIs).], [Gestión ágil colaborativa de tareas, incidencias y entregables.],
  [Estructura de Datos], [Base de datos relacional de objetos, categorías y relaciones CMDB.], [Tarjetas de issues vinculadas a repositorios, ramas y PRs.],
  [Planificación y Asignación], [Estructura formal basada en roles de contacto y componentes asignados.], [Asignación dinámica de tarjetas, etiquetas, responsables e hitos.],
  [Visualización del Avance], [Listados tabulares y bitácoras cronológicas de auditoría.], [Tableros Kanban interactivos con movimiento fluido entre columnas.],
  [Auditoría y Trazabilidad], [Logbook inmutable con fecha, autor y nivel de alarma por evento.], [Historial cronológico de commits, comentarios y cierres de PRs.],
  [Coordinación del Equipo], [Enfocada en documentación formal de cambios de configuración.], [Enfocada en comunicación activa, hilos de discusión y menciones.],
  [Curva de Adopción], [Requiere configuración previa del inventario y modelado de CIs.], [Muy intuitiva y de inmediata adopción por equipos de desarrollo.],
  [Seguimiento de Plazos], [Ciclos de vida y fechas de soporte en servicios y componentes.], [Fechas límite directas vinculadas a sprints y entregables.],
  [Mejor Aplicación], [Gobierno y control de inventario de infraestructura y software.], [Seguimiento del trabajo diario del equipo y control de cambios ágil.],
)

#figure(
  image("/l2/img/idoit-applications.png", width: 90%),
  caption: [Inventario activo de 5 componentes de software registrados en la CMDB de i-doit.],
) <fig-idoit-applications>

#figure(
  image("/l2/img/idoit-logbook.png", width: 90%),
  caption: [Bitácora de auditoría (Logbook) en i-doit con los 10 eventos cronológicos de creación y modificación.],
) <fig-idoit-logbook>

=== d) Reporte de Seguimiento y Control de Avance del Proyecto

Siguiendo el procedimiento establecido en la guía de práctica (Anexo 21), se realizó el seguimiento a las solicitudes de cambio gestionadas, registrando las observaciones de verificación y el estado final de cumplimiento de cada ítem:

#table(
  columns: (0.9fr, 2.8fr, 1.3fr, 1.3fr, 0.9fr, 2.8fr),
  align: (center, left, center, left, center, left),
  table.header([*ID*], [*Descripción Breve*], [*Estado Actual*], [*Responsable*], [*Plazo*], [*Observaciones y Resultado*]),
  [CHG-001], [Importación JSON/CSV de requisitos.], [En desarrollo], [L. Sequeiros], [15/09], [Parser JSON completado; se implementa soporte CSV.],
  [CHG-002], [Taxonomía ISO/IEC/IEEE 29148.], [En revisión], [Arq. Software], [18/09], [Matriz de tipos enviada al CCB para aprobación.],
  [CHG-003], [Grafo dinámico de dependencias.], [En pruebas], [Auditor QA], [20/09], [Algoritmo validado; en prueba de visualización UI.],
  [CHG-004], [Webhooks de notificación para CCB.], [Pendiente], [DevOps], [22/09], [A la espera de definición de endpoints del cliente.],
  [CHG-005], [Autenticación multifactor (2FA).], [Cerrado], [Ciberseguridad], [10/09], [Implementado con TOTP y validado sin incidencias.],
  [CHG-006], [Validación semántica de enunciados.], [En desarrollo], [Ing. Calidad], [25/09], [Reglas heurísticas integradas en motor de análisis.],
  [CHG-007], [Reportes Markdown y OpenAPI.], [Cerrado], [Dev Backend], [12/09], [Generación automática operativa y documentada.],
  [CHG-008], [Optimización de matriz de datos.], [En pruebas], [DBA / Backend], [24/09], [Índices B-Tree aplicados; pruebas de carga en curso.],
  [CHG-009], [Rediseño accesible y modo oscuro.], [En revisión], [Diseñador UI], [28/09], [Prototipo Figma validado con usuarios clave.],
  [CHG-010], [Persistencia multi-nube (S3/DB).], [Pendiente], [Arq. Cloud], [30/09], [Plan de arquitectura formulado para evaluación CCB.],
)

#figure(
  image("/l2/img/github-commits-history.png", width: 90%),
  caption: [Historial de confirmaciones y trazabilidad de cambios en el repositorio de GitHub.],
) <fig-gh-commits>
