== III. Resultados: Ejercicios Resueltos

=== Componentes de la plantilla

La plantilla consta de 7 hojas. `Instrucciones` reúne 15 reglas de trabajo; `Ejemplo R1–R6` presenta el caso resuelto y no debe modificarse; `Tablas AR` define las escalas P/I de 1–5, los niveles (Bajo de 1 a 4, Moderado 5–9, Alto 10–16, Crítico 17–25) y las dimensiones; `Catálogo T1–T10` ofrece amenazas locales de referencia (no son códigos oficiales MAGERIT); y `Activos`, `Cruces` y `Análisis` conforman el registro del alumno. Las celdas amarillas son entrada del alumno y las azules son fórmulas (`R=P×I`, `Rres=Pres×Ires`, reducción ordinal `(R−Rres)/R`). El estado `Listo` exige ID único, activo y amenaza registrados, vulnerabilidad, dimensión, controles actuales (`Ninguno` si no existen), P/I enteros de 1–5 y justificación.

=== Activos valorados

#figure(
  image("/l6/img/excel_activos.png", width: 95%),
  caption: [Activos A1–A6 con propietario y valoración C/I/D/A en OnlyOffice. A2 datos concentra C5/I5.],
) <fig-activos>

=== Escalas y catálogo de referencia

#figure(
  image("/l6/img/excel_tablas_a.png", width: 95%),
  caption: [Tablas AR: escalas P (probabilidad a 12 meses) e I (consecuencia más grave).],
) <fig-tablas-a>

#figure(
  image("/l6/img/excel_tablas_b.png", width: 95%),
  caption: [Tablas AR: clasificación Bajo–Crítico, umbral aceptable R≤4 y dimensiones C/I/D/A.],
) <fig-tablas-b>

#figure(
  image("/l6/img/excel_catalogo.png", width: 95%),
  caption: [Catálogo T1–T10 con vulnerabilidad posible y medida posible por amenaza.],
) <fig-catalogo>

=== Ejemplo resuelto de referencia

#figure(
  image("/l6/img/excel_ejemplo.png", width: 95%),
  caption: [Ejemplo R1–R6 resuelto (no se modifica): se observa que R3, con 4×5=20 Crítico, alcanza con controles 1×5=5 Moderado sin reducir la gravedad.],
) <fig-ejemplo>

#figure(
  image("/l6/img/excel_formula.png", width: 95%),
  caption: [Celda azul con fórmula automática (`G6` según umbrales de `Tablas AR`): los niveles se calculan, no se digitan.],
) <fig-formula>

=== Guía de referencia

Se revisó la guía INCIBE de gestión de riesgos @incibe-guia-riesgos y el Libro I de MAGERIT v3 @magerit-v3-libro1, que describe el proceso de gestión de riesgos. Como complemento se consultó el artículo de Mañas sobre PILAR @manas2004-pilar, que distingue el análisis cualitativo por niveles (rápido y útil para relativizar riesgos) del cuantitativo monetario (más preciso si los datos se validan, y capaz de estimar el retorno de las salvaguardas).
