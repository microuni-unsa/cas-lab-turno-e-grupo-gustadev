== II. Ejercicios Resueltos

=== Verificación del Caso Resuelto de Referencia

Siguiendo el enunciado de la guía de laboratorio, se analizó el caso resuelto de referencia utilizando las herramientas de cálculo en hoja de cálculo para las metodologías NESMA y COCOMO II @boehm2000.

==== 1. Delimitación de Frontera y Conteo Funcional NESMA
El caso resuelto toma como base el Sistema de Gestión de Pedidos y Clientes. Se identificaron sus componentes funcionales según el estándar ISO/IEC 24570 @iso24570:
- *Archivos Lógicos Internos (ALI):* 6 archivos de complejidad media que representan Clientes, Cuentas, Productos, Pedidos Simples, Pedidos Compuestos y Transacciones. Aportan $6 times 10 = 60 " PF"$.
- *Archivos de Interfaz Externa (AIE):* 2 archivos de complejidad media que representan la Pasarela Bancaria y el Sistema Logístico. Aportan $2 times 7 = 14 " PF"$.
- *Entradas Externas (EE):* 9 procesos elementales de complejidad media para altas, actualizaciones, pedidos y cobros. Aportan $9 times 4 = 36 " PF"$.
- *Salidas Externas (SE):* 4 procesos de complejidad media para facturas electrónicas, reportes comerciales y balances. Aportan $4 times 5 = 20 " PF"$.
- *Consultas Externas (CE):* 3 consultas de complejidad baja para saldos, estados de pedidos y stock. Aportan $3 times 3 = 9 " PF"$.

La suma de estos componentes da el tamaño funcional no ajustado:
$ P F N A = 60 + 14 + 36 + 20 + 9 = 139 " Puntos de Función No Ajustados" $

Al aplicar las 14 características generales del sistema con un grado de influencia total $T D I = 25$ para un entorno transaccional interactivo, se obtiene el factor de ajuste:
$ F A = 0.65 + 0.01 times 25 = 0.90 $
$ P F A = 139 times 0.90 = 125.1 approx 125 " Puntos de Función Ajustados (PFA)" $

#figure(
  image("../../img/excel_cocomo_fp_resuelto.png", width: 85%),
  caption: [Conteo de Puntos de Función en la herramienta COCOMO II para el caso resuelto.],
) <fig-excel-fp-resuelto>

==== 2. Estimación de Esfuerzo con COCOMO II
La herramienta convierte los Puntos de Función a líneas de código fuente considerando un promedio de $60 " SLOC/PF"$ en tecnología Java:
$ "SLOC Totales" = 125 " PF" times 60 " SLOC/PF" = 7\,500 " SLOC" = 7.5 " KSLOC" $

En la sección de esfuerzo de la herramienta se configuran los factores de escala con exponente $E = 1.0426$ y el producto de multiplicadores de esfuerzo en $0.2544$. El cálculo arroja un esfuerzo neto de desarrollo de $112.58 " días-persona"$, equivalente a $5.925 " PM"$ y una tasa de productividad de $7.21 " horas/PF"$.

#figure(
  image("../../img/excel_cocomo_effort_resuelto.png", width: 85%),
  caption: [Consolidado de tamaño y esfuerzo en la herramienta COCOMO II para el caso resuelto.],
) <fig-excel-effort-resuelto>

==== 3. Planificación de Esfuerzo, Plazos y Costos
Tomando los supuestos de la guía para la conversión a recursos:
- *Productividad:* Con una tasa nominal de $10 " PF / persona-mes"$, el esfuerzo total es:
  $ E = frac(125 " PF", 10 " PF / persona-mes") = 12.5 " personas-mes" $
- *Plazos de entrega:*
  $ D_1 = frac(12.5 " personas-mes", 1 " persona") = 12.5 " meses" $
  $ D_3 = frac(12.5 " personas-mes", 3 " personas") = 4.17 " meses" $
- *Costo de mano de obra:* Con una tarifa de S/ 1,500 mensuales por persona, equivalente a S/ 150 por PF:
  $ "Costo" = 12.5 " PM" times "S/ " 1\,500 = 125 " PF" times "S/ " 150 = "S/ " 18\,750 $

==== 4. Estimación con la Herramienta PERT
Para verificar el proyecto mediante un enfoque ágil basado en tareas, se desglosó el sistema en 10 historias de usuario con estimaciones optimista, más probable y pesimista.

#figure(
  image("../../img/excel_pert_estimates_resuelto.png", width: 85%),
  caption: [Estimación probabilística PERT para las 10 historias de usuario del caso resuelto.],
) <fig-excel-pert-resuelto>

El cálculo del promedio ponderado en la herramienta PERT da un total de $112.83 " días-persona"$, valor muy cercano a los $112.58 " días"$ obtenidos con el modelo COCOMO II.

==== 5. Precisiones Metodológicas
1. El caso resuelto corresponde al sistema de pedidos y clientes, independiente del sistema de cajero automático.
2. La experiencia del equipo no reduce el tamaño funcional solicitado por el usuario; su efecto se refleja en las horas requeridas por Punto de Función.
3. Se verifica la relación directa entre la tarifa mensual de S/ 1,500 y el costo de S/ 150 por PF bajo una productividad de 10 PF por persona-mes.
