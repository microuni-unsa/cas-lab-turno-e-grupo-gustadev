|||||||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA|||
|---|---|---|---|---|---|---|---|---|
||- -|Aprobación: 2022/03/01 III. RESULTADOS OBTENIDOS Tipo de Función|Formato: entrega considerando 1 y 3 personas. El sistema tiene dos partes principales: Identificando funciones Nesma tenemos:|Descripción|procederá a subir el informe al drive del respectivo laboratorio. compuestos), con validaciones y cobros automáticos. Cantidad|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación Código: GUIA-PRLE-001 d. Revisión final y envío.- Revisar completamente el informe redactado, una vez verificado se 1. Dimensionar la aplicación descrita en el Anexo 1. Calcular el valor en soles y el tiempo de Cajero automático, con operaciones de retirar, depositar, transferir y consultar saldos. Gestión de pedidos, que maneja clientes, cuentas, productos y pedidos (simples y Complejidad|PF por función|Página: 2 Total PF|
||Archivos||Clientes,||6|Media|10|60|
||Lógicos Internos (ALI)|Archivos de|Cuentas, Productos, Pedidos, s, Cajeros, Banco Consorcio,|Transaccione|2|Media|7|14|
||Interfase|Externa (AIE)|sistema bancario externo||||||
||Entradas|Externas (EE)|Retiro, Depósito,, Consulta cliente, Alta|Transferencia saldo, Alta de de producto,|9|Media|4|36|

|||||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA|||
|---|---|---|---|---|---|---|
||Formato:|||Guía de Práctica de Laboratorio / Talleres / Centros de Simulación|||
|Aprobación: 2022/03/01||||Código: GUIA-PRLE-001||Página: 3|
||Creación de pedido, Cobro, tarjeta|Validación de|||||
|Salidas||Comprobant|4|Media|5|20|
|Externas (SE)|pedidos, Reporte de cuentas, Reporte de|e, Reporte de transacciones|||||
|Consultas|Consultar||3|Simple|3|9|
|Externas (CE)|saldo, verificar estado pedido, historial|transacciones|||||
|Fórmula: Perú, 2024).|PFA = PFNA × FA = 139 × 0.90 = 125 PF|Se analizan las 14 características generales del sistema. Por consiguiente los puntos de función ajustados serían:|Esto nos da 60 + 14 + 36 + 20 + 9 = 139 puntos de función no ajustados FA = (Nivel de Influencia × 0.01) + 0.65 = (25 × 0.01) + 0.65 = 0.90|Dado que es un sistema transaccional, con validación online y múltiples actores, estimamos un nivel de influencia total de 25 (valor típico de sistemas interactivos con procesamiento externo). El costo por punto de función depende de la complejidad y tecnología, pero para entornos académicos o de simulación se usa una media de S/ 150 por PF (según tasas de desarrollo en|||
|||Formato:||Guía de Práctica de Laboratorio / Talleres / Centros de Simulación|||
||Aprobación: 2022/03/01|||Código: GUIA-PRLE-001||Página: 4|
||125 PF × S/ 150 = S/ 18,750|Esto genera un costo total estimado: Considerando 1 y 3 personas tenemos:|||||
||Personas 1 persona 3 personas a. Comparación:|12.5 tamaño pequeño, mediano y grande.|Esfuerzo (persona-mes) 12.5 ÷ 3 = 4.2|Duración estimada (meses) ≈ 12.5 meses ≈ 4.2 meses 2. Compare los valores de ambas aplicaciones y determine valores aproximados de PF para un||Costo estimado (S/ 150 por PF) S/ 18,750 S/ 18,750|
||Característica||Sistema del Ejemplo|Sistema del Anexo 1||Comparación|
||Dominio Entradas externas Archivos lógicos internos|7 3|Gestión de clientes y productos|Cajero automático + pedidos + cuentas 9 6||Más amplio y complejo +2 funciones +3 archivos|
||Interfases externas Consultas y salidas|2 4||2 7|Igual|+3 consultas|
||PF ajustados|64 PF b. Clasificación de tamaño según puntos de función||125 PF||~2× mayor tamaño funcional|

||||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA|||
|---|---|---|---|---|---|
||Formato:||Guía de Práctica de Laboratorio / Talleres / Centros de Simulación|||
|Aprobación: 2022/03/01|||Código: GUIA-PRLE-001||Página: 5|
|Categoría de tamaño||Rango típico (PF)||Descripción|Ejemplo práctico|
|Pequeño||1 – 70 PF||Aplicaciones con pocos módulos, sin interfaces externas complejas|Ejemplo.pdf (64 PF)|
|Mediano||71 – 150 PF||Aplicaciones con módulos múltiples, validaciones, algunos actores externos|Anexo 1 (125 PF)|
|Grande|c. Estimación de valores representativos|151 – 300 PF||Sistemas con alta integración, múltiples interfaces, o gestión distribuida|ERP, banca en línea, o facturación compleja|
|Categoría de tamaño||Rango típico (PF)||Descripción|Ejemplo práctico|
|Pequeño||1 – 70 PF||Aplicaciones con pocos módulos, sin interfaces externas complejas|Ejemplo.pdf (64 PF)|
|Mediano||71 – 150 PF||Aplicaciones con módulos múltiples, validaciones, algunos actores externos|Anexo 1 (125 PF)|
|Grande||151 – 300 PF||Sistemas con alta integración, múltiples interfaces, o gestión distribuida|ERP, banca en línea, o facturación compleja|
|||Aprobación: 2022/03/01 para hacerlo. Factor Experiencia del equipo Uso de frameworks o componentes reutilizables Complejidad técnica real Cambios en requerimientos|Formato:|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación Código: GUIA-PRLE-001 3. Modifique valor de PF que debería ajustar con la experiencia y como sería el procedimiento Primero empezaremos con los factores que influyen en el ajuste Efecto Reducción del esfuerzo total Disminuye la cantidad de lógica desarrollada desde cero Aumenta PF si hay integración con servicios externos o seguridad adicional Ajuste dinámico durante el desarrollo|Página: 6 Ejemplo Un equipo con 5 años de experiencia en proyectos similares puede reducir un 10–15% los PF efectivos. Si se reutiliza una plantilla de gestión de pedidos, se puede restar 5–10 PF. Integración con una API bancaria puede sumar 10–15 PF. Aumentos o reducciones del 5–20%, según las revisiones funcionales.|
||● ● ● ● ●|Paso 1: Revisión del conteo inicial|Paso 2: Identificación de factores de productividad Analizar las condiciones reales del proyecto: Herramientas de desarrollo utilizadas.|El procedimiento formal de ajuste (según NESMA e ISO/IEC 24570) es el siguiente: según su nivel de complejidad (simple, medio, complejo). Experiencia del equipo (años, proyectos similares). Nivel de automatización (entornos low-code, librerías reutilizables).|Verificar que las entradas, salidas y archivos lógicos estén correctamente clasificados|

||||||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA||
|---|---|---|---|---|---|---|
||●|Aprobación: 2022/03/01 Valores típicos: FAE = 1.00 → nivel promedio. Paso 4: Recalcular PF finales Condición Equipo experto Equipo estándar Equipo inexperto o con alta complejidad Escenario Equipo experto Equipo promedio Equipo inexperto construya tabla comparativa.|Formato: FAE = 0.85 → equipo muy experimentado. 125 125 125 106 125 144|Paso 3: Definición del factor de ajuste por experiencia (FAE) Se aplica un coeficiente correctivo sobre los PF originales: PF ajustados por experiencia=PF calculados×FAE FAE = 1.15 → equipo con poca experiencia o alta complejidad técnica. PF Calculados PF Ajustados|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación Código: GUIA-PRLE-001 FAE 0.85 1.00 1.15 Teniendo el costo unitario de S/150 por PF, el cambio en costo y duración sería: Costo (S/) S/ 15,900 S/ 18,750 S/ 21,600 4. Realizar el dimensionamiento en COCOMO, wine y COSMIC. Revise, verifique resultados y|Página: 7 PF Ajustados 106 PF 125 PF 144 PF Duración (1 persona) 10.6 meses 12.5 meses 14.4 meses|

||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA||
|---|---|---|
|Formato:|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación||
|Aprobación: 2022/03/01|Código: GUIA-PRLE-001|Página: 8|
|a. Dimensionamiento con COCOMO función convertidos. Formula: E=a×(KLOC)^b Donde: a = 2.94 (constante empírica) b = 1.1 (proyecto semidetallado) Entonces: Resultado COCOMO II ● ● ● ● b. Dimensionamiento con Wine en tipo de proyecto.|El modelo COCOMO II (Constructive Cost Model) estima el esfuerzo (en persona-meses) según el tamaño del software (en miles de líneas de código — KLOC) o puntos de 1 PF ≈ 100 líneas de código en 4GL → 125 PF ≈ 12,500 LOC → 12.5 KLOC E=2.94×(12.5)1.1≈2.94×17.6=51.7 persona-meses Esfuerzo total: 51.7 persona-meses Duracion: D=2.5×E^0.38 =2.5×51.7 x 0.38≈9.7 meses Costo (S/ 150 por PF): S/ 18,750 Productividad: 2.4 PF/persona-mes El modelo Wine (hoja COCOMO simplificada) asume rangos de productividad basados Esfuerzo: E=2.4×(12.5)1.05=2.4×14.4=34.6 persona-meses Duración: D=2.5×(34.6)0.38≈7.9 meses Costo: 34.6 persona-meses × (S/ 1,500 mensual por persona) = S/ 51,900||

||||||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA|||||
|---|---|---|---|---|---|---|---|---|---|
|||Formato:|||Guía de Práctica de Laboratorio / Talleres / Centros de Simulación|||||
||Aprobación: 2022/03/01||||Código: GUIA-PRLE-001||||Página: 9|
|c.||Dimensionamiento con COSMIC-FFP software y su entorno. Según comparaciones empíricas: d. Tabla comparativa final|Esfuerzo: E=160/10=16 persona-meses Duración: 16 / 3 personas ≈ 5.3 meses Costo (S/ 150/PF): 160 × 150 = S/ 24,000|tamaño funcional con movimientos de datos (Entry, Exit, Read, Write). 1 CFP ≈ 0.8 PF → 125 PF × 0.8 ≈ 100 CFP equivalentes|El método COSMIC (Common Software Measurement International Consortium) mide el Un Punto COSMIC (CFP) equivale a una unidad de movimiento de datos entre el|||||
||Método||Unidades|Tamaño (PF/CFP)||Esfuerzo (persona-m es)|Duración estimada||Costo estimado (S/150 por|
||||||||||PF)|
||NESMA|PF||125|12.5||12.5 meses (1 persona) / 4.2 (3 personas)|18,750||
||COCOMO II||PF → KLOC|125 PF ≈ 12.5 KLOC|51.7||9.7 meses|18,750||
||Wine||PF → KLOC|12.5 KLOC|34.6||7.9 meses|51,900||
||(Simplificad o)|||||||||
||COSMIC-FF|CFP||160|16||5.3 meses|24,000||
||P|||||||||

||UNIVERSIDAD NACIONAL DE SAN AGUSTIN FACULTAD DE INGENIERÍA DE PRODUCCIÓN Y SERVICIOS ESCUELA PROFESIONAL DE INGENIERÍA DE SISTEMA||
|---|---|---|
|Formato:|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación||
|Aprobación: 2022/03/01|Código: GUIA-PRLE-001|Página: 10|
|IV. ANALISIS DE RESULTADOS En nuestros resultados tenemos 1. Aplicación del anexo 1: ● ajustados. ● ● personas. ● 2. Comparación y valores aproximados de PF: ● ● validaciones múltiples. ● 3. Modificando el valor del PF ● ● ● 4. Dimensionamiento en COCOMO ● ● ● ●|El tamaño funcional de la aplicación es de aproximadamente 125 puntos de función El costo estimado del desarrollo es de S/ 18,750, suponiendo S/ 150 por PF. El tiempo de desarrollo se estima en 12.5 meses con una persona, o 4 meses con tres Este análisis puede afinarse ajustando el factor de productividad o usando COCOMO II con parámetros reales del entorno. El sistema pequeño (Ejemplo.pdf) tiene un tamaño funcional de 64 PF, representando una aplicación básica de registro y consulta. El sistema mediano (Anexo 1) alcanza 125 PF, con mayor complejidad funcional y Un sistema grande (por ejemplo, una plataforma bancaria completa) rondaría 250 PF o más, requiriendo equipos mayores y más meses de trabajo. El ajuste de Puntos de Función permite refinar la estimación inicial con base en la experiencia y condiciones reales de desarrollo. Un equipo experimentado puede reducir hasta un 15–20% el tamaño funcional efectivo, logrando mayor productividad y menor costo. Un procedimiento iterativo (reestimación por fase o sprint) mejora la precisión del dimensionamiento temprano y reduce desviaciones en costo y tiempo. NESMA ofrece una estimación funcional directa y sencilla para etapas tempranas. COCOMO II y Wine agregan una visión de esfuerzo y cronograma basada en líneas de código, siendo más útiles en la planificación. COSMIC-FFP brinda una visión moderna y más precisa para sistemas con alto intercambio de datos (como el cajero automático). En general, el sistema del Anexo 1 se ubica en un rango mediano de complejidad, con esfuerzo entre 16 y 52 persona-meses según el modelo.||
|Formato:|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación||
|Aprobación: 2022/03/01|Código: GUIA-PRLE-001|Página: 11|
|V. SOLUCIÓN DEL CUESTIONARIO antes de iniciar el desarrollo, lo que ayuda a: ● ● ● ● significativos. 2. Indique la ventaja de usar la metodología NESMA varias ventajas clave: ● ● ● ● proyecto. Su confiabilidad proviene de que: ● sus fórmulas y constantes. ● ●|1. ¿Qué resuelve conocer dimensionamiento temprano del proceso de construcción de software? Conocer el dimensionamiento temprano permite estimar el tamaño funcional del software Calcular el esfuerzo, costo y tiempo de entrega del proyecto. Asignar recursos humanos y técnicos adecuados. Controlar y planificar el cronograma, comparando avances reales frente a estimaciones. Evaluar la viabilidad económica del proyecto antes de comprometer recursos La metodología NESMA (Netherlands Software Metrics Association), derivada de IFPUG, ofrece Permite medir el tamaño funcional del software de forma objetiva y reproducible, independientemente de la tecnología o lenguaje de programación. Facilita comparaciones entre proyectos, dado que se basa en unidades funcionales (entradas, salidas, archivos y consultas). Simplifica la estimación de costos y tiempos desde etapas tempranas del ciclo de vida. Está alineada con el estándar internacional ISO/IEC 24570, lo que garantiza consistencia con otros métodos de medición funcional. 3. ¿Es confiable el dimensionamiento COCOMO, porque? Sí, COCOMO es confiable, siempre que se utilicen parámetros calibrados y datos reales del Se basa en datos empíricos recopilados de cientos de proyectos reales, lo que respalda Permite ajustar el modelo según el tipo de proyecto (orgánico, semidetallado o embebido) y factores de costo (experiencia, complejidad, calidad, etc.). Proporciona estimaciones consistentes del esfuerzo, tiempo y costo cuando el tamaño (en KLOC o PF) está bien definido.||
|Formato:|Guía de Práctica de Laboratorio / Talleres / Centros de Simulación||
|Aprobación: 2022/03/01|Código: GUIA-PRLE-001|Página: 12|
|VI. CONCLUSIONES toma de decisiones en gestión de proyectos. obtener métricas comparables entre desarrollos.|Como conclusiones tenemos que el dimensionamiento temprano reduce la incertidumbre inicial y mejora la Por otra parte, NESMA es ideal para proyectos donde se necesita estimar sin depender del código fuente y En cuanto a COCOMO es bastante confiable, sin embargo, su precisión depende de la calidad de los datos de entrada: si se subestima el tamaño funcional o los factores de ajuste, los resultados serán menos confiables. RETROALIMENTACIÓN GENERAL REFERENCIAS Y BIBLIOGRAFÍA||
|[1]|[https://www.geeksforgeeks.org/software-engineering/software-engineering-cocomo-ii-model/||](https://www.geeksforgeeks.org/software-engineering/software-engineering-cocomo-ii-model/||)
|[2]|[https://cosmic-sizing.org/wp-content/uploads/2020/04/6-COSMIC-4.0.1-Gui%CC%81a-FSM-temprano-o-ra%C||](https://cosmic-sizing.org/wp-content/uploads/2020/04/6-COSMIC-4.0.1-Gui%CC%81a-FSM-temprano-o-ra%C||)
|C%81pido.pdf|||
|[3] [https://gitlab.winehq.org/wine/wine/-/wikis/home|||](https://gitlab.winehq.org/wine/wine/-/wikis/home|||)
