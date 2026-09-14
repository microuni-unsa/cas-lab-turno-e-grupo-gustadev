#import "/components/lab-section.typ": lab-section

#lab-section(title: "CONCLUSIONES")[
  #show heading: set text(weight: "bold")
  #set par(justify: true)

  - La estructuración de la Descripción de Diseño de Software (SDD) mediante Puntos de Vista de Diseño (IEEE Std. 1016-2009) garantiza que cada interesado consulte la perspectiva técnica correspondiente a sus necesidades sin sobrecarga cognitiva, previniendo omisiones críticas en sistemas con alta integración distribuida.

  - El modelado formal mediante diagramas de actividades, clases y secuencia de UML 2.5 erradica la ambigüedad del lenguaje natural al definir con precisión matemática la concurrencia, las bifurcaciones en paralelo, los disparadores por ítem y las condiciones de sincronización.

  - La integración de los patrones de diseño Composite y Singleton resuelve de forma limpia la jerarquía recursiva de pedidos y la centralización del lote diario de cobros, blindando invariantes de negocio como el límite de 20 unidades por pedido simple, la titularidad común de cuentas y la atomicidad del débito financiero.

  - Las inspecciones técnicas formales basadas en el estándar IEEE Std. 1028 constituyen compuertas preventivas de calidad indispensables, mitigando defectos en fases tempranas de análisis y evitando sobrecostos de remediación que en producción pueden ser de 10 a 100 veces mayores según la curva de Boehm.

  - La adopción del enfoque Diagrams as Code asegura la trazabilidad y mantenibilidad continua de la arquitectura, permitiendo versionar los modelos visuales en conjunto con la documentación del sistema y facilitando su evolución sostenida.
]
