== IV. Resultados Obtenidos: Ejercicios Propuestos

=== a) Incorporación de Requerimientos del Laboratorio 1 al Ciclo de Cambios

A partir de la especificación estructurada en el Laboratorio 1 (orientada a un sistema de gestión de requisitos bajo IEEE Std 830-1998 @iso29148), se formalizaron diez Solicitudes de Cambio (*RFC*). Cada requerimiento de cambio documenta su causa técnica o normativa, su prioridad, su estado actual en el ciclo de vida, su responsable y la fecha límite acordada:

#table(
  columns: (0.9fr, 1.1fr, 2.7fr, 2.5fr, 1.2fr, 1.4fr, 1.2fr),
  align: (center, center, left, left, center, left, center),
  table.header([*ID*], [*Req. Base*], [*Descripción del Cambio*], [*Motivo del Cambio*], [*Estado*], [*Responsable*], [*Límite*]),
  [CHG-001], [RF-01], [Importación masiva en formatos JSON y CSV para catálogo de requisitos.], [Evitar ingreso manual individual en proyectos con >100 requisitos.], [En desarrollo], [L. Sequeiros (Analista)], [15/09/2026],
  [CHG-002], [RF-02], [Extensión del esquema de taxonomía hacia la norma ISO/IEC/IEEE 29148.], [Cumplimiento con estándares internacionales modernos que reemplazan IEEE 830.], [En revisión], [Arquitecto Software], [18/09/2026],
  [CHG-003], [RF-03], [Renderizado dinámico de grafo de dependencias y detección de requisitos huérfanos.], [Matrices tabulares inmanejables; se requiere alerta visual inmediata para QA.], [En pruebas], [Auditor QA], [20/09/2026],
  [CHG-004], [RF-04], [Webhooks de notificación en tiempo real para solicitudes dirigidas al CCB.], [Reducir latencia de aprobación mediante avisos automáticos a evaluadores.], [Pendiente], [DevOps Engineer], [22/09/2026],
  [CHG-005], [RNF-02], [Autenticación multifactor (2FA TOTP) para autorización de cambios en línea base.], [Exigencia regulatoria de seguridad y trazabilidad estricta de auditores.], [Cerrado], [Ciberseguridad], [10/09/2026],
  [CHG-006], [RF-05], [Motor de validación semántica con reglas de completitud y sintaxis RFC 2119.], [Detectar enunciados ambiguos o inconsistentes previo a someter a aprobación formal.], [En desarrollo], [Ing. Calidad / NLP], [25/09/2026],
  [CHG-007], [RF-06], [Exportación automatizada a formatos Markdown, PDF y especificaciones OpenAPI/Swagger.], [Necesidad de artefactos técnicos portables e interoperables para backend.], [Cerrado], [Dev Backend], [12/09/2026],
  [CHG-008], [RNF-01], [Optimización de consultas a la matriz de trazabilidad con indexación distribuida y caché Redis.], [Pruebas de estrés mostraron degradación (>4.5 s) con catálogos >5,000 requisitos.], [En pruebas], [DBA / Backend], [24/09/2026],
  [CHG-009], [RNF-03], [Rediseño accesible de interfaz web conforme a WCAG 2.1 nivel AA y soporte de modo oscuro.], [Reportes de fatiga visual en jornadas de elicitación y normativas de accesibilidad.], [En revisión], [Diseñador UI/UX], [28/09/2026],
  [CHG-010], [RC-01], [Desacoplamiento de almacenamiento para compatibilidad multi-nube (S3 / MariaDB / PostgreSQL).], [Despliegue híbrido del catálogo sin atarse a un único proveedor de persistencia.], [Pendiente], [Arquitecto Cloud], [30/09/2026],
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
- *CHG-006 (sobre RF-05 - Validación de Consistencia):* Aplica análisis estático de texto para verificar el uso riguroso de palabras clave obligatorias ("debe", "no debe") y detectar requisitos incompletos.
- *CHG-007 (sobre RF-06 - Exportación):* Despliega microservicio de renderizado que compila especificaciones en OpenAPI v3 y documentos PDF ejecutivos. Cerrado tras validación funcional completa.
- *CHG-008 (sobre RNF-01 - Rendimiento):* Incorpora caché en memoria mediante Redis y reestructuración de índices compuestos B-Tree en MariaDB para responder consultas en menos de 0.5 s.
- *CHG-009 (sobre RNF-03 - Usabilidad):* Actualización de hojas de estilo y componentes visuales para garantizar contraste mínimo de 4.5:1 y ergonomía para usuarios analistas.
- *CHG-010 (sobre RC-01 - Restricción Técnica):* Crea una capa de abstracción de repositorio (_Repository Pattern_) para permitir interoperabilidad entre MariaDB, PostgreSQL y buckets S3.

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

El seguimiento periódico de los diez requerimientos de cambio permitió monitorear el avance del proyecto y detectar posibles desvíos respecto al cronograma establecido:

#table(
  columns: (0.9fr, 1.8fr, 1.3fr, 1.4fr, 1.1fr, 2.5fr),
  align: (center, left, center, left, center, left),
  table.header([*ID*], [*Descripción Breve*], [*Estado Actual*], [*Responsable*], [*Límite*], [*Observaciones y Estado de Avance*]),
  [CHG-001], [Importación JSON/CSV], [En desarrollo], [L. Sequeiros], [15/09/2026], [Parser de CSV validado; integrando importador JSON.],
  [CHG-002], [Esquema ISO 29148], [En revisión], [Arq. Software], [18/09/2026], [Evaluación de impacto sobre catálogo existente en curso.],
  [CHG-003], [Grafo de Trazabilidad], [En pruebas], [Auditor QA], [20/09/2026], [Pruebas de estrés superadas con 500 nodos sin latencia.],
  [CHG-004], [Webhooks de Alerta], [Pendiente], [DevOps], [22/09/2026], [A la espera de definir endpoint de mensajería del CCB.],
  [CHG-005], [Autenticación 2FA], [Cerrado], [Ciberseguridad], [10/09/2026], [Verificado con tokens TOTP y desplegado en producción.],
  [CHG-006], [Validación Semántica], [En desarrollo], [Ing. Calidad], [25/09/2026], [Reglas sintácticas implementadas al 60%; pruebas de falsos positivos.],
  [CHG-007], [Exportación OpenAPI/PDF], [Cerrado], [Dev Backend], [12/09/2026], [Exportador validado y documentado con Swagger UI.],
  [CHG-008], [Optimización Redis], [En pruebas], [DBA / Backend], [24/09/2026], [Caché en staging reduce latencia promedio a 120 ms.],
  [CHG-009], [Accesibilidad WCAG 2.1], [En revisión], [Diseñador UI], [28/09/2026], [Revisión de paletas de color con equipo de usabilidad.],
  [CHG-010], [Persistencia Multi-Nube], [Pendiente], [Arq. Cloud], [30/09/2026], [Elaborando diagrama de arquitectura y costos asociados.],
)

// PLACEHOLDER: Captura del Reporte o Registro de Auditoría (Logbook de i-doit o Reporte CLI de GitHub)
// #figure(
//   image("/l2/img/reporte-seguimiento.png", width: 85%),
//   caption: [Reporte consolidado de seguimiento y auditoría de cambios.],
// ) <fig-reporte-seguimiento>
