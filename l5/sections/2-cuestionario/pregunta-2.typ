= Pregunta b): ¿Cuál es la estructura del nombre o etiqueta propuesta para cada versión?

Subversion no usa nombres arbitrarios por defecto sino dos mecanismos complementarios @svnbook:

+ *Número de revisión global:* entero monotónico `rN` (`r0, r1, r2, r3, ...`). Cada `commit` exitoso incrementa en 1 la revisión de todo el árbol, no por archivo. Ejemplo del lab: `r1 import inicial`, `r2 cambio 1`, `r3 alice`.
+ *Convención de layout + etiquetas legibles:* la estructura recomendada (y exigida por TortoiseSVN §3.12 @ejie-tortoise-manual) es:
  ```
  /trunk                # línea principal estable
  /branches/<nombre>    # ej. /branches/feat-x, /branches/hotfix-login
  /tags/<versión>       # ej. /tags/v1.0, /tags/v1.0.1, /tags/release-2026-10
  ```
  Una etiqueta es una copia barata (`svn copy trunk tags/v1.0 -m "tag"`) que congela una revisión con nombre semántico. Por convención se usan `vMAYOR.MENOR.PARCHE` y nunca se commitea sobre `tags/`.

En el laboratorio se usó `file:///home/$USER/lab5-svn/repo/trunk` como línea principal y `svn info` confirmó `Relative URL: ^/trunk`. La tabla de revisiones (@fig-commit-log) muestra el mapeo `rN <-> mensaje <-> autor`, que es la etiqueta funcional mínima; para entregas se recomienda crear `tags/v1.0` con `svn copy`.
