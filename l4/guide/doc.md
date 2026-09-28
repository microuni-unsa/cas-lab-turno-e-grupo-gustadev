# Práctica 04: Dimensionamiento de Software

En las prácticas anteriores definimos qué debe hacer el sistema, cómo fluye el trabajo y cómo se organiza la solución. Esta semana utilizamos esos artefactos como evidencia para estimar cuánto software habrá que construir. El tamaño funcional se expresa en puntos de función o, con COSMIC, en puntos COSMIC; el esfuerzo, el plazo y el costo requieren supuestos adicionales. La estimación se revisa cuando se aclaran los requisitos.

# Infografía de la secuencia de trabajo

|1 Requerimientos|2 Procesos|3 Diseño|4 Estimación|
|---|---|---|---|
|Funciones, datos y reglas|Pasos y eventos; entradas y|Componentes y relaciones|Se cuentan funciones y se|
|del usuario. ¿Qué debe hacer?|salidas. ¿Cuándo ocurre?|UML. ¿Cómo se organiza?|estiman esfuerzo, plazo y costo. ¿Cuánto implica?|

Hilo conductor: caso de uso de transferencia → pasos del proceso → entidades y componentes → funciones identificables → estimación documentada. Cada flecha exige revisar que la misma funcionalidad aparece de manera coherente en los artefactos.

# Qué significa dimensionar

Dimensionar es estimar el tamaño del software a partir de las funciones que el usuario recibe. No equivale a contar pantallas, clases o líneas de código. Dos implementaciones técnicas distintas pueden prestar las mismas funciones. La medida funcional sirve como base de comparación; para pasar a horas o soles hace falta una tasa de productividad, tarifas y supuestos de calendario.

# Cómo leer los métodos de la guía

|Método|Qué aporta|Cuidado al explicarlo|
|---|---|---|
|NESMA / puntos de función|Cuenta funciones de datos y transacciones con las reglas del método elegido.|Definir frontera, tipos y complejidad; registrar cada clasificación.|
|COSMIC|Mide movimientos de datos de entrada, salida, lectura y escritura en procesos funcionales.|Su unidad es CFP; no convertir directamente desde PF de NESMA.|
|COCOMO II|Estima esfuerzo y plazo mediante un modelo calibrado y factores del proyecto.|Requiere tamaño de entrada y parámetros; una hoja de cálculo no sustituye su justificación.|
|Wine|El término aparece sin identificación suficiente en el enunciado.|Pedir nombre exacto, versión y herramienta antes de comparar cifras.|

# Ejemplo para explicarlo en voz alta

En el cajero del anexo, “retirar efectivo” es una función solicitada. El proceso muestra validación de tarjeta y clave, consulta de saldo, autorización, actualización y comprobante. El diseño identifica la interacción con banco y consorcio. Para medir, fijamos primero la frontera del sistema y determinamos qué transacciones y datos entran en el conteo: no basta contar cinco óvalos del diagrama de casos de uso. Tampoco se debe contar dos veces una validación compartida sin aplicar las reglas del método. Ejemplo puramente didáctico de conversión: si el conteo documentado fuera 80 PF y la productividad histórica comprobada fuera 12 horas de trabajo por PF, el esfuerzo sería 960 horas de trabajo. Con 160 horas disponibles por persona al mes, eso representa 6 meses persona. Una persona requeriría al menos 6 meses y tres, un mínimo aritmético de 2 meses; coordinación, dependencias y pruebas pueden alargar ambos plazos. Si la tarifa fuera S/ 45 por hora, el costo directo de mano de obra sería S/ 43 200. Estas cifras ilustran el cálculo; no son resultados del anexo.

# Secuencia sugerida para el laboratorio

