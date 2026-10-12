= Pregunta c): Grafique el diagrama de actualización de versión luego de un cambio

El ciclo copiar-modificar-fusionar del SVN-BOOK @svnbook verificado con `wc-alice/wc-bob` (@fig-conflict) se grafica así (fuente Mermaid en `src/diagrama-actualizacion.mmd`):

#figure(
  image("/l5/img/diagrama_actualizacion_version.png", width: 55%),
  caption: [Diagrama de actualización de versión luego de un cambio: `svn update` fusiona o marca conflicto `C`, se resuelve con Meld + `svn resolved` y recién entonces `svn commit` crea la nueva revisión.],
) <fig-diagrama-update>

*Lectura:* `update` trae los cambios ajenos e intenta fusión automática. Si no hay solape, la copia queda en `r3` sin intervención. Si hay solape, marca `C`, genera los tres temporales y bloquea el `commit` hasta `resolved`. Solo después de resolver se puede crear `r4`. Este es el diagrama que pide el enunciado y corresponde a EJIE §3.4–3.5 @ejie-tortoise-manual.

#figure(
  image("/l5/img/merge_conflict_wc.png", width: 90%),
  caption: [Evidencia del punto de decisión: `C app.txt` tras `svn update` a r3.],
)

#figure(
  image("/l5/img/meld_wc_conflicts.png", width: 90%),
  caption: [Evidencia de la rama SÍ: fusión manual en Meld antes de `resolved + commit`.],
)
