= Pregunta 2: Herramienta de mejor utilidad para la gestión de requerimientos

*Enunciado:* _¿Qué herramienta considera de mejor utilidad para la gestión de requerimientos?_

La selección de la herramienta óptima no es absoluta, sino que depende directamente del tamaño de la organización, la criticidad del sistema, la metodología de desarrollo adoptada y el nivel de rigor regulatorio exigido:

+ *Desarrollo de Software Dinámico y DevOps (GitHub Projects / Jira Software):*
  - *Ventaja Clave:* Integración nativa con el ciclo de vida del código fuente. Permite vincular de forma directa cada requerimiento (issue) con ramas de trabajo (_feature branches_), revisiones de código (_pull requests_), pruebas automáticas (CI/CD) y despliegues.
  - *Utilidad:* Es la opción más eficiente y moderna para equipos de desarrollo contemporáneos, al eliminar la desincronización entre la documentación y el código real mediante automatizaciones de línea de comandos (`gh` CLI) y tableros Kanban interactivos.

+ *Gestión de Servicios de TI y Configuración Empresarial (i-doit / ServiceNow):*
  - *Ventaja Clave:* Enfoque estructurado bajo ITIL @itil4 y bases de datos de gestión de configuración (CMDB).
  - *Utilidad:* Indispensable cuando los requerimientos involucran la modificación de infraestructura, servidores, dependencias entre servicios y licencias de software, proveyendo análisis de impacto visual y trazabilidad de activos de misión crítica.

+ *Sistemas Críticos y Normativos de Gran Escala (IBM Engineering Requirements / DOORS, Jama Connect):*
  - *Ventaja Clave:* Rigor formal, matrices de trazabilidad multidimensionales y cumplimiento de estándares de seguridad funcional (DO-178C, ISO 26262, IEC 62304).
  - *Utilidad:* Proyectos aeroespaciales, médicos y automotrices donde cada línea de requerimiento debe tener validación formal y auditoría legal estricta.

*Dictamen:*
En el ámbito de la ingeniería y construcción de software de mediano y gran porte, *GitHub Projects* representa la herramienta de mayor utilidad práctica por su capacidad de unificar la especificación de requerimientos con el flujo diario del desarrollador sin fricción burocrática, mientras que para la administración y gobierno de configuración de plataformas operativas empresariales, soluciones especializadas como *i-doit* resultan insustituibles.
