== III (cont.). Resultados: Ejercicios Propuestos

=== Escenarios implementados (R1–R8)

#figure(
  image("/l6/img/excel_cruces.png", width: 95%),
  caption: [Cruces R1–R8: 2 críticos, 4 altos y 2 moderados. R3 (4×5=20) y R1 (4×5=20) son los más graves.],
) <fig-cruces>

Se seleccionaron únicamente combinaciones pertinentes (en lugar de todas las posibles): ransomware, phishing, alteración de notas, falla eléctrica, borrado, divulgación, falla de servidor y error de actualización. Cada escenario justifica P en el horizonte de 12 meses e I según la consecuencia más grave aplicable de `Tablas AR`.

=== Tratamiento y residual previsto

#figure(
  image("/l6/img/excel_tratamiento.png", width: 95%),
  caption: [Tratamiento Mitigar en los 8 casos; residual PREVISTO con `Supuesto didáctico`. R3 y R6 mantienen I=5: bajar P no baja la gravedad.],
) <fig-tratamiento>

En consecuencia, R1, R2, R3 y R6 (residual 5–6, superior a 4) requieren `Nuevo tratamiento`, mientras que R4, R5, R7 y R8 (residual 3–4, dentro del umbral) pasan a `Solicitar aceptación`, lo que de todos modos exige aprobación documentada.

=== Pirani (herramienta ejecutada)

Se adoptó Pirani @pirani-app como herramienta del laboratorio. El portal PILAR del CCN-CERT resultó inaccesible durante la práctica y se documenta como limitación; además, la matriz docente de 5×5 de la plantilla constituye una simplificación y no reproduce el algoritmo completo de PILAR.

Se creó el proceso `Gestión Académica L6` (Missional/Macroprocess) y los 8 riesgos R1–R8 espejo del Excel, con Impacto (Minor→Catastrophic) y Frecuencia (Remote→Very likely) equivalentes a I/P 1–5:

#figure(
  image("/l6/img/pirani_proceso.png", width: 95%),
  caption: [Proceso `Gestión Académica L6` creado en Pirani (Plan Free).],
) <fig-pirani-proceso>

#figure(
  image("/l6/img/pirani_riesgos.png", width: 95%),
  caption: [Riesgos ORM-1 a ORM-8 en Pirani: 3 Critical (R1, R3, R6), 3 High (R2, R4, R5) y 2 Medium (R7, R8), consistente con el Excel (2 críticos, 4 altos, 2 moderados).],
) <fig-pirani-riesgos>

#figure(
  image("/l6/img/pirani_dashboard.png", width: 95%),
  caption: [Dashboard Pirani: 1 proceso, 8 riesgos y mapa de calor con la concentración en zona alta/crítica. El residual se gestiona en el Excel (4 Bajos para aceptación, 4 Moderados con nuevo tratamiento).],
) <fig-pirani-dashboard>

#figure(
  image("/l6/img/pirani_detalle_r3.png", width: 95%),
  caption: [Detalle R3 en Pirani: riesgo inherente Critical (Impact Catastrophic 100% / Likelihood Likely 80%), equivalente a P4×I5=20 del Excel.],
) <fig-pirani-detalle>

#figure(
  image("/l6/img/pirani_reportes.png", width: 95%),
  caption: [Reportes Pirani: mapa de calor y perfil por proceso con fecha 19/10/2026. Los reportes avanzados (perfil organizacional, robustez de controles) requieren plan Basic; el análisis residual se sustenta en el Excel.],
) <fig-pirani-reportes>

=== Matriz RMAT vs Pirani

La práctica no precisa qué producto RMAT debe compararse, por lo que la comparación se plantea por criterios: inventario de activos, escalas P/I, catálogo de amenazas, controles o salvaguardas y reportes. Pirani cubre registro de procesos y riesgos, matriz de calor, planes de tratamiento y reportes web en plan Free, según se evidencia en las capturas. Una comparación exhaustiva exigiría identificar producto y versión del RMAT de referencia, de modo que aquí se documentan los criterios y el alcance efectivamente verificado en Pirani.
