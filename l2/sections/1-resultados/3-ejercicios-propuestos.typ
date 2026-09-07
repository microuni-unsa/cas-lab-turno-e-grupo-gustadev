== IV. Resultados Obtenidos: Ejercicios Propuestos

=== a) Incorporación de Requerimientos del Laboratorio 1 al Ciclo de Cambios

A partir de la especificación estructurada en el Laboratorio 1 (orientada a un sistema de gestión de requisitos bajo IEEE Std 830-1998 @iso29148), se generaron cinco Solicitudes de Cambio formalizadas (*RFC*). Cada requerimiento de cambio documenta su causa técnica o normativa, su prioridad, su estado en el ciclo de vida, su responsable y la fecha límite acordada:

#table(
  columns: (1fr, 1.2fr, 2.8fr, 2.5fr, 1.3fr, 1.4fr, 1.3fr),
  align: (center, center, left, left, center, left, center),
  table.header([*ID*], [*Req. Base*], [*Descripción del Cambio*], [*Motivo del Cambio*], [*Estado*], [*Responsable*], [*Límite*]),
  [CHG-001], [RF-01], [Importación masiva en formatos JSON y CSV para catálogo de requisitos.], [Evitar ingreso manual individual en proyectos con >100 requisitos.], [En desarrollo], [L. Sequeiros (Analista)], [15/09/2026],
  [CHG-002], [RF-02], [Extensión del esquema de taxonomía hacia la norma ISO/IEC/IEEE 29148.], [Cumplimiento con estándares internacionales modernos que reemplazan IEEE 830.], [En revisión], [Arquitecto Software], [18/09/2026],
  [CHG-003], [RF-03], [Renderizado dinámico de grafo de dependencias y detección de requisitos huérfanos.], [Matrices tabulares inmanejables; se requiere alerta visual inmediata para QA.], [En pruebas], [Auditor QA], [20/09/2026],
  [CHG-004], [RF-04], [Webhooks de notificación en tiempo real para solicitudes dirigidas al CCB.], [Reducir latencia de aprobación mediante avisos automáticos a evaluadores.], [Pendiente], [DevOps Engineer], [22/09/2026],
  [CHG-005], [RNF-02], [Autenticación multifactor (2FA TOTP) para autorización de cambios en línea base.], [Exigencia regulatoria de seguridad y trazabilidad estricta de auditores.], [Cerrado], [Ciberseguridad], [10/09/2026],
)

// PLACEHOLDER: Captura de pantalla del Tablero de Proyectos (GitHub Projects Kanban o Vista de i-doit)
// #figure(
//   image("/l2/img/github-projects-board.png", width: 85%),
//   caption: [Tablero Kanban de seguimiento del ciclo de vida de los cambios en GitHub Projects.],
// ) <fig-kanban-board>

*Detalle Técnico de las Solicitudes de Cambio:*
- *CHG-001 (sobre RF-01 - Elicitación):* Permite la ingesta por lotes de requisitos provenientes de herramientas externas. Aprobado por el CCB y en implementación en la capa de persistencia.
- *CHG-002 (sobre RF-02 - Clasificación):* Modifica la base de datos relacional para admitir atributos requeridos por la norma ISO 29148. Se encuentra en evaluación de compatibilidad hacia atrás.
- *CHG-003 (sobre RF-03 - Trazabilidad):* Integra un motor de grafos dirigidos interactivos para inspeccionar el impacto en cascada de una modificación sobre casos de uso.
- *CHG-004 (sobre RF-04 - Control de Cambios):* Automatiza la comunicación entre analistas y miembros del comité de cambios. Registrado recientemente en estado de espera.
- *CHG-005 (sobre RNF-02 - Seguridad):* Refuerzo criptográfico con algoritmos TOTP y registro inmutable en log de auditoría. Implementado, verificado por QA y cerrado formalmente.

=== b) Evaluación Comparativa entre i-doit y GitHub Projects

Se contrastan ambas soluciones tecnológicas de gestión bajo criterios de arquitectura, funcionalidad, trazabilidad y aplicabilidad profesional:

#table(
  columns: (1.8fr, 2.6fr, 2.6fr),
  align: (left, left, left),
  table.header([*Criterio Técnico*], [*i-doit (Open 38)*], [*GitHub Projects (v2)*]),
  [Enfoque Primario], [Gestión de Configuración ITIL (CMDB), infraestructura y activos de TI.], [Gestión ágil de proyectos, trazabilidad de código e incidencias DevOps.],
  [Arquitectura y Despliegue], [On-premise contenerizado (Docker: PHP 8.3 + Apache + MariaDB 10.11).], [Cloud SaaS integrado nativamente en el ecosistema GitHub.],
  [Trazabilidad de ECSs], [Alta a nivel de infraestructura: relaciones de impacto entre servidores, software y servicios.], [Alta a nivel de desarrollo: enlace bidireccional entre issues, commits, branches y pull requests.],
  [Flujo de Aprobación de Cambios], [Control formal por historial de cambios en Logbook y formularios de ciclo de vida.], [Flujo ágil mediante estados Kanban, revisiones de código (PR reviews) y reglas de protección.],
  [Automatización y API], [API JSON-RPC y scripts de consola PHP (`console.php`).], [GitHub CLI (`gh`), GraphQL API, REST API y automatización con GitHub Actions.],
  [Curva de Aprendizaje], [Media a Alta: requiere comprender modelos ITIL y configuración de servidor.], [Baja a Media: interfaz intuitiva para equipos de desarrollo y gestión.],
  [Licenciamiento], [Código abierto (AGPLv3) para versión Open; comercial para Add-ons Pro.], [Freemium institucional integrado con planes de GitHub.],
  [Caso de Uso Ideal], [Gobierno corporativo de TI, auditorías de infraestructura y gestión de centros de datos.], [Equipos de desarrollo de software que requieren control de cambios integrado al repositorio.],
)

=== c) Tareas de Seguimiento y Generación de Reportes

El seguimiento periódico de los requerimientos de cambio permitió monitorear el avance del proyecto y detectar posibles desvíos respecto al cronograma establecido:

#table(
  columns: (1fr, 1.8fr, 1.4fr, 1.5fr, 1.3fr, 2.5fr),
  align: (center, left, center, left, center, left),
  table.header([*ID*], [*Descripción Breve*], [*Estado Actual*], [*Responsable*], [*Límite*], [*Observaciones y Estado de Avance*]),
  [CHG-001], [Importación JSON/CSV], [En desarrollo], [L. Sequeiros], [15/09/2026], [Parser de CSV validado; integrando importador JSON.],
  [CHG-002], [Esquema ISO 29148], [En revisión], [Arq. Software], [18/09/2026], [Evaluación de impacto sobre catálogo existente en curso.],
  [CHG-003], [Grafo de Trazabilidad], [En pruebas], [Auditor QA], [20/09/2026], [Pruebas de estrés superadas con 500 nodos sin latencia.],
  [CHG-004], [Webhooks de Alerta], [Pendiente], [DevOps], [22/09/2026], [A la espera de definir endpoint de mensajería del CCB.],
  [CHG-005], [Autenticación 2FA], [Cerrado], [Ciberseguridad], [10/09/2026], [Verificado con tokens TOTP y desplegado en producción.],
)

// PLACEHOLDER: Captura del Reporte o Registro de Auditoría (Logbook de i-doit o Reporte CLI de GitHub)
// #figure(
//   image("/l2/img/reporte-seguimiento.png", width: 85%),
//   caption: [Reporte consolidado de seguimiento y auditoría de cambios.],
// ) <fig-reporte-seguimiento>
