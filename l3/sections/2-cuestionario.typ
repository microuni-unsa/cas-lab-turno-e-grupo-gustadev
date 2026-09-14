#import "/components/lab-section.typ": lab-section

#lab-section(title: "CUESTIONARIO")[
  #show heading: set text(weight: "bold")
  #set par(justify: true)

  === 1. ¿Actualmente qué actividades utiliza con frecuencia en la gestión de requerimientos y cuáles considera que son esenciales?

  En los flujos habituales de desarrollo se emplean con alta frecuencia actividades orientadas a la agilidad como la *elicitación mediante historias de usuario*, las sesiones de *refinamiento colaborativo del backlog* y la construcción de *prototipos visuales rápidos* para validar interacciones con el usuario final @iso29148.

  Sin embargo, para garantizar la calidad y robustez del software, existen actividades que resultan *estrictamente esenciales*:

  - La *elicitación estructurada con criterios de aceptación verificables*, asegurando que ningún requerimiento se apruebe sin condiciones unívocas de éxito y fallo para erradicar la ambigüedad desde el inicio.
  - La *matriz de trazabilidad bidireccional*, la cual vincula sistemáticamente cada requerimiento con sus casos de uso, modelos arquitectónicos y pruebas de verificación, impidiendo requerimientos huérfanos.
  - Las actividades de *verificación y validación temprana*, apoyadas en inspecciones técnicas formales bajo lineamientos como IEEE Std. 1028 para identificar omisiones e inconsistencias antes de iniciar la construcción.

  === 2. ¿Qué herramienta considera de mejor utilidad para la gestión de requerimientos?

  La elección de la herramienta más adecuada está condicionada por la criticidad del producto y el modelo operativo del equipo. En la práctica profesional se destacan tres alternativas principales:

  - *Jira Software junto a Confluence* representa la solución de mayor utilidad práctica en entornos colaborativos y metodologías ágiles, pues articula historias de usuario jerárquicas, define flujos de aprobación personalizados y centraliza la visibilidad operativa del proyecto.
  - *Enterprise Architect o Visual Paradigm* son herramientas de máxima utilidad cuando el dominio exige un *modelado formal riguroso*, ingeniería basada en modelos (MBSE) y una vinculación directa con diagramas UML normativos.
  - *DOORS o Helix RM* se consolidan como plataformas idóneas en sectores de *alta regulación y criticidad estricta*, tales como la ingeniería aeroespacial, médica o automotriz, donde las auditorías de cumplimiento normativo son obligatorias.

  Para proyectos corporativos de software general, la combinación de *Jira articulado con documentación técnica viva* ofrece el equilibrio más eficiente entre agilidad, trazabilidad y control de requerimientos.

  === 3. ¿Qué porcentaje de recursos del proyecto considera aceptable para gestión de cambios?

  En concordancia con los estándares de ingeniería de software (PMBOK y métricas empíricas del SEI), se considera técnicamente aceptable e idóneo asignar entre un *8% y un 15% del presupuesto, tiempo y esfuerzo total del proyecto* a los procesos de gestión y control de cambios @pressman2020.

  Este esfuerzo se distribuye habitualmente en tres frentes operativos clave:

  - La evaluación y dictamen formal de solicitudes de cambio por parte del *Comité de Control de Cambios*.
  - El *análisis de impacto arquitectónico*, la estimación de riesgos técnicos y el reajuste del cronograma del proyecto.
  - La *actualización de la línea base documental*, la reconfiguración de matrices de trazabilidad y el versionado formal de artefactos.

  Asignar menos del *5%* genera un riesgo crítico de *corrupción del alcance* (_scope creep_), provocando inestabilidad en el diseño por cambios imprevistos. Por el contrario, destinar más del *20%* sobrecarga el proyecto con burocracia excesiva que reduce la productividad. El rango del *8% al 15%* maximiza la estabilidad de la arquitectura y salvaguarda los costos de desarrollo.
]
