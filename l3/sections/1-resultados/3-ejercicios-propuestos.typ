== III. Ejercicios Propuestos

=== a) Ejercicio Propuesto 1: Diagrama de Casos de Uso del Cajero Automático (Caso 1)

El modelado funcional del sistema de Cajero Automático (_Automated Teller Machine_, ATM) articula las operaciones transaccionales y su interacción con entidades bancarias externas:

- *Actores del Sistema:*
  - *Usuario / Cliente (Actor Principal):* Tarjetahabiente que opera la interfaz física del cajero.
  - *Consorcio de Cajeros (Actor Secundario):* Red interbancaria mediadora responsable de la conectividad y validación de terminales.
  - *Banco Emisor (Actor Secundario):* Institución financiera depositaria de la cuenta que procesa y aprueba las transacciones.
- *Casos de Uso Principales:*
  - `CU-00 Autenticar Tarjeta y PIN`: Verificación criptográfica de credenciales.
  - `CU-01 Retirar Efectivo`: Dispensación física de billetes con cargo a la cuenta.
  - `CU-02 Depositar Efectivo`: Recepción y acreditación de dinero en cuenta.
  - `CU-03 Hacer Transferencias`: Traspaso de fondos entre cuentas bancarias.
  - `CU-04 Obtener Información de Cuenta`: Consulta de saldos y últimos movimientos.
- *Relaciones Notacionales:*
  - `<<include>>`: Las cuatro transacciones principales incorporan obligatoriamente a `CU-00`, garantizando que ninguna operación se ejecute sin previa autenticación válida.
  - `<<extend>>`: Los flujos de excepción (`Bloquear Tarjeta por 3 Errores de PIN`) y opciones complementarias (`Emitir Comprobante`) extienden condicionalmente el flujo base.

La @fig-caso1-cu presenta el diagrama de Casos de Uso del sistema:

#figure(
  image("/l3/img/caso1_casos_uso_atm.png", width: 66%),
  caption: [Diagrama de Casos de Uso para el Cajero Automático con autenticación obligatoria y actores bancarios externos.],
) <fig-caso1-cu>

La @tab-especificacion-cu detalla la especificación formal del caso de uso crítico `CU-01 Retirar Efectivo` @cockburn2000:

#figure(
  table(
    columns: (1.5fr, 3.5fr),
    align: (left, left),
    table.header([*Atributo*], [*Especificación Formal del Caso de Uso CU-01*]),
    [*Identificador y Nombre*], [CU-01: Retirar Efectivo del Cajero Automático.],
    [*Actor Principal*], [Usuario / Tarjetahabiente.],
    [*Actores Secundarios*], [Consorcio de Red de Cajeros, Banco Emisor.],
    [*Precondiciones*],
    [
      1. Terminal ATM en estado operativo y con stock en dispensador.\ 2. Tarjeta plástica válida y PIN activo del cliente.
    ],

    [*Flujo Principal (Éxito)*],
    [
      1. El cliente inserta la tarjeta e introduce su PIN en el teclado seguro.\ 2. El sistema ejecuta `CU-00 Autenticar Tarjeta y PIN` vía Consorcio y Banco.\ 3. El cliente selecciona la opción de Retiro y especifica el monto deseado.\ 4. El cajero comprueba la disponibilidad física de billetes en su dispensador.\ 5. El cajero envía la solicitud de débito al Consorcio, que la enruta al Banco.\ 6. El Banco valida el saldo disponible, debita la cuenta y aprueba la operación.\ 7. El cajero dispensa los billetes, expulsa la tarjeta y emite el comprobante.
    ],

    [*Flujos Alternativos*],
    [A1. _PIN Inválido:_ Se notifica el error; al tercer intento fallido se retiene la tarjeta.\ A2. _Fondos Insuficientes:_ El Banco declina el débito; se notifica al usuario sin dispensar dinero.\ A3. _Stock Físico Insuficiente:_ El cajero alerta de la indisponibilidad de billetes antes de contactar al banco.],

    [*Postcondiciones*], [Monto debitado en la cuenta del Banco y efectivo entregado al cliente.],
  ),
  caption: [Especificación formal del caso de uso de retiro de efectivo (IEEE 830 / Cockburn).],
) <tab-especificacion-cu>

=== b) Ejercicio Propuesto 2: Análisis, Identificación y Regeneración del Diagrama 2

