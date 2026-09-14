#import "/lib.typ": unsa-report

#show: unsa-report.with(
  course_name: "Laboratorio - Calidad de Software",
  lab_title: "Análisis de Sistemas - Estándar IEEE 1016-2009 y Artefactos UML",
  lab_number: "03",
  instructor_name: "Delgado Bastidas, Jose Rafael",
  members: (
    "Sequeiros Condori Luis Gustavo",
  ),
)

// Tipografía y espaciado de texto
#set par(justify: true, leading: 0.62em, spacing: 0.75em)
#set list(indent: 4pt)
#show raw.where(block: false): it => box(inset: (x: 1pt))[#it]

// Configuración de figuras y dimensiones visuales
#set image(width: 60%)
#set figure(placement: none, gap: 0.45em)
#show figure.where(kind: table): set figure(placement: none)
#show figure.where(kind: table): set figure(supplement: [Tabla])
#show figure.where(kind: table): set block(breakable: true)
#show figure.caption: set text(size: 8pt)

// Estilo profesional para tablas y encabezados institucionales
#show table.cell.where(y: 0): set text(weight: "bold", fill: white, size: 8pt)
#set table(
  stroke: (x, y) => if y == 0 { (bottom: 1.2pt + rgb("#991b1b")) } else { 0.4pt + rgb("#cbd5e1") },
  fill: (col, row) => if row == 0 { rgb("#C8310E") } else if calc.even(row) { rgb("#f8fafc") } else { none },
  inset: (x: 5pt, y: 4.5pt),
)

#include "sections/1-resultados.typ"
#v(0.4em)
#include "sections/2-cuestionario.typ"
#v(0.4em)
#include "sections/3-conclusiones.typ"
#v(0.4em)
#pagebreak(weak: true)
#include "sections/4-referencias.typ"
