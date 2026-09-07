#import "/components/lab-section.typ": lab-section

#lab-section(title: "RESULTADOS Y PRUEBAS")[
  #show heading: set text(weight: "bold")
  #set par(justify: true)

  #include "1-resultados/1-objetivos-procedimiento.typ"
  #v(0.5em)
  #include "1-resultados/2-ejercicios-resueltos.typ"
  #v(0.5em)
  #include "1-resultados/3-ejercicios-propuestos.typ"
  #v(0.5em)
  #include "1-resultados/4-analisis-resultados.typ"
]