==== Identificación Tipológica
El artefacto bajo análisis corresponde a un *Diagrama de Actividades UML* (_UML Activity Diagram_) de la categoría de comportamiento, destinado a modelar la concurrencia, sincronización de flujos y toma de decisiones procedimentales en el ciclo de despacho @fowler2003.

==== Descomposición Notacional
1. *Nodo Inicial:* Marca el punto de activación procedimental ante la llegada de un pedido.
2. *Acción `Recibe orden`:* Captura inicial de la orden de compra.
3. *Barra de Bifurcación (Fork Bar) con disparador múltiple:* Modela la división del flujo en dos ramas paralelas:
  - _Anotación semántica: `* [por cada artículo de línea del pedido]`._ Establece que la verificación de inventario se ejecuta de forma iterativa o concurrente por cada ítem individual.
4. *Rama Financiera (Izquierda):* Ejecuta `Autoriza pago`. Si la validación resulta en `[fallo]`, avanza a `Cancela orden` y finaliza; si resulta en `[éxito]`, avanza hacia la barra de sincronización.
5. *Rama de Inventario (Derecha):* Ejecuta `Comprueba artículo de línea`. Si está `[en existencia]`, transiciona a `Asigna a orden`; si `[se necesita reordenar]`, dispara concurrentemente la acción `Reordena artículo` para abastecimiento.
6. *Barra de Sincronización (Join Bar):* Sincroniza las ramas paralelas bajo la guarda: _`[existencia asignada a todos los artículos de línea y pago autorizado]`_. Garantiza que no se avance al despacho sin el cumplimiento simultáneo de ambas condiciones.
7. *Acción Terminal y Nodo Final:* Ejecuta `Despacha orden` y finaliza el proceso.

La @fig-caso2-actividades ilustra el diagrama descrito.

#figure(
  image("/l3/img/caso2_actividades_orden.png", width: 60%),
  caption: [Diagrama de Actividades UML para el procesamiento concurrente y sincronización de órdenes.],
) <fig-caso2-actividades>

=== c) Ejercicio Propuesto 3: Diagrama de Clases para la Gestión de Pedidos (Caso 3)

El sistema de gestión de pedidos demanda resolver requerimientos de agregación jerárquica y centralización operativa mediante patrones de diseño @gamma1994:

1. *Patrón Estructural Composite:*
  - Permite modelar pedidos simples y pedidos compuestos bajo una interfaz uniforme:
    - `Pedido` (Componente Abstracto): Declara las operaciones comunes (`calcularTotal()`, `totalUnidades()`, `obtenerCuentasAsociadas()`, `validarInvariantes()`).
    - `PedidoSimple` (Hoja / Leaf): Asociado a una única `CuentaPago` y limitado a un máximo de 20 unidades en total.
    - `PedidoCompuesto` (Compuesto / Composite): Contenedor que agrupa dos o más pedidos (simples o compuestos).
2. *Patrón Creacional Singleton:*
  - `GestorProcesamientoPedidos`: Instancia única responsable del procesamiento del lote diario de cobros, orden de distribución y confirmación de entrega.
3. *Invariantes de Dominio Modeladas:*
  - *Titularidad Homogénea:* Todos los pedidos simples que componen un pedido compuesto deben abonarse con cuentas pertenecientes al mismo cliente.
  - *Restricción de Capacidad:* Todo pedido simple tiene un límite de 20 unidades totales ($sum "unidades" <= 20$).
  - *Prerrequisito de Fondos:* Un cliente únicamente puede ordenar si cuenta con al menos una cuenta con saldo disponible positivo.
  - *Atomicidad del Cobro:* Si una cuenta carece de fondos suficientes durante el cobro diario, el pedido se rechaza por completo (si forma parte de un pedido compuesto, se rechaza íntegramente la estructura).

La @fig-caso3-clases muestra el Diagrama de Clases desarrollado.

#figure(
  image("/l3/img/caso3_clases_pedidos.png", width: 70%),
  caption: [Diagrama de Clases para la Gestión de Pedidos con patrones Composite y Singleton.],
) <fig-caso3-clases>

=== d) Ejercicio Propuesto 4: Triada de Diagramas por Caso de Estudio

Para garantizar la cobertura multidimensional requerida por IEEE Std. 1016-2009, se desarrollaron las tres perspectivas (funcional, estructural y de comportamiento) para cada sistema:

