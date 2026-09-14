# GUÍA DE LABORATORIO

CALIDAD DE SOFTWARE

#### COMPETENCIAS

Construye responsablemente soluciones siguiendo un proceso adecuado llevando a cabo las pruebas ajustada a los recursos disponibles del cliente.. Cm

### Análisis de Sistemas

### I OBJETIVOS

Conocer, analizar y utilizar adecuadamente alguna norma y herramienta para el análisis de sistemas utilizando alguna metodología (Ejemplo UML).

### II TEMAS A TRATAR

- **Estándar IEEE 1016 2009**
- **Artefactos UML**

### III MARCO TEÓRICO

- **Estándar IEEE 1016 2009** Esta norma especifica los requisitos sobre el contenido y la organización de la información para el diseño de software, descripciones (SDD). Un SDD es una representación de un diseño de software que se utilizará para grabar información de diseño, abordando diversas preocupaciones de diseño y comunicando esa información a las partes interesadas del diseño. Los SDD juegan un papel fundamental en el desarrollo y mantenimiento de sistemas de software. Durante su vida, un SDD es utilizado por adquirentes, gerentes de proyectos, personal de control de calidad, gerentes de configuración, software diseñadores, programadores, probadores y mantenedores. Cada una de estas partes interesadas tiene necesidades únicas, tanto en términos de la información de diseño requerida y organización óptima de esa información. Por lo tanto, en diseño l+a descripción contiene la información de diseño que necesitan esas partes interesadas. El estándar también especifica requisitos sobre los lenguajes de diseño que se utilizarán al producir SDD conforme a estos requisitos de contenido y organización. El estándar especifica que un SDD se organice en varias vistas de diseño. Cada vista aborda un conjunto específico de preocupaciones de diseño de las partes interesadas. Cada vista de diseño está prescrita por un punto de vista de diseño. El punto de vista identifica las preocupaciones de diseño en las que se debe enfocar dentro de su vista y selecciona el diseño. Lenguajes utilizados para registrar esa vista de diseño. El estándar establece un conjunto común de puntos de vista para el diseño. **M en C JESUS MARTIN SILVA FERNANDEZ**

vistas, como punto de partida para la preparación de un SDD, y una capacidad genérica para definir un nuevo diseño puntos de vista ampliando así la expresividad de un SDD para sus partes interesadas

- **Artefactos UML** El UML está compuesto por diversos elementos gráficos que se combinan para conformar diagramas. Debido a que el UML es un lenguaje, cuenta con reglas para combinar tales elementos. La finalidad de los diagramas es presentar diversas perspectivas de un sistema, a las cuales se les conoce como modelo. Recordemos que un modelo es una representación simplificada de la realidad; el modelo UML describe lo que supuestamente hará un sistema, pero no dice cómo implementar dicho sistema. A continuación se describirán los diagramas más comunes del UML y los conceptos que representan:
- Diagrama de Clases
- Diagrama de Objetos
- Diagrama de Casos de Uso
- Diagrama de Estados
- Diagrama de Secuencias
- Diagrama de Actividades
- Diagrama de Colaboraciones
- Diagrama de Componentes
- Diagrama de Distribución

### ACTIVIDADES

1. Procedimiento

- Ejecutar ejercicios resueltos y propuestos
- Escriba un reporte sobre las tareas realizadas y resultados.
- Escriba sus conclusiones

2. Agenda: Generación de artefactos 90 minutos Generación de reporte 15 minutos
3. Estructura de reporte
   I. Objetivo II. Descripción del procedimiento realizado en la práctica III. Resultados obtenidos IV. Análisis de resultados
   V. Conclusiones
   El envío al drive compartido del reporte, incluye código generado por alumno. **V**

### EJERCICIOS RESUELTOS

