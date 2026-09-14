== I. Objetivos y Procedimiento Metodológico

=== a) Objetivos de la Práctica

1. *Aplicar el estándar internacional IEEE Std. 1016-2009* para estructurar Descripciones de Diseño de Software (SDD) organizadas en Vistas de Diseño y Puntos de Vista canónicos.
2. *Modelar sistemas mediante el Lenguaje Unificado de Modelado (UML 2.5)*, articulando perspectivas funcionales, estructurales y dinámicas en dominios transaccionales y de comercio electrónico.
3. *Implementar patrones de diseño arquitectónicos (Composite y Singleton)* para resolver problemas de agregación jerárquica y centralización de procesos de facturación.
4. *Diseñar prototipos visuales de interfaz de usuario (Mockups)* que permitan validar la experiencia de usuario y los flujos de interacción del sistema.
5. *Auditar formalmente la calidad del diseño* mediante listas de verificación técnicas estructuradas bajo lineamientos del estándar IEEE Std. 1028-2008.

=== b) Procedimiento Metodológico

El desarrollo de la práctica se estructuró en cinco etapas técnicas consecutivas:

1. *Selección y parametrización de herramientas de diseño:* Evaluación de plataformas de modelado bajo el paradigma _Diagrams as Code_ (Mermaid y PlantUML) y entornos de prototipado UX/UI para interfaces de usuario.
2. *Análisis normativo de la norma IEEE Std. 1016-2009:* Descomposición de la arquitectura de un SDD, identificando las preocupaciones técnicas de los interesados y mapeándolas hacia los diagramas formales de UML 2.5.
3. *Análisis y modelado de los casos de estudio:*
   - _Sistema de Cajero Automático (ATM):_ Identificación de actores, casos de uso financieros (retiro, depósito, transferencia y consulta) y relaciones de inclusión para autenticación de credenciales.
   - _Procesamiento Concurrente de Órdenes:_ Modelado de actividades procedimentales, destacando bifurcaciones paralelas por artículo de línea, control de stock y sincronización de pago.
   - _Gestión de Pedidos Compuestos:_ Diseño orientado a objetos aplicando el patrón *Composite* (pedidos simples limitados a 20 unidades y compuestos anidados) y el patrón *Singleton* para la orquestación diaria de cobros.
4. *Articulación de la triada de vistas de diseño:* Elaboración sistemática de las tres perspectivas indispensables (funcional, estructural y de comportamiento) para cada uno de los sistemas analizados.
5. *Prototipado de interfaces y auditoría formal de calidad:* Construcción de maquetas de pantalla para terminales y portales web, y evaluación de conformidad del diseño frente a los criterios normativos de completitud y consistencia.