1. Delimitar el sistema y seleccionar uno de los dos casos del anexo: cajero o gestión de pedidos. Son aplicaciones diferentes.
2. Recuperar requisitos y diagramas; completar funciones, datos, entradas y salidas que falten.
3. Elegir un método de tamaño, listar cada función, clasificarla y conservar la hoja de conteo.
4. Explicar los supuestos de productividad, jornada, tarifa y disponibilidad; calcular esfuerzo, plazo para 1 y 3 personas, y costo en soles.
5. Comparar métodos solo si se cuenta con insumos suficientes, indicando unidades y supuestos; interpretar diferencias y límites.
# Precisiones para evitar confusiones

El enunciado menciona COSMIC en el marco teórico, pero el ejercicio resuelto indica NESMA y los propuestos agregan COCOMO y “wine”. También remite a Ejemplo.doc, que no está incluido en el PDF. La agenda menciona instalación de herramienta, pero no define con claridad una herramienta para cada método. Conviene presentar la actividad como una comparación de estimaciones, precisar con los alumnos qué método se exige y no fabricar resultados numéricos sin el documento de ejemplo o sin supuestos explícitos. El encabezado interno dice “Sistemas Distribuidos” aunque la portada dice “Calidad de Software”: esto parece un error de edición de la guía.

# Cierre que puede comunicar a los estudiantes

“Un buen cálculo no comienza con una fórmula: comienza con requisitos verificables, una frontera clara y funciones sin duplicaciones. La cifra final debe poder reconstruirse desde nuestros artefactos y sus supuestos. Medir tamaño ayuda a planificar pruebas, personas y tiempo, y a discutir si el compromiso con el cliente es realista.” Fuente de trabajo: Guía de laboratorio 4, Dimensionamiento: Puntos de Función, documento proporcionado por el docente, pp. 1–5. Los valores del ejemplo son supuestos pedagógicos.

# Observaciones sobre la solución del semestre anterior

El informe calificado permite reconocer la estructura solicitada, pero su calificación no convierte cada cifra o supuesto en una regla de medición. Use estas observaciones para enseñar a verificar resultados, no para reproducir sin revisión sus números.

|Fragmento del informe|Observación verificable|Qué deben hacer los alumnos|
|---|---|---|
|Conteo de 6 ALI y 139 PF no ajustados|La lista textual de ALI contiene 7 nombres; además une cajero y pedidos, que el anexo presenta como dos descripciones independientes.|Elegir la aplicación y su frontera; listar cada grupo lógico una sola vez y justificar su pertenencia.|
|9 EE y 3 CE|“Consultar saldo” figura como entrada y consulta; “validación de tarjeta” puede ser paso interno o interacción según la frontera.|Clasificar cada proceso elemental según entradas, salidas y lógica; evitar doble conteo.|
|FA = 0,90 y 125 PF|El cálculo 139 × 0,90 = 125,1 PF; el nivel 25 se declara sin detallar las 14 valoraciones.|Mostrar cada calificación del ajuste si se usa; indicar redondeo y versión de método. No confundir el factor con la experiencia del equipo.|
|125 PF; 12,5 persona meses; S/ 18 750|12,5 implica asumir 10 PF por persona mes, dato no declarado; S/ 150 por PF es una tarifa supuesta, sin fuente comprobable en el informe.|Separar tamaño, productividad, esfuerzo y tarifa. Registrar y justificar cada hipótesis.|
|3 personas: “esfuerzo” 4,2|12,5 persona meses sigue siendo el esfuerzo total; 4,17 meses es el plazo ideal si tres personas trabajan a tiempo completo y sin pérdidas.|Usar esfuerzo total E y duración D = E / personas disponibles, como escenario simplificado.|
|Experiencia “reduce PF”|La experiencia, reutilización y herramientas afectan principalmente la productividad y el esfuerzo; no cambian por sí solas la funcionalidad entregada.|Recontar PF únicamente si cambia el alcance funcional; variar horas/PF o PF/persona mes para escenarios de equipo.|
|COCOMO II: 51,7 y 9,7 meses|Con los supuestos escritos, 2,94 × 12,5^1,1 ≈ 47,3 persona meses; 2,5 × 51,7^0,38 ≈ 11,2 meses. Faltan factores y escala propios de una aplicación completa de COCOMO II.|Escribir fórmula, parámetros, fuente de conversión PF a LOC y recalcular. Llamar al resultado “ejemplo simplificado” si se omiten factores.|
|Wine: 34,6 y 7,9 meses|El informe no identifica un método de estimación llamado Wine ni la fuente de sus coeficientes; cita WineHQ, software distinto. La fórmula escrita da ≈34,0 persona meses y ≈9,5 meses.|Solicitar especificación de la herramienta y sus parámetros antes de exigir esta comparación.|
|COSMIC: 100 / 160 CFP; 16 persona meses|Convierte 125 PF con factor 0,8 para obtener 100 CFP, después usa 160 CFP sin conteo. La relación E = 160/10 presupone productividad no declarada y el costo multiplica CFP por una tarifa en PF.|Contar movimientos Entry, Exit, Read y Write por proceso funcional. Mantener CFP y tarifas compatibles, sin conversión automática PF↔CFP.|

