#import "/lib.typ": unsa-report
#import "/components/code-block.typ": code-block

#show: unsa-report.with(
  course_name: "Laboratorio - Calidad de Software",
  lab_title: "Proceso de Requerimientos",
  lab_number: "02",
  instructor_name: "Delgado Bastidas, Jose Rafael",
  members: (
    "Sequeiros Condori Luis Gustavo",
  ),
)

#set image(width: 70%)
#set list(indent: 2pt)
#show raw.where(block: false): it => box(inset: (x: 0.5pt))[#it]

// Prevent awkward page breaks by enabling figure placement, table breakability and compact spacing
#set figure(placement: auto)
#show figure.where(kind: table): set figure(placement: none)
#show figure.where(kind: table): set figure(supplement: [Tabla])
#show figure.where(kind: table): set block(breakable: true)
#set figure(gap: 0.6em)
#show figure.caption: set text(size: 8pt)
#set table(inset: 4.5pt)

#include "sections/1-resultados.typ"
#v(0.5em)
#include "sections/2-cuestionario.typ"
#v(0.5em)
#include "sections/3-conclusiones.typ"
#v(0.5em)
#pagebreak(weak: true)
#include "sections/4-referencias.typ"
