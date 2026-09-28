== I. Objetivos y Procedimiento

=== a) Objetivos de la Práctica

1. *Comprender y aplicar la metodología de dimensionamiento funcional* mediante Puntos de Función bajo el estándar ISO/IEC 24570 de NESMA @iso24570 y las reglas de IFPUG @ifpug2010, cuantificando el tamaño del software a partir de los requerimientos en etapas tempranas.
2. *Calcular y parametrizar modelos de esfuerzo y costo* utilizando COCOMO II @boehm2000, evaluando la conversión del tamaño funcional a líneas de código equivalentes SLOC, personas-mes PM y tiempo de desarrollo TDEV.
3. *Utilizar y contrastar herramientas de cálculo* de estimación paramétrica y probabilística PERT, analizando las diferencias entre ambos modelos.
4. *Evaluar escenarios de asignación de personal* para 1 y 3 desarrolladores, analizando el impacto de la experiencia del equipo en el presupuesto en soles y el cronograma de entrega.

=== b) Procedimiento Metodológico

La práctica se desarrolló en las siguientes etapas:

1. *Delimitación de la frontera del sistema:* Se definió el alcance del Cajero Automático ATM del Anexo 1, separando las funciones internas del terminal de las entidades externas Banco Emisor y Red de Cajeros del Consorcio.
2. *Verificación del caso de ejemplo en hoja de cálculo:* Se analizó la plantilla de estimación COCOMO II provista en la guía, comprobando el cálculo de los componentes funcionales ILF, EIF, EI, EO y EQ, su conversión a líneas de código y el esfuerzo resultante.
3. *Conteo e identificación de funciones del Cajero Automático:* Se catalogaron los componentes funcionales de datos y transacciones correspondientes a las operaciones de retiro, depósito, transferencia, consulta y validación de seguridad.
4. *Estimación en COCOMO II y PERT:* Se cargaron los datos en las herramientas de cálculo, determinando el esfuerzo en personas-mes, la duración para 1 y 3 desarrolladores y el costo total en soles.
5. *Comparación y análisis de sensibilidad:* Se contrastaron los resultados de ambos métodos, determinando rangos de clasificación para proyectos pequeños, medianos y grandes, y estableciendo el procedimiento formal para aplicar el ajuste por experiencia.
