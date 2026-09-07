== Resultados Obtenidos: Ejercicios Propuestos

=== a) Requerimientos Funcionales y No Funcionales

Estructuración formal de los requerimientos aplicando la norma IEEE Std 830-1998:

*1. Requerimientos Funcionales:*

#table(
  columns: (1fr, 1.8fr, 3.5fr, 1.2fr, 1fr),
  align: (center, left, left, center, center),
  table.header([*Código*], [*Nombre*], [*Descripción*], [*Prioridad*], [*Estado*]),
  [RF-01], [Elicitación y Registro], [El sistema permite ingresar nuevos requisitos asignando identificador unívoco, descripción, prioridad, versión y fuente de origen.], [Vital / Inmediata], [Validado],
  [RF-02], [Clasificación IEEE 830], [El sistema clasifica los requisitos en funcionales, no funcionales y restricciones de diseño.], [Vital / Inmediata], [Validado],
  [RF-03], [Trazabilidad Bidireccional], [El sistema vincula requisitos con objetivos y casos de uso, generando la matriz de trazabilidad y detectando elementos huérfanos.], [Vital / Inmediata], [Validado],
  [RF-04], [Control de Cambios], [El sistema mantiene el historial de versiones de cada requisito, permitiendo comparar versiones anteriores y registrar solicitudes de cambio justificadas.], [Importante / Media], [Validado],
  [RF-05], [Validación de Consistencia], [El sistema comprueba que ningún requisito carezca de atributos obligatorios o enlaces de trazabilidad.], [Importante / Media], [Validado],
  [RF-06], [Exportación de Especificación], [El sistema genera y exporta la especificación de requisitos en formatos estándar XML y reportes HTML navegables.], [Importante / Media], [Validado],
)

#figure(
  image("/l1/img/requisito-funcional.jpeg", width: 80%),
  caption: [Ficha técnica de un Requisito Funcional en REM.],
) <fig-rem-rf>

*2. Requerimientos No Funcionales y Restricciones:*

#table(
  columns: (1.1fr, 1.8fr, 3.5fr, 1.2fr, 1.2fr),
  align: (center, left, left, center, center),
  table.header([*Código*], [*Categoría*], [*Descripción*], [*Criterio de Aceptación*], [*Prioridad*]),
  [RNF-01], [Rendimiento], [Las consultas sobre el catálogo y generación de matrices deben responder en tiempo oportuno.], [Tiempo de respuesta menor o igual a 2 segundos para 10,000 registros.], [Importante],
  [RNF-02], [Seguridad], [Control de acceso basado en roles para la modificación de requisitos validados.], [Solo roles autorizados modifican artefactos validados.], [Vital],
  [RNF-03], [Usabilidad], [Interfaz estructurada con formularios guiados y validación en tiempo real.], [Curva de aprendizaje menor a 2 horas para nuevos analistas.], [Importante],
  [RC-01], [Restricción Técnica], [Persistencia y validación formal de datos compatible con el esquema de REM.], [Validación conforme con el esquema formal de la herramienta.], [Vital],
)

=== b) Cronograma de Actividades según Modelo de Proceso

La gestión de requisitos se organiza bajo el Proceso Unificado en cinco fases iterativas:

#figure(
  image("/l1/img/cronograma-actividades.svg", width: 95%),
  caption: [Cronograma de ingeniería de requisitos y diagrama de Gantt.],
) <fig-cronograma>

*Desglose de Actividades y Entregables:*

