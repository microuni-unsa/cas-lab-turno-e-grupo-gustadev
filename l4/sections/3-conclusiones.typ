#import "/components/lab-section.typ": lab-section

#lab-section(title: "CONCLUSIONES")[
  #show heading: set text(weight: "bold")
  #set par(justify: true)

  - El dimensionamiento funcional temprano mediante metodologías normalizadas como NESMA e IFPUG permite cuantificar objetivamente los requerimientos de software antes de iniciar la construcción, facilitando la planificación y el control de calidad.

  - La delimitación rigurosa de la frontera del Cajero Automático ATM frente a las entidades externas Banco Emisor y Red Consorcio permitió obtener un conteo consistente de 60 Puntos de Función No Ajustados, asegurando una estimación confiable.

  - La correspondencia entre NESMA con 60 PF y COSMIC con 26 CFP demuestra que ambos métodos producen estimaciones compatibles de entre 5.2 y 6.0 personas-mes cuando se aplican rigurosamente las reglas de cada estándar.

  - La evaluación comparativa de las herramientas de cálculo demostró que la estimación paramétrica con COCOMO II es idónea para etapas iniciales de contratación, mientras que la estimación PERT es adecuada para la gestión operativa de tareas en sprints ágiles.

  - Se comprobó que la experiencia técnica del equipo influye directamente en la productividad y en el plazo de entrega, pero no altera el tamaño funcional del software entregado al cliente, el cual depende exclusivamente de los requerimientos especificados.
]
