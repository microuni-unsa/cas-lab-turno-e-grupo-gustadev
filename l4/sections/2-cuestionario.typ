#import "/components/lab-section.typ": lab-section

#lab-section(title: "CUESTIONARIO")[
  #show heading: set text(weight: "bold")
  #set par(justify: true)

  === 1. ¿Qué resuelve conocer el dimensionamiento temprano del proceso de construcción de software?

  Conocer el dimensionamiento en etapas iniciales permite reducir la incertidumbre en la toma de decisiones técnicas y gerenciales @boehm2000. Sus principales aportes son:

  - *Evaluación de viabilidad económica:* Determina si el proyecto es factible antes de comprometer recursos y firmar contratos vinculantes.
  - *Planificación fundamentada de plazos y personal:* Proporciona un cálculo objetivo de las personas-mes y la duración esperada, evitando fechas límite arbitrarias que comprometan la calidad del producto.
  - *Control del alcance:* Al fijar una línea base funcional cuantitativa, cualquier cambio solicitado durante el desarrollo puede evaluarse con precisión en costo y tiempo adicional.
  - *Medición de productividad:* Permite comparar el rendimiento real del equipo frente a registros históricos de la organización.

  === 2. Indique la ventaja de usar la metodología NESMA

  La metodología NESMA, normalizada en el estándar ISO/IEC 24570 @iso24570, ofrece ventajas clave frente a otros métodos funcionales:

  - *Flexibilidad con tres niveles de detalle:* Permite medir según el grado de madurez de los requisitos disponibles:
    1. _Conteo Indicativo:_ Estima el tamaño evaluando solo los archivos lógicos ALI y AIE, deduciendo las transacciones con factores estadísticos para estudios preliminares de viabilidad.
    2. _Conteo Estimativo:_ Identifica todas las funciones de datos y transacciones asignando complejidades medias por defecto, adecuado durante el diseño inicial.
    3. _Conteo Detallado:_ Conteo exhaustivo de alta precisión idéntico a las reglas estándar de IFPUG.
  - *Independencia de la tecnología:* Mide la funcionalidad entregada al usuario sin depender del lenguaje de programación o la arquitectura elegida.
  - *Alta repetibilidad y consistencia:* Reduce la discrepancia entre diferentes analistas, facilitando auditorías y comparaciones objetivas entre proyectos.

  === 3. ¿Es confiable el dimensionamiento COCOMO, por qué?

  El modelo COCOMO II es confiable como herramienta de estimación macroscópica @boehm2000 @pressman2020 debido a sus fundamentos:

  - *Base empírica validada:* Se fundamenta en datos estadísticos de cientos de proyectos reales de software analizados rigurosamente.
  - *Modelado no lineal del esfuerzo:* Contempla que el esfuerzo no crece de manera lineal con el tamaño, incorporando factores de escala para reflejar la complejidad agregada de comunicación y pruebas en proyectos mayores.
  - *Ajuste multidimensional:* Permite calibrar la estimación considerando la capacidad del personal PERS, la experiencia en plataforma PREX y el uso de herramientas CASE.

  Su confiabilidad requiere exactitud en el tamaño de entrada y una evaluación objetiva de los factores del entorno.
]
