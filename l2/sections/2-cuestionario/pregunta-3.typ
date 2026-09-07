== Pregunta 3: Estimación y Asignación de Recursos para el Control de Cambios

*Enunciado:* _¿Qué % de recursos del proyecto considera aceptable para gestión de cambios?_

Conforme a la literatura canónica de ingeniería de software y las directrices de estimación económica de Pressman & Maxim @pressman2020, Sommerville @sommerville2011 y Baufest @baufest2003, la reserva presupuestal y de esfuerzo (horas-hombre, personal de aseguramiento de calidad e infraestructura) considerada óptima oscila entre el *10% y el 15%* del presupuesto global del proyecto:

#block(breakable: false)[
*1. Variación del Esfuerzo según el Paradigma de Desarrollo:*
- *Modelos Secuenciales o Predictivos (Cascada / V-Model / RUP @guerrero2003):*
  Se recomienda destinar entre un *5% y un 10%*. Las líneas base se congelan al culminar la fase de especificación; por ende, las alteraciones aprobadas por el CCB son excepcionales y demandan exhaustivos análisis de regresión documental y arquitectónica.
- *Modelos Iterativos y Adaptativos (Scrum / Kanban / Lean Software):*
  Se estipula una reserva planificada entre un *15% y un 20%*. La premisa ágil acoge el cambio de requisitos como una fuente continua de valor de negocio, demandando refinamiento recurrente del backlog, estimaciones de impacto en cada sprint y reconfiguración continua de las suites de prueba automatizadas.
]

*2. Dinámica de Riesgo por Desviación de Recursos:*
- *Subasignación Crítica ($< 8\%$ de recursos):* Genera inevitablemente deuda técnica severa, modificaciones no documentadas en caliente, vulnerabilidades de seguridad inadvertidas y costos de remediación tardía que, según Boehm y Pressman, pueden superar en un orden de magnitud ($10 times$ a $100 times$) el costo de resolución en etapa de diseño.
- *Sobreasignación Ineficiente ($> 25\%$ de recursos):* Desemboca en la patología de la "parálisis por análisis", generando burocracia excesiva en la aprobación de cambios triviales, desincentivando la innovación y demorando la entrega continua de valor al usuario final.

*Conclusión Técnica:*
Una asignación comprendida entre el *10% y el 15%* equilibra con precisión la agilidad operativa y el rigor de aseguramiento de la calidad, salvaguardando la integridad del producto sin erosionar el margen financiero ni comprometer los plazos de liberación al mercado.