==== 1. Triada para el Caso 1 (Cajero Automático)
- *Vista Funcional:* Casos de Uso del sistema ATM (@fig-caso1-cu).
- *Vista Estructural:* Arquitectura de componentes de hardware (`RanuraTarjeta`, `DispensadorBilletes`, `TecladoPIN`), contratos de transacciones y entidades de red (@fig-caso1-clases).
- *Vista Dinámica:* Secuencia temporal de intercambio de mensajes para la validación y retiro de efectivo (@fig-caso1-secuencia).

#figure(
  image("/l3/img/caso1_clases_atm.png", width: 90%),
  caption: [Diagrama de Clases estructural para los componentes y subsistemas del Cajero Automático.],
) <fig-caso1-clases>

#figure(
  image("/l3/img/caso1_secuencia_retiro.png", width: 90%),
  caption: [Diagrama de Secuencia dinámico para el flujo temporal de retiro de efectivo.],
) <fig-caso1-secuencia>

==== 2. Triada para el Caso 2 (Procesamiento Concurrente de Órdenes)
- *Vista Dinámica:* Diagrama de Actividades con sincronización concurrente (@fig-caso2-actividades).
- *Vista Funcional:* Casos de Uso para la recepción de órdenes, comprobación de inventario y despacho (@fig-caso2-cu).
- *Vista Estructural:* Diagrama de Clases que modela entidades de orden, artículos de línea y almacén (@fig-caso2-clases).

#figure(
  image("/l3/img/caso2_casos_uso_orden.png", width: 80%),
  caption: [Diagrama de Casos de Uso funcional para el sistema de procesamiento y despacho de órdenes.],
) <fig-caso2-cu>

#figure(
  image("/l3/img/caso2_clases_orden.png", width: 80%),
  caption: [Diagrama de Clases estructural para las entidades de orden, artículos y logística.],
) <fig-caso2-clases>

==== 3. Triada para el Caso 3 (Gestión de Pedidos Compuestos)
- *Vista Estructural:* Diagrama de Clases con patrones Composite y Singleton (@fig-caso3-clases).
- *Vista Funcional:* Casos de Uso para la configuración de cuentas, pedidos y cobros (@fig-caso3-cu).
- *Vista Dinámica:* Máquina de estados finitos del ciclo de vida del pedido (@fig-caso3-estados) y secuencia temporal del cobro diario por lote (@fig-caso3-secuencia).

#figure(
  image("/l3/img/caso3_casos_uso_pedidos.png", width: 70%),
  caption: [Diagrama de Casos de Uso funcional para la gestión y procesamiento de pedidos.],
) <fig-caso3-cu>

#figure(
  image("/l3/img/caso3_estados_pedido.png", width: 80%),
  caption: [Diagrama de Estados para el ciclo de vida del pedido (Pendiente, Cobrado, En Distribución, Confirmado, Rechazado).],
) <fig-caso3-estados>

#figure(
  image("/l3/img/caso3_secuencia_cobro_diario.png", width: 90%),
  caption: [Diagrama de Secuencia para el cobro diario masivo y validación atómica de fondos.],
) <fig-caso3-secuencia>

=== e) Ejercicio Propuesto 5: Mockups de Interfaz de Usuario

Se diseñaron prototipos visuales en escala de grises de alta fidelidad para validar la experiencia de usuario y las restricciones de interacción:

1. *Cajero Automático — Autenticación y Menú Principal:* Pantalla táctil de bienvenida con ingreso seguro de PIN y panel de operaciones bancarias disponibles (@fig-mockup-atm-login y @fig-mockup-atm-menu).
2. *Gestión de Pedidos — Portal de Cliente y Consola Gestor:* Armado de pedidos compuestos con control dinámico del límite de 20 unidades y consola del Singleton para monitoreo del cobro diario masivo (@fig-mockup-pedidos-cliente y @fig-mockup-pedidos-admin).

#grid(
  columns: (1fr, 1fr),
  gutter: 10pt,
  [
    #figure(
      image("/l3/img/mockup_atm_bienvenida.svg", width: 100%),
      caption: [Mockup táctil de autenticación y PIN.],
    ) <fig-mockup-atm-login>
  ],
  [
    #figure(
      image("/l3/img/mockup_atm_menu.svg", width: 100%),
      caption: [Mockup del menú de transacciones.],
    ) <fig-mockup-atm-menu>
  ],
)

