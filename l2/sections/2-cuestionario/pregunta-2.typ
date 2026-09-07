== Pregunta 2: Criterios de Selección y Utilidad de Herramientas de Gestión

*Enunciado:* _¿Qué herramienta considera de mejor utilidad para la gestión de requerimientos?_

La determinación de la herramienta óptima no admite una respuesta unívoca, puesto que depende directamente de la escala del sistema, el marco metodológico adoptado, el nivel de criticidad y las exigencias de cumplimiento regulatorio:

+ *Desarrollo de Software Dinámico y Ecosistemas DevOps (GitHub Projects / Jira Software):*
  - *Ventaja Estratégica:* Integración nativa e inmediata con el ciclo de vida del código fuente. Permite enlazar cada requerimiento funcional o RFC con ramas de desarrollo aisladas (_feature branches_), revisiones colegiadas de código (_pull requests_), pruebas automáticas de integración continua (CI/CD) y despliegues.
  - *Utilidad Práctica:* Constituye la alternativa más eficiente para equipos ágiles contemporáneos, erradicando la desincronización entre la documentación estática y el código ejecutable mediante automatización por línea de comandos (`gh` CLI) y tableros interactivos.

+ *Gobierno de Servicios de TI y Configuración Empresarial (i-doit / ServiceNow):*
  - *Ventaja Estratégica:* Estructuración ontológica bajo los lineamientos de ITIL 4 @itil4 y bases de datos de gestión de configuración (CMDB).
  - *Utilidad Práctica:* Resulta indispensable cuando los cambios impactan sobre topologías de red, servidores, microservicios distribuidos, dependencias entre sistemas corporativos y gestión de licenciamiento, facilitando análisis de impacto gráfico y auditorías de seguridad física y lógica.

+ *Sistemas de Misión Crítica y Rigor Normativo (IBM Engineering DOORS / Jama Connect):*
  - *Ventaja Estratégica:* Modelado de matrices de trazabilidad multidimensionales de alta granularidad y cumplimiento de estándares funcionales rigurosos (DO-178C en aviónica, ISO 26262 en automoción, IEC 62304 en dispositivos médicos).
  - *Utilidad Práctica:* Proyectos de gran envergadura donde cada cláusula de requerimiento debe contar con verificación formal matemática y custodia legal ante entes reguladores.

*Dictamen Conclusivo:*
En el contexto del desarrollo moderno de aplicaciones software, *GitHub Projects* se posiciona como la herramienta de mayor utilidad operativa por su capacidad de cerrar la brecha entre el requerimiento y el commit diario sin sobrecarga burocrática; en contraste, para el gobierno corporativo de activos y dependencias operativas, *i-doit* se consolida como una solución insustituible.
