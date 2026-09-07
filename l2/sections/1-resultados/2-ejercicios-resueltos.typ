== II. Implementación y Configuración del Control de Cambios

=== a) Caracterización de las Plataformas de Gestión

Para articular el control de cambios y garantizar la trazabilidad integral de los requerimientos, se instrumentaron dos soluciones tecnológicas representativas de enfoques complementarios:

*1. i-doit en la Gestión de Configuración (CMDB / ITIL):*

Para el inventario y modelado de componentes, i-doit permite registrar formalmente los módulos de software y servicios como Elementos de Configuración (CIs). Como se ilustra en la @fig-idoit-dashboard, el espacio de trabajo del proyecto consolida cinco aplicaciones operativas en un catálogo estructurado @itil4.

#figure(
  image("/l2/img/idoit-dashboard.png", width: 72%),
  caption: [Entorno principal de gestión de configuración en i-doit evidenciando el espacio de trabajo del proyecto y el registro consolidado de 5 aplicaciones.],
) <fig-idoit-dashboard>

Asimismo, cada componente posee una ficha técnica detallada que especifica su estado CMDB, asignaciones de contacto y relaciones funcionales. La @fig-idoit-ci-detail evidencia la parametrización del módulo 2FA (CI #27), cuyos atributos y dependencias quedan custodiados de manera formal para auditorías de calidad.

#figure(
  image("/l2/img/idoit-ci-detail.png", width: 72%),
  caption: [Ficha técnica detallada del elemento de configuración CI #27 (`Módulo de Autenticación Multifactor 2FA`) en i-doit con sus atributos y estado operativo.],
) <fig-idoit-ci-detail>

*2. GitHub Projects en la Planificación Ágil y Gobernanza Colaborativa:*

En la dimensión de gestión ágil, GitHub Projects organiza las solicitudes de cambio mediante incidencias categorizadas. En la @fig-gh-issues se observa el catálogo consolidado de las diez RFCs, estructuradas mediante etiquetas jerárquicas de prioridad (`prioridad:alta`, etc.) y estado del ciclo de vida para coordinar la asignación del equipo.

#figure(
  image("/l2/img/github-issues-list.png", width: 72%),
  caption: [Catálogo de las 10 Solicitudes de Cambio (RFCs) registradas como issues en GitHub con etiquetado semántico de estado y criticidad.],
) <fig-gh-issues>

Cada tarjeta detalla las especificaciones técnicas del requerimiento modificado. La @fig-gh-issue-detail expone la Solicitud de Cambio `CHG-005`, especificando su justificación técnica, alcance, criterios de aceptación verificables y asignación al área de ciberseguridad.

#figure(
  image("/l2/img/github-issue-detail.png", width: 72%),
  caption: [Detalle analítico de la Solicitud de Cambio `CHG-005` (RFC #5) en GitHub con justificación técnica, urgencia, alcance y criterios de aceptación.],
) <fig-gh-issue-detail>

=== b) Modelado del Flujo Metodológico de Gestión de Cambios

El control de cambios previene que modificaciones arbitrarias alteren el alcance comprometido, el presupuesto o el cronograma de entrega. En la @fig-flujo-cambios se detalla el ciclo formal de seis fases por el cual transita toda solicitud emergente antes de integrarse a la línea base @pressman2020:

#figure(
  image("/l2/img/flujo-gestion-cambios.png", width: 88%),
  caption: [Diagrama de flujo del proceso metodológico para el control de cambios en el proyecto (generado en Mermaid).],
) <fig-flujo-cambios>

*Fases del Proceso de Gestión de Cambios:*
+ *1. Registro de Solicitud de Cambio (RFC):* Cualquier parte interesada formula la necesidad mediante una ficha estructurada indicando justificación del negocio, componente afectado y nivel de urgencia.
+ *2. Análisis de Impacto Técnico y Económico:* El equipo de ingeniería cuantifica el esfuerzo estimado en horas-hombre, costos de infraestructura, riesgos asociados y posibles retrasos en la fecha de liberación.
+ *3. Dictamen del Comité de Control de Cambios (CCB):* El comité colegiado evalúa el balance costo-beneficio para emitir un dictamen formal: aprobación para desarrollo, postergación para iteraciones futuras o rechazo justificado.
+ *4. Planificación y Asignación de Recursos:* Las solicitudes aprobadas ingresan al tablero del proyecto, asignándoles prioridad, responsable técnico y fecha límite improrrogable.
+ *5. Implementación y Pruebas de Calidad (QA):* Se ejecuta la modificación en ramas aisladas del repositorio y el equipo de aseguramiento de calidad valida el cumplimiento mediante pruebas de regresión.
+ *6. Cierre e Integración en Línea Base:* Tras la certificación QA, el cambio se fusiona en la rama principal (`main`), se actualiza la documentación arquitectónica y se actualiza el estado en el catálogo de CIs.
