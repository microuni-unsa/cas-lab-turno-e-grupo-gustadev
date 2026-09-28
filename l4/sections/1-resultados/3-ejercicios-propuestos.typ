#import "/components/code-block.typ": code-block

== III. Ejercicios Propuestos

=== 1. Dimensionamiento del Cajero Automático del Anexo 1

A partir de la especificación funcional del Anexo 1 de la guía, se estableció la frontera del Cajero Automático ATM y se cuantificó su tamaño funcional mediante la metodología NESMA @iso24570 @ifpug2010.

==== a) Delimitación de Frontera y Modelo de Casos de Uso
El terminal ATM opera como un sistema transaccional interactivo. Su frontera interna abarca la interfaz de usuario, la validación local y los controladores de dispensador, tolva de depósito e impresora. Las entidades externas corresponden al Banco Emisor y a la Red Consorcio, tal como muestra la @fig-anexo-atm.

#figure(
  image("../../img/anexo1_diagrama.png", width: 55%),
  caption: [Diagrama de casos de uso del Cajero Automático según el Anexo 1 de la guía.],
) <fig-anexo-atm>

==== b) Inventario y Conteo Funcional NESMA
Se catalogaron y valoraron los componentes funcionales:

1. *Archivos Lógicos Internos (ALI):*
  - Registro de auditoría y transacciones locales: complejidad media, aporta 7 PF.
  - Control de efectivo y gavetas: complejidad baja, aporta 7 PF.
  - Subtotal ALI: 2 archivos, 14 PF.

2. *Archivos de Interfaz Externa (AIE):*
  - Datos de cuentas y tarjetas del Banco Emisor: complejidad media, aporta 7 PF.
  - Directorio de enrutamiento de la Red Consorcio: complejidad media, aporta 7 PF.
  - Subtotal AIE: 2 archivos, 14 PF.

3. *Entradas Externas (EE):*
  - Autenticación de tarjeta y PIN: complejidad media, aporta 4 PF.
  - Retiro de efectivo: complejidad media, aporta 4 PF.
  - Depósito de efectivo: complejidad media, aporta 4 PF.
  - Transferencia entre cuentas: complejidad media, aporta 4 PF.
  - Subtotal EE: 4 procesos, 16 PF.

4. *Salidas Externas (SE):*
  - Emisión de comprobante impreso: complejidad media, aporta 5 PF.
  - Dispensación física de billetes: complejidad baja, aporta 4 PF.
  - Subtotal SE: 2 procesos, 9 PF.

5. *Consultas Externas (CE):*
  - Consulta de saldo en pantalla: complejidad baja, aporta 3 PF.
  - Consulta de últimos movimientos: complejidad media, aporta 4 PF.
  - Subtotal CE: 2 procesos, 7 PF.

La @tabla-conteo-nesma-atm resume el conteo de Puntos de Función No Ajustados PFNA.

#figure(
  table(
    columns: (3fr, 1.2fr, 1.5fr, 1.2fr, 1.5fr),
    align: (left, center, center, center, center),
    table.header([Categoría Funcional], [Cantidad], [Complejidad], [Peso Unitario], [Total PF]),
    [Archivos Lógicos Internos (ALI)], [2], [Media y Baja], [7], [14],
    [Archivos de Interfaz Externa (AIE)], [2], [Media], [7], [14],
    [Entradas Externas (EE)], [4], [Media], [4], [16],
    [Salidas Externas (SE)], [2], [Media y Baja], [5 y 4], [9],
    [Consultas Externas (CE)], [2], [Baja y Media], [3 y 4], [7],
    table.cell(colspan: 4, align: right)[*Total Puntos de Función No Ajustados (PFNA)*], [*60*],
  ),
  caption: [Conteo funcional del Cajero Automático mediante metodología NESMA.],
) <tabla-conteo-nesma-atm>

La @fig-excel-cocomo-fp muestra estos valores cargados en la herramienta de cálculo COCOMO II, totalizando 60 Puntos de Función.

#figure(
  image("../../img/excel_cocomo_fp_atm.png", width: 95%),
  caption: [Conteo de Puntos de Función en la herramienta COCOMO II para el Cajero Automático.],
) <fig-excel-cocomo-fp>

==== c) Factor de Ajuste y Puntos de Función Ajustados
Evaluando las 14 características generales del sistema con un grado de influencia total $T D I = 30$, se obtiene el factor de ajuste:

$ F A = 0.65 + 0.01 times 30 = 0.95 $
$ P F A = 60 times 0.95 = 57 " PFA" $

Para fines de planificación se toma como referencia el valor base de 60 PF.

==== d) Estimación de Esfuerzo, Plazo y Costo
Aplicando las tasas definidas en la guía de laboratorio:
- Tarifa de desarrollo: S/ 150 por PF.
- Productividad nominal: 10 PF por persona-mes.