#table(
  columns: (0.8fr, 2.2fr, 2.8fr, 1.2fr, 1.2fr),
  align: (center, left, left, center, center),
  table.header([*Fase*], [*Actividad y Tareas*], [*Entregables Generados*], [*Duración*], [*Responsable*]),
  [1.0], [Elicitación y Descubrimiento \ - Entrevistas a partes interesadas \ - Aplicación de cuestionarios \ - Análisis documental], [Documento de Visión, Actas de reunión, Catálogo inicial de necesidades.], [Semana 1], [Analista de Requisitos],
  [2.0], [Análisis y Negociación \ - Modelado de Objetivos \ - Identificación y resolución de conflictos \ - Priorización por valor de negocio], [Catálogo de Objetivos formalizado, Matriz de resolución de conflictos.], [Semana 2], [Analista y Cliente],
  [3.0], [Especificación Formal \ - Definición de requerimientos funcionales y no funcionales \ - Modelado de Casos de Uso \ - Asignación de atributos IEEE 830], [Documento de especificación preliminar, Base de datos del proyecto estructurada.], [Semanas 3-4], [Ingeniero de Requisitos],
  [4.0], [Verificación y Validación \ - Creación de Matriz de Trazabilidad \ - Verificación de consistencia \ - Revisión con cliente], [Matriz de Trazabilidad completa, Reporte de verificación, Acta de conformidad.], [Semana 5], [Auditor QA y Cliente],
  [5.0], [Gestión y Control de Cambios \ - Establecimiento de Línea Base \ - Exportación final a XML y HTML \ - Control de solicitudes de cambio], [Línea base formal de requisitos, Especificación aprobada, Paquete de artefactos.], [Semana 6], [Gestor de Configuración],
)

=== c) Organigrama del Proyecto y Matriz RACI

Estructura de roles y asignación de responsabilidades:

#figure(
  image("/l1/img/organigrama-roles.svg", width: 92%),
  caption: [Organigrama de roles para la gestión de requisitos del proyecto.],
) <fig-organigrama>

*Matriz RACI de Responsabilidades:*
- *R:* Responsable de ejecución | *A:* Aprobador | *C:* Consultado | *I:* Informado

#table(
  columns: (2.5fr, 1fr, 1fr, 1fr, 1fr, 1fr),
  align: (left, center, center, center, center, center),
  table.header([*Actividad*], [*Cliente*], [*Analista*], [*Auditor QA*], [*Desarrollador*], [*Usuario*]),
  [Elicitación de Necesidades], [A], [R], [C], [I], [C],
  [Formulación de Requerimientos], [I], [R], [C], [C], [I],
  [Matriz de Trazabilidad], [I], [R], [A], [C], [I],
  [Validación y Firma de Especificación], [A], [C], [R], [I], [C],
  [Gestión de Solicitudes de Cambio], [A], [R], [C], [I], [I],
)

=== d) Evaluación Comparativa de Herramientas CASE

Comparación técnica entre REM, OSRMT y Gatherspace bajo criterios de la norma ISO/IEC TR 24766 @iso24766:

#table(
  columns: (2.2fr, 1.8fr, 1.8fr, 1.8fr),
  align: (left, center, center, center),
  table.header([*Criterio Técnico*], [*REM*], [*OSRMT*], [*Gatherspace*]),
  [Plataforma y Arquitectura], [Escritorio y base de datos relacional], [Escritorio Java y SQL], [Web en la nube],
  [Soporte Nativo IEEE 830], [Alto con estructura formal integrada], [Medio con campos genéricos], [Medio orientado a historias ágiles],
  [Gestión de Trazabilidad], [Matriz bidireccional automatizada], [Trazabilidad gráfica basada en grafos], [Trazabilidad básica por enlaces],
  [Validación de Consistencia], [Alta con esquemas formales], [Media mediante control manual], [Baja sin motor formal de reglas],
  [Exportación de Informes], [XML estandarizado y HTML interactivo], [PDF, CSV y HTML básico], [Reportes web e impresos],
  [Curva de Aprendizaje], [Baja a Media], [Media], [Baja],
  [Licenciamiento], [Código abierto académico], [Código abierto], [Comercial por suscripción],
)

*Ventajas y Desventajas:*
- *REM:* Destaca por su rigor metodológico bajo IEEE 830, trazabilidad automatizada y exportación estructurada en XML. Requiere entorno de escritorio para su ejecución.
- *OSRMT:* Destaca por ser multiplataforma en Java y utilizar bases de datos relacionales abiertas. Requiere mayor configuración de infraestructura SQL.
- *Gatherspace:* Destaca por su acceso colaborativo en la nube sin instalación local. Presenta menor personalización para atributos normativos formales.