# Procedimiento reproducible para entregar la práctica

## Preparar el alcance

Separar cajero de gestión de pedidos. Dibujar la frontera, actores externos y funcionalidades. Si se estudian ambas aplicaciones, entregar dos conteos y luego compararlos.

## Construir el inventario

Por cada requisito, registrar identificador, descripción, datos mantenidos o consultados, flujo y pantalla o caso de uso asociado. Resolver ambigüedades antes de contar.

## Contar con NESMA

Clasificar ALI, AIE, EE, SE y CE con las reglas del manual y su nivel de complejidad. Presentar cantidad × peso, suma PF no ajustados y, si corresponde al esquema elegido, factor de ajuste documentado. Las cifras 139 y 125 del informe son hipótesis de trabajo, no la respuesta obligatoria.

## Convertir tamaño en planificación

Definir productividad histórica p en PF/persona mes; E = PF/p. Para n personas plenamente disponibles, D ideal = E/n. Definir costo por persona mes y costo = E × tarifa; añadir costos no laborales cuando corresponda. Mostrar escenarios con 1 y 3 personas.

## Comparar aplicaciones y escenarios

Para pequeña, mediana y grande, declarar rangos locales de referencia y fundamento; los umbrales 1–70, 71–150 y 151–300 del informe son ilustrativos, no categorías universales. Hacer análisis de sensibilidad variando productividad o alcance.

## Aplicar métodos complementarios

Si se exige COSMIC, hacer su propio conteo de movimientos de datos y expresar CFP. Si se exige COCOMO II, documentar conversión de tamaño y todos los parámetros usados, con unidades. Aclarar primero qué significa “wine” en el curso.

## Redactar el informe

Objetivo; frontera y supuestos; procedimiento de conteo; resultados tabulados; verificación aritmética; análisis de diferencias; límites; conclusiones; fuentes y hojas de cálculo. Cada valor de la tabla comparativa debe rastrearse hasta una regla, dato o supuesto.

# Ejemplo corregido para explicar las unidades

Solo como ejercicio aritmético, tome 125 PF, productividad asumida de 10 PF/persona mes y tarifa de S/ 1 500/persona mes: E = 125/10 = 12,5 persona meses; D ideal con 1 persona = 12,5 meses y con 3 personas = 12,5/3 ≈ 4,17 meses; costo de mano de obra = 12,5 × 1 500 = S/ 18 750 en ambos escenarios. Si la tarifa es S/ 150/PF, coincide numéricamente porque 1 persona mes produce 10 PF; explique esa equivalencia y no la presente como dato observado. El costo final puede variar por coordinación y otros gastos. Fuentes examinadas: enunciado de la práctica 04, pp. 1–5, e informe CONT2 2025B 1704254 PRAC04 MEJOR NOTA, pp. 1–12. Los reparos se basan en los datos y fórmulas visibles de estos documentos; no se contó de nuevo la aplicación porque los requisitos del anexo no detallan todas las transacciones y archivos necesarios.
