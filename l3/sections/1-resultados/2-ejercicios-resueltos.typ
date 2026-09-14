== II. Ejercicios Resueltos

=== a) Ejercicio Resuelto 1: Herramientas para Elaboración de Diagramas UML y Mockups

La selección del instrumental para la especificación y diseño de software repercute directamente en la calidad del producto, la velocidad de iteración y la trazabilidad de los artefactos. Se evaluaron dos paradigmas complementarios:

1. *Paradigma Diagrams as Code (DaC) — PlantUML y Mermaid:*
   Permite especificar diagramas mediante lenguajes textuales formales. Sus ventajas radican en la trazabilidad en sistemas de control de versiones Git, posibilitando revisiones técnicas de cambios y regeneración continua de gráficos vectoriales sin pérdida de resolución.
2. *Herramientas Visuales de Prototipado UX/UI — Moqups, Draw.io y Lucidchart:*
   Plataformas orientadas a la elaboración de maquetas de interfaz de usuario (_wireframes_ y _mockups_ interactivos). Facilitan la validación temprana de flujos de interacción con clientes y usuarios finales.

La @tab-comparativa-herramientas sintetiza la evaluación técnica de las herramientas analizadas:

#figure(
  table(
    columns: (1.3fr, 1.4fr, 1.2fr, 1.5fr, 1.6fr),
    align: (left, center, center, left, left),
    table.header([*Herramienta*], [*Paradigma*], [*Curva*], [*Soporte Git / CI*], [*Caso de Uso Óptimo*]),
    [PlantUML], [Diagrams as Code], [Baja], [Excelente (Diff texto)], [Modelado estricto de clases, interfaces y secuencias.],
    [Mermaid], [Diagrams as Code], [Baja], [Excelente (Nativo)], [Documentación arquitectónica viva y diagramas ágiles.],
    [Moqups], [Visual / Web GUI], [Baja], [Limitado (Binario)], [Diseño rápido de pantallas y flujos de usuario.],
    [Draw.io], [Visual / Híbrido], [Baja], [Medio (XML)], [Topologías de despliegue y diagramas de contexto.],
    [Lucidchart], [Visual / Cloud GUI], [Baja], [Bajo (Propietario)], [Sesiones colaborativas de diseño síncrono.],
  ),
  caption: [Evaluación comparativa de herramientas de modelado UML y prototipado de interfaces.],
) <tab-comparativa-herramientas>

=== b) Ejercicio Resuelto 2: Revisión Metodológica del Estándar IEEE Std. 1016-2009

El estándar internacional *IEEE Std. 1016-2009* (_Software Design Descriptions_) establece los requisitos normativos sobre el contenido y la estructura de la información de diseño de software @ieee1016. Su principio medular determina que un sistema complejo debe documentarse a través de un conjunto coordinado de *Vistas de Diseño* (_Design Views_), donde cada vista aborda preocupaciones técnicas específicas gobernadas por un *Punto de Vista de Diseño* (_Design Viewpoint_).

==== Conceptos Fundamentales del Estándar

- *Software Design Description (SDD):* Documento técnico que captura las decisiones arquitectónicas para comunicarlas a los diversos interesados (_stakeholders_).
- *Interesados (Stakeholders):* Actores con intereses legítimos en el sistema (adquirentes, administradores de proyecto, arquitectos, programadores, ingenieros de calidad y mantenedores).
- *Preocupación de Diseño (Design Concern):* Área de interés crítico para uno o más interesados (modularidad, fiabilidad, rendimiento, seguridad o distribución).
- *Punto de Vista (Design Viewpoint):* Especificación de las reglas, notaciones de modelado y métodos analíticos para construir una vista.
- *Atributos de Entidad de Diseño:* La norma exige documentar para cada componente su *Identificación*, *Tipo*, *Propósito*, *Función*, *Subordinados*, *Dependencias*, *Interfaces* y *Recursos consumidos*.

La @tab-ieee-uml establece la correspondencia entre los puntos de vista del estándar y los diagramas de UML 2.5:

#figure(
  table(
    columns: (1.5fr, 2.2fr, 1.8fr, 1.5fr),
    align: (left, left, left, left),
    table.header([*Punto de Vista (IEEE 1016)*], [*Preocupaciones Técnicas Abordadas*], [*Diagrama UML Asociado*], [*Audiencia Objetivo*]),
    [Context Viewpoint], [Fronteras del sistema, actores externos y servicios del entorno.], [Diagrama de Casos de Uso], [Usuarios, Adquirentes],
    [Composition Viewpoint], [Descomposición modular en subsistemas y componentes.], [Diagrama de Componentes], [Arquitectos, Gestores],
    [Logical Viewpoint], [Estructura de clases, atributos, métodos e invariantes.], [Diagrama de Clases / Objetos], [Desarrolladores, QA],
    [Dependency Viewpoint], [Acoplamiento, importación y llamadas entre módulos.], [Diagrama de Paquetes], [Mantenedores, Auditores],
    [Information Viewpoint], [Entidades de persistencia, estructuras de datos y esquemas.], [Diagrama de Clases (Dominio)], [DBAs, Desarrolladores],
    [Interface Viewpoint], [Firmas de métodos, parámetros, contratos y excepciones.], [Diagrama de Clases / Secuencia], [Integradores, QA],
    [Structure Viewpoint], [Conectividad interna entre partes y puertos de enlace.], [Diagrama de Estructura Compuesta], [Arquitectos de Software],
    [Interaction Viewpoint], [Secuencia temporal de mensajes y colaboraciones entre objetos.], [Diagrama de Secuencia], [Desarrolladores, Testers],
    [State Dynamics Viewpoint], [Transiciones reactivas, ciclos de vida y eventos de estado.], [Diagrama de Máquina de Estados], [Diseñadores, QA],
    [Algorithm Viewpoint], [Lógica procedimental, control condicional y concurrencia.], [Diagrama de Actividades], [Desarrolladores, Auditores],
  ),
  caption: [Matriz de correspondencia entre Puntos de Vista de IEEE Std. 1016-2009 y diagramas UML 2.5.],
) <tab-ieee-uml>
