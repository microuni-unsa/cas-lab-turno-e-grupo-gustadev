== IV. Análisis de Resultados

+ *Concentración del riesgo:* 6 de los 8 escenarios nacen en Alto o Crítico (R1–R6), lo que resulta coherente con controles actuales inexistentes (`Ninguno`) o parciales en copias, segundo factor, permisos y enlaces.
+ *Eficacia prevista:* el tratamiento desplaza los 8 casos hacia Bajo o Moderado (4+4); sin embargo, ningún residual llega a 0, pues no se aceptó anular el riesgo por evitar o transferir sin justificación. Además, en R3 y R6 la I se mantuvo en 5 aunque P bajó a 1, ya que una divulgación o alteración masiva sigue siendo grave aunque resulte menos probable.
+ *Umbral:* R≤4 no equivale a aceptación automática; los 4 casos Bajos requieren aprobación documentada y los 4 Moderados exigen nuevo tratamiento o una excepción justificada.
+ *Limitaciones:* las estimaciones son ordinales y se apoyan en supuestos didácticos (sin pruebas de restauración ejecutadas, 2FA parcial y roles sin implementar). Por tanto, el residual declarado es previsto hasta que los controles se implementen y se prueben. Conforme a las reglas de la plantilla, no se sumaron índices ordinales como valores monetarios ni se empleó P como frecuencia anual.

#figure(
  image("/l6/img/excel_analisis_actual.png", width: 95%),
  caption: [Bloque superior del Análisis: R actual automático desde Cruces (20 Crítico con atención Urgente hasta 8 Moderado con Programar), todo en estado Listo.],
) <fig-analisis-actual>