#grid(
  columns: (1fr, 1fr),
  gutter: 10pt,
  [
    #figure(
      image("/l3/img/mockup_pedidos_cliente.svg", width: 100%),
      caption: [Mockup del portal de cliente.],
    ) <fig-mockup-pedidos-cliente>
  ],
  [
    #figure(
      image("/l3/img/mockup_pedidos_admin.svg", width: 100%),
      caption: [Mockup de la consola del Gestor Singleton.],
    ) <fig-mockup-pedidos-admin>
  ],
)

=== f) Ejercicio Propuesto 6: Verificación y Auditoría de Cumplimiento IEEE Std. 1016-2009

Para auditar sistemáticamente la calidad y completitud de la Descripción de Diseño de Software (SDD), se aplicó una lista de verificación técnica formal bajo lineamientos de inspección IEEE Std. 1028-2008 @ieee1028. La @tab-checklist-ieee consolida los resultados:

#figure(
  table(
    columns: (1fr, 2.2fr, 2.8fr, 1.2fr, 1fr),
    align: (center, left, left, center, center),
    table.header(
      [*ID*], [*Criterio Normativo (IEEE 1016)*], [*Evidencia en el Diseño*], [*Método Verif.*], [*Resultado*]
    ),
    [CHK-01],
    [Entidades de diseño formales (ID, Tipo, Propósito).],
    [Clases, casos de uso y actividades con nomenclatura estandarizada y responsabilidades explícitas.],
    [Inspección documental],
    [Conforme (100%)],

    [CHK-02],
    [Punto de Vista de Contexto (Context Viewpoint).],
    [Límites del sistema y actores externos claramente definidos en los diagramas de Casos de Uso.],
    [Análisis de fronteras],
    [Conforme (100%)],

    [CHK-03],
    [Punto de Vista Lógico (Logical Viewpoint).],
    [Modelos de clases con tipos de datos, visibilidad, métodos y multiplicidades formales.],
    [Revisión estática],
    [Conforme (100%)],

    [CHK-04],
    [Punto de Vista de Interacción (Interaction Viewpoint).],
    [Diagramas de secuencia que capturan el ordenamiento temporal de mensajes y respuestas.],
    [Simulación de trazas],
    [Conforme (100%)],

    [CHK-05],
    [Punto de Vista Dinámico y Estados (State Dynamics).],
    [Máquina de estados finitos que formaliza las transiciones de pedidos válidos e inválidos.],
    [Análisis de estados],
    [Conforme (100%)],

    [CHK-06],
    [Punto de Vista Algorítmico (Algorithm Viewpoint).],
    [Diagrama de actividades con bifurcaciones concurrentes (`fork`), disparadores múltiples y uniones (`join`).],
    [Inspección de flujo],
    [Conforme (100%)],

    [CHK-07],
    [Modelado de Restricciones e Invariantes.],
    [Restricciones de 20 unidades por pedido simple, titularidad común de cuentas y cobro atómico.],
    [Verificación formal],
    [Conforme (100%)],

    [CHK-08],
    [Trazabilidad Requerimientos-Diseño.],
    [Mapeo sistemático entre especificaciones funcionales y artefactos UML elaborados.],
    [Matriz de trazabilidad],
    [Conforme (100%)],
  ),
  caption: [Lista de verificación formal (Checklist Fagan / IEEE 1028) para auditoría de conformidad del SDD según IEEE Std. 1016-2009.],
) <tab-checklist-ieee>

*Dictamen de Auditoría:* El diseño satisface el *100% de los criterios normativos evaluados*, certificando que los artefactos generados constituyen una Descripción de Diseño de Software (SDD) rigurosa, no ambigua y preparada para guiar la construcción del sistema.

=== g) Ejercicio Propuesto 7: Tabla Taxonómica de Diagramas de UML 2.5

La @tab-taxonomia-uml presenta la clasificación taxonómica de los 14 diagramas de UML 2.5, detallando su utilidad técnica y su contribución a los atributos de calidad de software (ISO/IEC 25010) @booch2005:

