== II. Resultados Obtenidos: Ejercicios Resueltos

=== a) Herramientas para la Gestión de Proyectos y Control de Cambios

Para la administración del proyecto y la trazabilidad de los requerimientos, se emplearon dos herramientas orientadas a diferentes facetas de la gestión:

*1. i-doit en la Gestión de Configuración del Proyecto:*
- *Inventario y Catálogo de Elementos del Proyecto:* Permite registrar los módulos de software, dependencias funcionales y componentes del sistema como elementos de configuración (CIs), manteniendo claridad sobre qué partes del proyecto existen y cómo se relacionan entre sí.
- *Bitácora de Auditoría (Logbook):* Cada cambio o actualización sobre los componentes del proyecto queda registrado cronológicamente con autor y fecha, garantizando control formal de versiones y facilitando auditorías de seguimiento.

// PLACEHOLDER: Captura de pantalla de la interfaz de i-doit (Catálogo de módulos o Logbook del proyecto)
// #figure(
//   image("/l2/img/idoit-dashboard.png", width: 85%),
//   caption: [Registro y auditoría de componentes del proyecto en i-doit.],
// ) <fig-idoit-dashboard>

*2. GitHub Projects en la Planificación Ágil y Colaboración del Equipo:*
- *Planificación y Asignación:* Facilita la división del trabajo mediante tarjetas de cambio (*RFC*), asignando responsables individuales, prioridades y fechas límite de entrega.
- *Visibilidad del Flujo de Trabajo:* El tablero Kanban interactivo proporciona a los miembros del equipo y a los interesados una visión en tiempo real del estado de cada requerimiento, agilizando la toma de decisiones y la coordinación diaria.

// PLACEHOLDER: Captura de pantalla del repositorio y listado de issues/etiquetas en GitHub
// #figure(
//   image("/l2/img/github-issues-list.png", width: 85%),
//   caption: [Listado de requerimientos de cambio en GitHub para el seguimiento del equipo.],
// ) <fig-gh-issues>

=== b) Diagrama de Flujo del Proceso de Gestión de Cambios (Anexo 21)

El control de cambios del proyecto asegura que cualquier modificación solicitada pase por un ciclo formal de revisión antes de ser incorporada. De este modo, se protege el alcance, se evalúa el esfuerzo requerido y se previenen retrasos imprevistos en el cronograma:

#figure(
  image("/l2/img/flujo-gestion-cambios.png", width: 65%),
  caption: [Diagrama de flujo del proceso de gestión de cambios del proyecto (generado en Mermaid).],
) <fig-flujo-cambios>

*Fases del Proceso de Gestión de Cambios:*
+ *1. Registro de Solicitud de Cambio (RFC):* Un miembro del equipo o cliente formula la necesidad indicando su justificación, urgencia y requerimiento afectado.
+ *2. Análisis de Impacto:* El equipo estima el esfuerzo en horas, los costos asociados y el impacto en las fechas de entrega.
+ *3. Evaluación del Comité de Control de Cambios (CCB):* Se decide colegiadamente si la solicitud se aprueba, se rechaza o se posterga.
+ *4. Planificación y Asignación:* Si es aprobada, se asigna al responsable y se establece la fecha límite en el tablero del proyecto.
+ *5. Implementación y Pruebas QA:* Se realiza la modificación y el área de calidad valida que cumpla con los criterios acordados.
+ *6. Cierre e Integración:* Se actualiza la línea base documental del proyecto y se da por completada la tarea.
