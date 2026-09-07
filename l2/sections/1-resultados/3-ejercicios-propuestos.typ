== III. Ejecución, Monitoreo y Comparativa de Herramientas

=== a) Formulación y Despliegue de 10 Solicitudes de Cambio (RFC)

A partir de los requerimientos analizados en el Laboratorio 1, se estructuraron diez Solicitudes de Cambio (*RFC: CHG-001 a CHG-010*) que modelan la evolución del sistema ante nuevas demandas funcionales, normativas y de seguridad. La @tab-rfcs resume los atributos de cada solicitud:

#figure(
  table(
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
  ),
  caption: [Catálogo estructurado de las 10 Solicitudes de Cambio (RFC) del proyecto.],
) <tab-rfcs>

La instrumentación de estas solicitudes en GitHub Projects permitió su monitoreo en dos modalidades de visualización: el tablero Kanban (@fig-kanban-board), que facilita el control visual del flujo de trabajo y la detección temprana de cuellos de botella, y la vista tabular estructurada (@fig-projects-table), orientada a la auditoría masiva de campos, responsables y avance de sub-tareas.

#figure(
  image("/l2/img/github-projects-board.png", width: 92%),
  caption: [Tablero Kanban interactivo en GitHub Projects v2 para la asignación dinámica y gobernanza visual del flujo de valor.],
) <fig-kanban-board>

#figure(
  image("/l2/img/github-projects-table.png", width: 92%),
  caption: [Vista tabular estructurada en GitHub Projects v2 con atributos consolidados de estado, asignación y progreso.],
) <fig-projects-table>

=== b) Dinámica de Estados y Ciclo de Vida de las Modificaciones

Para garantizar la estabilidad del proyecto y evitar la sobreasignación de tareas al equipo, cada solicitud transitó de manera estricta por el ciclo de vida formal ilustrado en la @fig-estados-cambio:

#figure(
  image("/l2/img/ciclo-vida-estados.png", width: 68%),
  caption: [Diagrama de transición de estados finitos que gobierna el ciclo de vida de cada cambio en el proyecto (generado en Mermaid).],
) <fig-estados-cambio>

- *Pendiente (_Backlog_):* Solicitudes registradas formalmente que aguardan estimación de esfuerzo y análisis de factibilidad técnica.
- *En Revisión (CCB):* Tareas sometidas al escrutinio del Comité de Control de Cambios para su priorización, reprogramación o descarte.
- *En Desarrollo:* Tareas aprobadas con recursos formalmente comprometidos y construcción técnica en curso sobre ramas de trabajo aisladas.
- *En Pruebas (QA):* Tareas con código finalizado sometidas a verificación de casos de prueba y análisis de no-regresión.
- *Cerrado:* Tareas certificadas e integradas de forma definitiva en la línea base del sistema, concluyendo el ciclo de cambio.

=== c) Comparación de Paradigmas de Gestión: i-doit vs. GitHub Projects

El contraste experimental entre ambas plataformas permite identificar la idoneidad de cada enfoque según el dominio de aplicación (@tab-comparativa):

#figure(
  table(
    columns: (1.8fr, 2.6fr, 2.6fr),
    align: (left, left, left),
    table.header([*Criterio Metodológico*], [*i-doit (Enfoque ITIL / CMDB)*], [*GitHub Projects (Enfoque Ágil / GitOps)*]),
    [Propósito Primario], [Control formal de configuración de activos, módulos y servicios (CIs).], [Gestión ágil colaborativa de requerimientos, tareas y sprints.],
    [Modelo de Datos], [Grafo relacional de CIs, categorías globales y dependencias de servicio.], [Tarjetas de issues vinculadas bidireccionalmente al repositorio Git.],
    [Asignación de Recursos], [Fichas estáticas de asignación de contacto, roles y responsabilidades.], [Asignación dinámica a desarrolladores con menciones y notificaciones en tiempo real.],
    [Monitoreo del Avance], [Inventarios tabulares e historiales de cambios por componente.], [Tableros Kanban interactivos con métricas de flujo continuo.],
    [Auditoría y Trazabilidad], [Bitácora inmutable (_Logbook_) con registro automático de eventos.], [Trazabilidad integral mediante commits, revisiones de pares y cierres de PR.],
    [Coordinación del Equipo], [Enfocada en documentación institucional y gobernanza formal.], [Enfocada en comunicación continua y resolución ágil de impedimentos.],
    [Curva de Adopción], [Moderada a alta; requiere modelado ontológico de la CMDB.], [Baja e inmediata; alineada al flujo de trabajo nativo del desarrollador.],
    [Control de Plazos], [Fechas de ciclo de vida, soporte y mantenimiento de versiones.], [Fechas límite directas vinculadas a hitos (_milestones_) y sprints.],
    [Ámbito Recomendado], [Gobierno institucional de configuración y plataformas operativas.], [Desarrollo ágil de software y gestión continua de requerimientos cambiantes.],
  ),
  caption: [Comparación metodológica y operativa entre i-doit (CMDB) y GitHub Projects (Ágil).],
) <tab-comparativa>