1. Instalar herramienta para elaborar diagramas UML y mockups, según detalle del Anexo 1. Alternativamente PlanText, Lucid Chart y moqup ([https://moqups.com/](https://moqups.com/))
2. Revisar documentación de la norma IEEE 1016 2009 para aplicar en la elaboración de los diagramas.

### VI EJERCICIOS PROPUESTOS

1. Diagrama de casos de uso para la descripción 1 del Anexo 1.
2. La descripción del diagrama 2, indique tipo de diagrama y regenere.
3. Diagrama de clases para la descripción 3 del Anexo 1
4. Completar los 3 tipos de diagramas para cada caso.
5. Generar los mockup para requerimientos de Anexo 1
6. Revisar e indicar si se ha cumplido del estándar IEEE Std. 1016-2009 Recommended Practice for Software Design Descriptions, utilice algún método de revisión y verificación.
7. Genere una tabla de todos los diagramas UML, describa la utilidad de cada uno.
8. Genere un reporte de la práctica y exponga sus conclusiones

### VII CUESTIONARIO

Actualmente que actividades utiliza con frecuencia en la gestión de requerimientos y cuales considera que son esenciales? Que herramienta considera de mejor utilidad para la gestión de requerimientos? Que % de recursos del proyecto considera aceptable para gestión de cambios?

### VIII BIBLIOGRAFÍA

<u>[http://cengproject.cankaya.edu.tr/wp-content/uploads/sites/10/2017/12/SDD-ieee-](http://cengproject.cankaya.edu.tr/wp-content/uploads/sites/10/2017/12/SDD-ieee-)</u> <u>1016-2009.pdf</u> <u>[https://www.teatroabadia.com/es/uploads/documentos/iagramas_del_uml.pdf](https://www.teatroabadia.com/es/uploads/documentos/iagramas_del_uml.pdf)</u>

#### ANEXO 1

#### Descripción 1

Se ha de realizar el diagrama de casos de uso de un cajero automático en el que se pueden realizar las operaciones siguientes:

- Retirar efectivo.
- Ingresar o depositar efectivo.
- Hacer transferencias.
- Obtener información de nuestra cuenta: movimientos, saldo, etc.
  Para realizar cualquiera de las operaciones el cajero automático ha de validar la tarjeta y la clave que introduce el usuario. Se debe considerar la interacción que tiene con el cajero, a la hora de realizar estas operaciones, el banco y el consorcio. Llamaremos consorcio a la red de cajeros automáticos a las que se suscriben los bancos para que los cajeros automáticos realicen las operaciones.

#### Diagrama 2

#### Descripcion 3

Realiza el diseño de una aplicación para la gestión de pedidos. La aplicación deberá:

- Manejar clientes (se guarda su nombre, dirección, teléfono y e-mail), que pueden realizar pedidos de productos, de los cuales se anota la cantidad en stock. Un cliente puede tener una o varias cuentas para el pago de los pedidos. Cada cuenta está asociada a una tarjeta de

crédito, y tiene una cierta cantidad disponible de dinero, que el cliente debe aumentar periódicamente para poder realizar nuevos pedidos.

• Un cliente puede empezar a realizar un pedido sólo si tiene alguna cuenta con dinero disponible.

Al realizar un pedido, un cliente puede agruparlos en pedidos simples o compuestos. Los pedidos simples están asociados a una sola cuenta de pago y (por restricciones en la distribución) contienen un máximo de 20 unidades del mismo o distinto tipo de producto. A su vez, un pedido compuesto contiene dos o más pedidos, que pueden ser simples o compuestos. Como es de esperar, el sistema debe garantizar que todos los pedidos simples que componen un pedido compuesto se paguen con cuentas del mismo cliente. Además, sólo es posible realizar peticiones de productos en stock.

• Existe una clase (de la cual debe haber una única instancia en la aplicación) responsable del cobro, orden de distribución y confirmación de los pedidos. El cobro de los pedidos se hace una vez al día, y el proceso consiste en comprobar todos los pedidos pendientes de cobro, y cobrarlos de la cuenta de pago correspondiente. Si una cuenta no tiene suficiente dinero, el pedido se rechaza (si es parte de un pedido compuesto, se rechaza el pedido entero). Una vez que el pedido está listo para servirse, se ordena su distribución, y una vez entregado, pasa a estar confirmado.

M en C JESUS MARTIN SILVA FERNANDEZ