$ "Costo Total" = 60 " PF" times "S/ " 150 = "S/ " 9,000 $
$ "Esfuerzo Total" = frac(60 " PF", 10 " PF / persona-mes") = 6.0 " personas-mes" $

Evaluando los plazos de entrega para los dos escenarios de equipo:
- *Escenario de 1 persona:*
  $ "Duración" = frac(6.0 " personas-mes", 1 " persona") = 6.0 " meses" $
- *Escenario de 3 personas:*
  $ "Duración ideal" = frac(6.0 " personas-mes", 3 " personas") = 2.0 " meses" $
  Considerando un 15% adicional por coordinación del equipo y pruebas integradas, el plazo estimado es de 2.3 meses. El costo de mano de obra se mantiene en S/ 9,000.

En la herramienta COCOMO II (@fig-excel-cocomo-effort), la conversión a código fuente da 3,600 SLOC. Con los factores calibrados, el modelo estima un esfuerzo neto de 2.84 personas-mes, equivalente a 54.04 días de desarrollo y 432.3 horas de trabajo.

#figure(
  image("../../img/excel_cocomo_effort_atm.png", width: 88%),
  caption: [Estimación de esfuerzo y líneas de código en la herramienta COCOMO II para el Cajero Automático.],
) <fig-excel-cocomo-effort>

=== 2. Comparación entre Aplicaciones y Clasificación de Tamaños

Para responder al Ejercicio Propuesto 2, se compararon los resultados entre la herramienta COCOMO II y la herramienta PERT, como sintetiza la @tabla-comparativa-excel.

==== a) Comparativa de Herramientas y Modelos

#figure(
  table(
    columns: (2.2fr, 2.8fr, 2.8fr),
    align: (left, left, left),
    table.header([Criterio de Evaluación], [Herramienta COCOMO II], [Herramienta PERT]),
    [Tipo de Enfoque], [Paramétrico descendente], [Probabilístico ascendente],
    [Unidad de Entrada], [Puntos de Función y KSLOC], [Historias de usuario y tareas],
    [Manejo de Incertidumbre], [Ajuste por factores empíricos], [Estimación de tres valores O, M y P],
    [Métricas Clave], [Personas-mes PM, duración y horas/PF], [Días-persona y Man-Days totales],
    [Proyecto Resuelto],
    [Gestión de pedidos: 125 PF, 5.93 PM, 112.58 días],
    [Gestión de pedidos: 10 historias, 112.83 Man-Days],

    [Cajero Automático ATM], [60 PF, 3.6 KLOC, 2.84 PM, 54.04 Man-Days], [8 historias de usuario, 46.67 Man-Days],
  ),
  caption: [Comparación metodológica entre las herramientas de estimación.],
) <tabla-comparativa-excel>

La @fig-excel-pert-estimates presenta el desglose de las ocho historias de usuario del Cajero Automático en la herramienta PERT bajo los tres escenarios de tiempo, obteniendo un total de 46.67 Man-Days.

#figure(
  image("../../img/excel_pert_estimates_atm.png", width: 95%),
  caption: [Estimación probabilística PERT para las 8 historias de usuario del Cajero Automático.],
) <fig-excel-pert-estimates>

- *COCOMO II* es útil en etapas tempranas de viabilidad y contratación, permitiendo cotizar el proyecto desde la especificación funcional general.
- *PERT* resulta útil en la planificación de sprints y asignación operativa de tareas concretas.

==== b) Clasificación Práctica por Tamaño Funcional
A partir de los proyectos analizados y la literatura estándar @pressman2020 @sommerville2016, se establecen los siguientes rangos de clasificación:

- *Pequeño (1 a 70 PF):* Aplicaciones modulares con lógica directa y pocas interfaces externas. Ejemplo: el Cajero Automático del Anexo 1 con 60 PF y entre 46 y 54 días de esfuerzo. Plazo típico de 1 a 6 meses.
- *Mediano (71 a 150 PF):* Aplicaciones con múltiples roles de usuario y transacciones comerciales estructuradas. Ejemplo: el Sistema de Gestión de Pedidos y Clientes del caso resuelto con 125 PF y 112.8 Man-Days. Plazo típico de 4 a 12 meses.
- *Grande (151 a 300+ PF):* Plataformas corporativas de gran escala como sistemas bancarios integrales de back-office o ERPs. Plazo típico superior a 12 meses.

=== 3. Procedimiento para el Ajuste por Experiencia

Un error común consiste en reducir los Puntos de Función argumentando que el equipo tiene mayor experiencia. Según el estándar ISO/IEC 24570, los Puntos de Función miden la funcionalidad que el usuario recibe y no dependen de quién construya el software.

La experiencia modifica la productividad del equipo en horas por PF o los multiplicadores de esfuerzo del modelo de costo.

