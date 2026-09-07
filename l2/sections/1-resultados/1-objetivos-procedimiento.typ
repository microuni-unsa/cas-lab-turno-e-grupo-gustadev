== I. Descripción del Procedimiento Metodológico

La gestión de configuración y el control sistemático de modificaciones constituyen pilares esenciales para mitigar la degradación del alcance (_scope creep_) y asegurar la calidad en proyectos de ingeniería de software @ieee828. En conformidad con las directrices de la guía práctica (Anexo 21), el procedimiento experimental se estructuró en cinco etapas secuenciales:

+ *Configuración del Entorno de Gestión y Plataformas Experimentales:*
  Se implementaron y parametrizaron dos plataformas representativas de paradigmas complementarios de gestión:
  - *i-doit (ITIL / CMDB):* Desplegada en un entorno contenerizado (Docker Compose con servidor web Apache y base de datos MariaDB) para operar como Base de Datos de Gestión de Configuración (_Configuration Management Database_, CMDB). Se destinó al inventario formal de Elementos de Configuración (_Configuration Items_, CIs), modelado de dependencias entre módulos y registro inmutable en bitácora (_Logbook_).
  - *GitHub Projects (Gestión Ágil / GitOps):* Configurada dentro de la organización académica `microuni-unsa` (repositorio `cas-lab-turno-e-grupo-gustadev`) para articular la planificación ágil, gobernanza de tareas mediante tableros Kanban interactivos, asignación de responsables y trazabilidad directa hacia confirmaciones de código fuente.

+ *Formalización del Flujo de Control de Cambios y Ciclo de Vida:*
  Se modeló el protocolo de gobernanza para procesar cualquier modificación solicitada sobre la línea base documental (_baseline_). El flujo definió los roles del Comité de Control de Cambios (_Change Control Board_, CCB) y la máquina de estados finitos que gobierna la evolución de las tareas: _Pendiente_ $->$ _En revisión_ $->$ _En desarrollo_ $->$ _En pruebas_ $->$ _Cerrado_.

+ *Elicitación y Formulación de Solicitudes de Cambio (RFC):*
  Tomando como base los requerimientos funcionales y no funcionales analizados en el Laboratorio 1, se redactaron diez Solicitudes de Cambio formales (_Requests for Change_, RFC: `CHG-001` a `CHG-010`). Cada RFC incorporó su justificación técnica, requerimiento de origen, criticidad, responsable asignado y fecha límite de entrega.

+ *Ejecución, Monitoreo y Auditoría de Estados:*
  Se ejecutó el seguimiento dinámico de las RFCs distribuidas entre los miembros del equipo. Se verificó el cumplimiento de las políticas de revisión previa, registrando las transiciones de estado empíricas tanto en el tablero Kanban como en la bitácora de auditoría de i-doit.

+ *Evaluación Comparativa Multicriterio:*
  Se contrastaron cuantitativa y cualitativamente ambas herramientas en función de su enfoque primario, curva de adopción, granularidad de auditoría, soporte de flujos colaborativos y capacidad de respuesta ante desvíos del cronograma.
