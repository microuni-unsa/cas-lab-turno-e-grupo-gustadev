== II. Implementación y Configuración del Control de Cambios

=== a) Caracterización de las Plataformas de Gestión

Para articular el control de cambios y garantizar la trazabilidad integral de los requerimientos, se instrumentaron dos soluciones tecnológicas representativas de enfoques complementarios:

*1. i-doit en la Gestión de Configuración (CMDB / ITIL):*
- *Inventario y Modelado de Elementos de Configuración (CIs):* Permite catalogar formalmente los módulos de software, microservicios, bases de datos y componentes de infraestructura como entidades interconectadas. Cada CI mantiene su estado operativo, responsable técnico y criticidad para el negocio @itil4.
- *Bitácora Inmutable de Auditoría (Logbook):* Cada evento de inserción, modificación de atributos o reasignación de estado es registrado de manera automática con sello de tiempo, usuario ejecutor y nivel de alarma (véase @fig-idoit-dashboard y @fig-idoit-ci-detail).

#figure(
  image("/l2/img/idoit-dashboard.png", width: 88%),
  caption: [Entorno principal de gestión de configuración en i-doit evidenciando el espacio de trabajo del proyecto y el registro consolidado de 5 aplicaciones.],
) <fig-idoit-dashboard>

#figure(
  image("/l2/img/idoit-ci-detail.png", width: 88%),
  caption: [Ficha técnica detallada del elemento de configuración CI #27 (`Módulo de Autenticación Multifactor 2FA`) en i-doit con sus atributos y estado operativo.],
) <fig-idoit-ci-detail>

*2. GitHub Projects en la Planificación Ágil y Gobernanza Colaborativa:*
- *Estructuración y Asignación de RFCs:* Mediante el sistema de incidencias y tarjetas (*issues*), las solicitudes de cambio son categorizadas a través de metadatos estandarizados: etiquetas jerárquicas de prioridad (`prioridad:alta`, etc.), estado del ciclo de vida y responsable asignado.
- *Visualización Dinámica del Flujo de Valor:* El tablero interactivo sincroniza en tiempo real las fases de trabajo entre los miembros del equipo, minimizando tiempos de bloqueo y optimizando la toma de decisiones basada en el trabajo en curso (_Work In Progress_, WIP) (véase @fig-gh-issues y @fig-gh-issue-detail).

#figure(
  image("/l2/img/github-issues-list.png", width: 88%),
  caption: [Catálogo de las 10 Solicitudes de Cambio (RFCs) registradas como issues en GitHub con etiquetado semántico de estado y criticidad.],
) <fig-gh-issues>

#figure(
  image("/l2/img/github-issue-detail.png", width: 88%),
  caption: [Detalle analítico de la Solicitud de Cambio `CHG-005` (RFC #5) en GitHub con justificación técnica, urgencia, alcance y criterios de aceptación.],
) <fig-gh-issue-detail>

=== b) Modelado del Flujo de Gestión de Cambios (Anexo 21)

El control de cambios previene que modificaciones arbitrarias alteren el alcance comprometido, el presupuesto o el cronograma de entrega. Todo requerimiento emergente es canalizado mediante el protocolo de seis fases ilustrado en la @fig-flujo-cambios @pressman2020:

#figure(
  image("/l2/img/flujo-gestion-cambios.png", width: 45%),
  caption: [Diagrama de flujo del proceso metodológico para el control de cambios en el proyecto (generado en Mermaid).],
) <fig-flujo-cambios>

*Fases del Proceso de Gestión de Cambios:*
+ *1. Registro de Solicitud de Cambio (RFC):* Cualquier parte interesada formula la necesidad mediante una ficha estructurada indicando justificación del negocio, componente afectado y nivel de urgencia.
+ *2. Análisis de Impacto Técnico y Económico:* El equipo de ingeniería cuantifica el esfuerzo estimado en horas-hombre, costos de infraestructura, riesgos asociados y posibles retrasos en la fecha de liberación.
+ *3. Dictamen del Comité de Control de Cambios (CCB):* El comité colegiado evalúa el balance costo-beneficio para emitir un dictamen formal: aprobación para desarrollo, postergación para iteraciones futuras o rechazo justificado.
+ *4. Planificación y Asignación de Recursos:* Las solicitudes aprobadas ingresan al tablero del proyecto, asignándoles prioridad, responsable técnico y fecha límite improrrogable.
+ *5. Implementación y Pruebas de Calidad (QA):* Se ejecuta la modificación en ramas aisladas del repositorio y el equipo de aseguramiento de calidad valida el cumplimiento mediante pruebas de regresión.
+ *6. Cierre e Integración en Línea Base:* Tras la certificación QA, el cambio se fusiona en la rama principal (`main`), se actualiza la documentación arquitectónica y se actualiza el estado en el catálogo de CIs.