==== Procedimiento Metodológico
1. *Fijar la línea base funcional:* Realizar el conteo de Puntos de Función PFNA de forma objetiva y mantenerlo fijo mientras los requerimientos no cambien.
2. *Calibrar los inductores de personal en COCOMO II:*
  - Factor de Capacidad del Personal PERS: varía desde 1.26 para un equipo novato hasta 0.83 para un equipo de alta capacidad.
  - Factor de Experiencia en Plataforma PREX: varía desde 1.22 para baja experiencia hasta 0.87 para alta experiencia.
3. *Calcular el Factor de Ajuste por Experiencia (FAE):*
  Si se aplica una corrección directa sobre el esfuerzo estimado:
  $ "Esfuerzo Ajustado" = "Esfuerzo Base" times F A E $
  Donde:
  - $F A E = 0.85$: equipo senior con experiencia previa en proyectos bancarios, reduce el esfuerzo en 15%.
  - $F A E = 1.00$: equipo con competencia estándar promedio.
  - $F A E = 1.15$: equipo junior o con curva de aprendizaje alta.

La @tabla-escenarios-experiencia detalla los resultados económicos y de tiempo para el Cajero Automático de 60 PF.

#figure(
  table(
    columns: (2.5fr, 1.2fr, 1.8fr, 1.8fr, 1.8fr),
    align: (left, center, center, center, center),
    table.header([Perfil del Equipo], [FAE], [Esfuerzo PM], [Plazo para 3 personas], [Costo Estimado]),
    [Equipo Experimentado], [0.85], [5.10 PM], [1.70 meses], [S/ 7,650],
    [Equipo Estándar], [1.00], [6.00 PM], [2.00 meses], [S/ 9,000],
    [Equipo con Poca Experiencia], [1.15], [6.90 PM], [2.30 meses], [S/ 10,350],
  ),
  caption: [Impacto del factor de experiencia en el dimensionamiento del Cajero Automático.],
) <tabla-escenarios-experiencia>

=== 4. Comparativa de Métodos: NESMA, COCOMO II y COSMIC

==== a) Dimensionamiento con COSMIC (ISO/IEC 19761)
El método COSMIC @iso19761 @cosmic2020 mide el tamaño funcional identificando movimientos individuales de datos: Entrada Entry, Salida Exit, Lectura Read y Escritura Write.

Para las operaciones del Cajero Automático:
- Autenticación PIN: Entry, Read y Exit, total 3 CFP.
- Retiro de Efectivo: Entry, Read, Write y 2 Exit, total 5 CFP.
- Depósito de Efectivo: Entry, Read, Write y Exit, total 4 CFP.
- Transferencia: Entry, 2 Read, 2 Write y Exit, total 6 CFP.
- Consulta de Saldo: Entry, Read y Exit, total 3 CFP.
- Consulta de Movimientos: Entry, Read y Exit, total 3 CFP.
- Emisión de Comprobante: Read y Exit, total 2 CFP.

$ "Total COSMIC" = 3 + 5 + 4 + 6 + 3 + 3 + 2 = 26 " CFP" $

Bajo una productividad nominal de 5 CFP por persona-mes en sistemas transaccionales, el esfuerzo es de $26 / 5 = 5.2$ personas-mes, lo que guarda estrecha concordancia con el resultado obtenido en NESMA.

==== b) Dimensionamiento con Modelo Wine
En la literatura clásica de métricas de software de la década de 1990, esta denominación hacía referencia a hojas de cálculo de estimación rápida basadas en las fórmulas básicas de Boehm. Dicho modelo empírico simplificado emplea la ecuación:

$ E = 2.4 times (K L O C)^(1.05) $

Para los 3.6 KLOC del Cajero Automático, esta fórmula arroja:
$ E = 2.4 times (3.6)^(1.05) approx 9.2 " personas-mes" $

==== c) Tabla Comparativa Consolidada
La @tabla-resumen-metodologias sintetiza los resultados obtenidos por los diferentes métodos para el Cajero Automático.

#figure(
  table(
    columns: (2fr, 1.5fr, 1.8fr, 1.8fr, 2fr),
    align: (left, center, center, center, left),
    table.header([Metodología], [Unidad de Tamaño], [Tamaño Calculado], [Esfuerzo PM], [Plazo para 3 personas]),
    [NESMA (ISO/IEC 24570)], [PF], [60 PF], [6.00 PM], [2.00 meses],
    [COCOMO II], [KLOC Java], [3.60 KLOC], [2.84 PM], [1.50 meses],
    [COSMIC (ISO/IEC 19761)], [CFP], [26 CFP], [5.20 PM], [1.73 meses],
    [Modelo Simplificado], [KLOC], [3.60 KLOC], [9.20 PM], [3.07 meses],
  ),
  caption: [Tabla comparativa consolidada de metodologías de dimensionamiento para el Cajero Automático.],
) <tabla-resumen-metodologias>