Como se observa en la @fig-idoit-applications, i-doit asegura un inventario formal de los componentes de software desarrollados, mientras que la @fig-idoit-logbook documenta la auditoría histórica de cada modificación efectuada, garantizando el cumplimiento de estándares como ISO/IEC/IEEE 29148 @iso29148 e ITIL 4 @itil4.

#figure(
  image("/l2/img/idoit-applications.png", width: 90%),
  caption: [Inventario consolidado de los 5 módulos de software registrados como CIs operativos en la CMDB de i-doit.],
) <fig-idoit-applications>

#figure(
  image("/l2/img/idoit-logbook.png", width: 90%),
  caption: [Registro inmutable de auditoría (Logbook) en i-doit documentando cronológicamente los 10 eventos de configuración generados.],
) <fig-idoit-logbook>

=== d) Reporte de Seguimiento y Trazabilidad del Proyecto (Anexo 21)

En correspondencia con el formato de reporte de seguimiento de la guía práctica (Anexo 21), la @tab-seguimiento documenta la evolución empírica, el estado final y los resultados de validación de las diez solicitudes procesadas:

#figure(
  table(
    columns: (0.9fr, 2.7fr, 1.3fr, 1.3fr, 0.9fr, 2.9fr),
    align: (center, left, center, left, center, left),
    table.header([*ID*], [*Descripción Breve*], [*Estado Actual*], [*Responsable*], [*Plazo*], [*Observaciones y Resultado de Verificación*]),
    [CHG-001], [Importación JSON/CSV de requisitos.], [En desarrollo], [L. Sequeiros], [15/09], [Parser JSON completado al 100%; soporte CSV en codificación.],
    [CHG-002], [Taxonomía ISO/IEC/IEEE 29148.], [En revisión], [Arq. Software], [18/09], [Matriz de tipos enviada al CCB; sesión de dictamen programada.],
    [CHG-003], [Grafo dinámico de dependencias.], [En pruebas], [Auditor QA], [20/09], [Algoritmo de adyacencia verificado; pruebas de renderizado UI en curso.],
    [CHG-004], [Webhooks de notificación para CCB.], [Pendiente], [DevOps], [22/09], [A la espera de especificación de endpoints de seguridad del cliente.],
    [CHG-005], [Autenticación multifactor (2FA).], [Cerrado], [Ciberseguridad], [10/09], [Protocolo TOTP integrado, auditado y validado sin incidencias.],
    [CHG-006], [Validación semántica de enunciados.], [En desarrollo], [Ing. Calidad], [25/09], [Reglas heurísticas de completitud incorporadas en motor léxico.],
    [CHG-007], [Reportes Markdown y OpenAPI.], [Cerrado], [Dev Backend], [12/09], [Módulo operativo y documentación de API publicada exitosamente.],
    [CHG-008], [Optimización de matriz de datos.], [En pruebas], [DBA / Backend], [24/09], [Índices B-Tree implementados; reducción del 42% en latencia de consulta.],
    [CHG-009], [Rediseño accesible y modo oscuro.], [En revisión], [Diseñador UI], [28/09], [Prototipo validado preliminarmente conforme a directrices WCAG 2.1 AA.],
    [CHG-010], [Persistencia multi-nube (S3/DB).], [Pendiente], [Arq. Cloud], [30/09], [Propuesta arquitectónica de abstracción formulada para evaluación CCB.],
  ),
  caption: [Reporte de seguimiento, estado de ciclo de vida y resultados de verificación de las RFCs (Anexo 21).],
) <tab-seguimiento>

La trazabilidad bidireccional entre las solicitudes de cambio y el código fuente se constató a través del historial de confirmaciones en GitHub (@fig-gh-commits), donde cada commit referencia explícitamente el identificador de la RFC correspondiente, asegurando que ningún cambio se introduzca sin respaldo documental ni justificación técnica verificable @baufest2003.

#figure(
  image("/l2/img/github-commits-history.png", width: 90%),
  caption: [Historial cronológico de confirmaciones en GitHub que corrobora la trazabilidad directa entre código fuente y Solicitudes de Cambio.],
) <fig-gh-commits>