#figure(
  table(
    columns: (1.2fr, 1.4fr, 2.6fr, 1.6fr),
    align: (center, left, left, left),
    table.header(
      [*Categoría*], [*Diagrama UML*], [*Propósito y Utilidad Técnica*], [*Aporte a la Calidad (ISO 25010)*]
    ),
    [Estructural],
    [Diagrama de Clases],
    [Modela la estructura estática: clases, atributos, métodos, relaciones de herencia y composición.],
    [Previene inconsistencias de tipos y asegura modularidad.],

    [Estructural],
    [Diagrama de Objetos],
    [Exhibe una instantánea en tiempo de ejecución de las instancias y sus enlaces actuales.],
    [Permite validar casos de prueba concretos y configuraciones complejas.],

    [Estructural],
    [Diagrama de Componentes],
    [Muestra la organización y dependencias entre módulos compilables y librerías.],
    [Facilita el mantenimiento y la reutilización de componentes.],

    [Estructural],
    [Diagrama de Despliegue],
    [Describe la topología física de nodos de hardware, servidores y enlaces de red.],
    [Garantiza la planificabilidad del rendimiento y la escalabilidad.],

    [Estructural],
    [Diagrama de Estructura Compuesta],
    [Descompone la estructura interna de un clasificador, mostrando sus partes y puertos.],
    [Clarifica la encapsulación y los contratos entre micro-partes.],

    [Estructural],
    [Diagrama de Paquetes],
    [Organiza elementos de modelado en grupos lógicos, espacios de nombres y capas.],
    [Previene dependencias circulares y organiza la arquitectura.],

    [Estructural],
    [Diagrama de Perfiles],
    [Permite extender la semántica de UML mediante estereotipos para dominios específicos.],
    [Estandariza dialectos en sectores altamente regulados.],

    [Comportamiento],
    [Diagrama de Casos de Uso],
    [Modela los requerimientos funcionales desde la perspectiva de los actores del entorno.],
    [Delimita el alcance del sistema y alinea expectativas.],

    [Comportamiento],
    [Diagrama de Actividades],
    [Representa flujos procedimentales, concurrencia (fork/join) y control condicional.],
    [Optimiza procesos de negocio y detecta cuellos de botella.],

    [Comportamiento],
    [Diagrama de Máquina de Estados],
    [Modela los estados discretos y transiciones en respuesta a eventos del ciclo de vida.],
    [Previene estados inválidos o transiciones corruptas.],

    [Interacción],
    [Diagrama de Secuencia],
    [Representa el intercambio temporal ordenado de mensajes sobre líneas de tiempo verticales.],
    [Detecta condiciones de carrera y llamadas redundantes.],

    [Interacción],
    [Diagrama de Comunicación],
    [Enfoca la interacción en la estructura de vínculos por donde viajan los mensajes.],
    [Verifica la viabilidad de rutas y el acoplamiento estructural.],

    [Interacción],
    [Diagrama de Tiempos (Timing)],
    [Modela las variaciones de estado en función de escalas de tiempo cuantitativas.],
    [Crítico para certificar sistemas de tiempo real estricto.],

    [Interacción],
    [Diagrama Global de Interacción],
    [Visión de alto nivel que articula nodos de actividad que contienen secuencias.],
    [Permite navegar flujos de integración complejos.],
  ),
  caption: [Taxonomía de los 14 diagramas del Lenguaje Unificado de Modelado (UML 2.5).],
) <tab-taxonomia-uml>

=== h) Ejercicio Propuesto 8: Síntesis de Resultados del Modelado y Trazabilidad de Vistas

El desarrollo sistemático de los artefactos de diseño ha permitido consolidar una Descripción de Diseño de Software integral que cubre los objetivos planteados:

1. *Consistencia Multivista:* Los tres casos de estudio cuentan con representaciones congruentes en sus perspectivas funcional, estructural y dinámica. Cada entidad identificada en los casos de uso encuentra su correlato en las clases del dominio y en los flujos de interacción.
2. *Garantía de Invariantes Arquitectónicas:* La aplicación del patrón Composite y del patrón Singleton resuelve formalmente la recursividad y la atomicidad del cobro, blindando el diseño contra inconsistencias contables o de logística.
3. *Validación Temprana de Usabilidad:* Los prototipos visuales (mockups) completan el ciclo de diseño al plasmar visualmente las restricciones lógicas (tope de 20 unidades, autenticación segura y monitoreo por lote), habilitando la retroalimentación ágil de los interesados antes de la fase de construcción.
