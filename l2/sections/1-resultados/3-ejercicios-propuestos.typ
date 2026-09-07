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
  caption: [Tablero Kanban interactivo en GitHub Projects para la asignación y seguimiento de tareas.],
) <fig-kanban-board>

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

Se contrastan *i-doit* y *GitHub Projects* evaluando su utilidad práctica para conducir y monitorear proyectos de software:

#table(
  columns: (2fr, 2.5fr, 2.5fr),
  align: (left, left, left),
  table.header([*Aspecto de Gestión*], [*i-doit*], [*GitHub Projects*]),
  [Enfoque de Gestión], [Control formal de configuración de activos, servicios e inventario.], [Gestión ágil colaborativa de tareas, requerimientos e incidencias.],
  [Planificación y Asignación], [Estructura basada en fichas de componentes y roles asignados.], [Asignación dinámica de tarjetas, etiquetas, responsables e hitos.],
  [Visualización del Avance], [Listados tabulares y bitácoras cronológicas de auditoría.], [Tableros Kanban interactivos con movimiento fluido entre columnas.],
  [Coordinación del Equipo], [Enfocada en documentación formal de cambios.], [Enfocada en comunicación activa, hilos de discusión y menciones.],
  [Curva de Aprendizaje], [Mayor esfuerzo inicial para definir el inventario del proyecto.], [Muy intuitiva y de rápida adopción para equipos de desarrollo.],
  [Seguimiento de Plazos], [Fechas de mantenimiento y ciclos de vida de servicios.], [Fechas límite directas vinculadas a sprints y entregables.],
  [Mejor Aplicación], [Gobierno y control de inventario de infraestructura y software.], [Seguimiento del trabajo diario del equipo y control de cambios ágil.],
)

#figure(
  image("/l2/img/idoit-logbook.png", width: 90%),
  caption: [Bitácora de auditoría (Logbook) en i-doit para el seguimiento formal y control de cambios en componentes.],
) <fig-idoit-logbook>

#figure(
  image("/l2/img/idoit-applications.png", width: 90%),
  caption: [Detalle de inventario de aplicaciones y servicios en i-doit para el control de configuración del software.],
) <fig-idoit-applications>
